<?php

namespace App\Providers;

use App\Support\DinoikiFailoverMiddleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Pembagian beban + failover antar API key Dinoiki, dengan satu model
 * per key (config('services.dinoiki.pool') = [[key, model], ...]).
 *
 * Mode (DINOIKI_ROTATION_MODE di .env):
 * - roundrobin (default): tiap request bergantian antar entri.
 * - primary: selalu entri pertama; entri berikutnya hanya dipakai
 *   kalau entri pertama gagal (failover) atau sedang "istirahat".
 *
 * Setiap request memilih satu entri lalu menimpa
 * config('services.dinoiki.key') DAN config('services.dinoiki.chat_model'),
 * sehingga semua controller/service yang membaca config tersebut
 * otomatis memakai pasangan key+model yang sama tanpa diubah.
 *
 * Bila panggilan gagal (401/402/403/429/5xx/koneksi), middleware HTTP
 * global mengirim ulang permintaan dengan entri lain, termasuk mengganti
 * field "model" di body supaya cocok dengan key tujuan.
 */
class DinoikiKeyRotationServiceProvider extends ServiceProvider
{
    private const DOWN_PREFIX = 'dinoiki_key_down:';

    public function boot(): void
    {
        $pool = $this->pool();

        if (count($pool) <= 1) {
            return;
        }

        $this->registerFailover($pool);

        $available = array_values(array_filter(
            $pool,
            fn ($entry) => ! $this->isDown($entry['key'])
        ));

        if ($available === []) {
            $available = $pool;
        }

        $index = config('services.dinoiki.rotation_mode', 'roundrobin') === 'primary'
            ? 0
            : $this->pickIndex(count($available));

        $chosen = $available[$index];

        config([
            'services.dinoiki.key' => $chosen['key'],
            'services.dinoiki.chat_model' => $chosen['model'],
            'services.dinoiki.active_key_index' => array_search($chosen, $pool, true),
        ]);
    }

    /** @return array<int,array{key:string,model:string}> */
    private function pool(): array
    {
        $pool = (array) config('services.dinoiki.pool', []);

        if ($pool !== []) {
            return array_values(array_filter($pool, fn ($e) => ! empty($e['key'])));
        }

        // Kompatibel dengan konfigurasi lama (hanya 'keys').
        $model = (string) config('services.dinoiki.chat_model', 'gpt-4o');

        return array_map(
            fn ($key) => ['key' => $key, 'model' => $model],
            array_values(array_filter((array) config('services.dinoiki.keys', [])))
        );
    }

    private function registerFailover(array $pool): void
    {
        $host = parse_url(
            (string) config('services.dinoiki.base_url', 'https://ai.dinoiki.com/v1'),
            PHP_URL_HOST
        );

        if (! $host) {
            return;
        }

        $keys = array_column($pool, 'key');
        $modelByKey = array_column($pool, 'model', 'key');

        Http::globalMiddleware(new DinoikiFailoverMiddleware(
            $keys,
            $host,
            fn (string $key, int $seconds) => $this->markDown($key, $seconds),
            function (string $from, string $to, string $reason) use ($modelByKey): void {
                Log::warning('Dinoiki failover: pindah key.', [
                    'from' => $this->mask($from),
                    'to' => $this->mask($to),
                    'to_model' => $modelByKey[$to] ?? null,
                    'reason' => $reason,
                ]);
            },
            $modelByKey
        ));
    }

    private function markDown(string $key, int $seconds): void
    {
        try {
            Cache::put(self::DOWN_PREFIX . md5($key), true, $seconds);
        } catch (\Throwable $exception) {
        }
    }

    private function isDown(string $key): bool
    {
        try {
            return (bool) Cache::get(self::DOWN_PREFIX . md5($key), false);
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function mask(string $key): string
    {
        return substr($key, 0, 6) . '...' . substr($key, -4);
    }

    private function pickIndex(int $total): int
    {
        try {
            $counter = Cache::increment('dinoiki_key_rotation_counter');

            return (int) (($counter - 1) % $total);
        } catch (\Throwable $exception) {
            Log::warning(
                'Dinoiki key rotation: cache increment gagal, pakai pemilihan acak.',
                ['message' => $exception->getMessage()]
            );

            return random_int(0, $total - 1);
        }
    }
}
