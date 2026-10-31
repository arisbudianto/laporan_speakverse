<?php

namespace App\Support;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Failover otomatis antar API key Dinoiki.
 *
 * Dipasang sebagai middleware HTTP global (lihat
 * DinoikiKeyRotationServiceProvider), jadi SEMUA panggilan ke Dinoiki
 * dari controller/service mana pun otomatis tercakup tanpa mengubah
 * kode pemanggilnya.
 *
 * Kalau sebuah permintaan ke Dinoiki gagal karena alasan yang terkait
 * key/kuota (401, 402, 403, 429) atau gangguan server (5xx) atau koneksi
 * putus, permintaan yang SAMA dikirim ulang dengan key lain. Key yang
 * gagal karena saldo/otorisasi ditandai "istirahat" sementara supaya
 * permintaan berikutnya tidak membuang waktu mencobanya lagi.
 */
class DinoikiFailoverMiddleware
{
    /** Status yang memicu pindah key. */
    private const FAILOVER_STATUSES = [401, 402, 403, 429];

    /**
     * @param  array<int,string>  $keys  Semua key yang tersedia.
     * @param  string  $host  Host API Dinoiki (hanya request ke host ini yang dicakup).
     * @param  Closure|null  $markDown  fn(string $key, int $seconds): void
     * @param  Closure|null  $onFailover  fn(string $fromKey, string $toKey, string $reason): void
     * @param  array<string,string>  $modelByKey  [key => model]. Saat pindah ke key lain,
     *         field "model" di body JSON chat/completions diganti sesuai model key tujuan.
     */
    public function __construct(
        private array $keys,
        private string $host,
        private ?Closure $markDown = null,
        private ?Closure $onFailover = null,
        private array $modelByKey = [],
    ) {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
            if (strcasecmp($request->getUri()->getHost(), $this->host) !== 0) {
                return $handler($request, $options);
            }

            $current = $this->bearerOf($request);
            $alternatives = array_values(array_filter(
                $this->keys,
                fn ($key) => $key !== $current
            ));

            return $this->attempt($handler, $request, $options, $current, $alternatives);
        };
    }

    private function attempt(
        callable $handler,
        RequestInterface $request,
        array $options,
        string $currentKey,
        array $alternatives
    ): PromiseInterface {
        $retry = function (string $reason) use ($handler, $request, $options, $currentKey, $alternatives) {
            $next = array_shift($alternatives);

            if ($next === null) {
                return null; // tidak ada key lain
            }

            if ($this->onFailover) {
                ($this->onFailover)($currentKey, $next, $reason);
            }

            $body = $request->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }

            $nextRequest = $this->withModelFor($request, $next);

            return $this->attempt(
                $handler,
                $nextRequest->withHeader('Authorization', 'Bearer ' . $next),
                $options,
                $next,
                $alternatives
            );
        };

        return $handler($request, $options)->then(
            function (ResponseInterface $response) use ($retry, $currentKey) {
                $status = $response->getStatusCode();
                $shouldFailover = in_array($status, self::FAILOVER_STATUSES, true) || $status >= 500;

                if (! $shouldFailover) {
                    return $response;
                }

                if ($this->markDown && $status !== 429 && $status < 500) {
                    // Saldo habis / key ditolak: istirahatkan 5 menit.
                    ($this->markDown)($currentKey, 300);
                } elseif ($this->markDown && $status === 429) {
                    ($this->markDown)($currentKey, 30);
                }

                return $retry("HTTP {$status}") ?? $response;
            },
            function ($reason) use ($retry) {
                // Gangguan koneksi: coba key lain; kalau tidak ada, lempar error aslinya.
                $label = $reason instanceof Throwable ? get_class($reason) : 'connection error';
                $promise = $retry($label);

                if ($promise !== null) {
                    return $promise;
                }

                throw $reason instanceof Throwable ? $reason : new \RuntimeException((string) $reason);
            }
        );
    }

    private function bearerOf(RequestInterface $request): string
    {
        $header = $request->getHeaderLine('Authorization');

        return trim(preg_replace('/^Bearer\s+/i', '', $header) ?? '');
    }

    /**
     * Ganti field "model" pada body JSON chat/completions sesuai key tujuan.
     * Request non-JSON (mis. upload audio) atau tanpa pemetaan dibiarkan apa adanya.
     */
    private function withModelFor(RequestInterface $request, string $key): RequestInterface
    {
        $model = $this->modelByKey[$key] ?? null;

        if ($model === null || ! str_ends_with($request->getUri()->getPath(), '/chat/completions')) {
            return $request;
        }

        $body = $request->getBody();
        $raw = (string) $body;

        if ($body->isSeekable()) {
            $body->rewind();
        }

        $data = json_decode($raw, true);

        if (! is_array($data) || ! array_key_exists('model', $data)) {
            return $request;
        }

        $data['model'] = $model;
        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $request = $request->withBody(\GuzzleHttp\Psr7\Utils::streamFor($encoded));

        if ($request->hasHeader('Content-Length')) {
            $request = $request->withHeader('Content-Length', (string) strlen($encoded));
        }

        return $request;
    }
}
