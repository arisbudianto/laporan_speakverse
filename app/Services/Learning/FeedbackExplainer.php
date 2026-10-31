<?php

namespace App\Services\Learning;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Saran perbaikan Modul 5 (Writing & Speaking — Opsi 3: "Bantu Pahami
 * Feedback").
 *
 * BEDA MENDASAR dari ScaffoldingEngine (Modul 3): di Writing/Speaking
 * tidak ada "kunci jawaban" untuk dijaga kerahasiaannya — feedback
 * AI atas esai/rekaman siswa MEMANG isinya sendiri, bukan sesuatu
 * yang dirahasiakan. Jadi TIDAK ADA guardrail anti-bocor di sini
 * (beda dengan ScaffoldingEngine yang wajib punya itu untuk soal
 * pilihan ganda).
 *
 * Fungsinya sempit dan aman: ambil feedback yang SUDAH ADA dan
 * SUDAH TERSIMPAN (dari alur penilaian AI yang sudah berjalan sejak
 * awal, TIDAK diubah sama sekali oleh service ini), lalu:
 * 1. Menerjemahkan/menyederhanakan jadi Bahasa Indonesia yang lebih
 *    mudah dipahami siswa SMK.
 *  2. Memberi SATU saran konkret untuk percobaan berikutnya.
 *
 * Kalau LLM gagal/tidak terkonfigurasi, kembalikan null — pemanggil
 * (controller) lalu cukup menampilkan feedback ASLI apa adanya,
 * tidak pernah membuat siswa kehilangan feedback sama sekali.
 */
class FeedbackExplainer
{
    private const HTTP_TIMEOUT_SECONDS = 20;

    /**
     * @return array{simple_explanation: string, tip_for_next_time: string}|null
     */
    public function explain(string $originalFeedback, ?int $score, string $skill): ?array
    {
        if (! $this->llmConfigured()) {
            return null;
        }

        if (trim($originalFeedback) === '') {
            return null;
        }

        try {
            $apiKey = config('services.dinoiki.key');

            $baseUrl = rtrim(
                (string) config('services.dinoiki.base_url', 'https://ai.dinoiki.com/v1'),
                '/'
            );

            $model = config('services.dinoiki.chat_model', 'gpt-4o');

            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(self::HTTP_TIMEOUT_SECONDS)
                ->post($baseUrl . '/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user', 'content' => $this->buildPrompt($originalFeedback, $score, $skill)],
                    ],
                    'max_completion_tokens' => 400,
                ]);

            if (! $response->successful()) {
                Log::warning('FeedbackExplainer: LLM request failed.', [
                    'status' => $response->status(),
                ]);

                return null;
            }

            $content = data_get($response->json(), 'choices.0.message.content', '');
            $content = $this->cleanJsonResponse((string) $content);
            $decoded = json_decode($content, true);

            if (
                ! is_array($decoded)
                || ! isset($decoded['simple_explanation'], $decoded['tip_for_next_time'])
                || trim((string) $decoded['simple_explanation']) === ''
            ) {
                Log::warning('FeedbackExplainer: LLM returned invalid JSON.');

                return null;
            }

            return [
                'simple_explanation' => trim((string) $decoded['simple_explanation']),
                'tip_for_next_time' => trim((string) $decoded['tip_for_next_time']),
            ];
        } catch (Throwable $exception) {
            Log::warning('FeedbackExplainer: failed to explain feedback.', [
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function llmConfigured(): bool
    {
        return (bool) config('services.dinoiki.key');
    }

    private function systemPrompt(): string
    {
        return 'You help Indonesian vocational high school (SMK) students understand AI-generated '
            . 'grading feedback on their English writing or speaking submissions. You will be given '
            . 'the original feedback and the score it received. Rewrite it as: '
            . '(1) a short, warm, easy-to-understand explanation IN INDONESIAN of what the feedback '
            . 'means in practice — avoid jargon, use simple everyday words, 2-4 sentences; '
            . '(2) ONE concrete, specific tip in Indonesian for what to focus on next time, phrased '
            . 'as encouragement, not criticism. Do not invent new criticisms not present in the '
            . 'original feedback — only clarify and simplify what is already there. '
            . 'Return ONLY valid JSON: {"simple_explanation": "<string>", "tip_for_next_time": '
            . '"<string>"}. No markdown, no code fences, no extra commentary.';
    }

    private function buildPrompt(string $originalFeedback, ?int $score, string $skill): string
    {
        $scoreLine = $score !== null ? "SCORE: {$score}/100\n" : '';

        return <<<PROMPT
SKILL: {$skill}
{$scoreLine}ORIGINAL FEEDBACK:
{$originalFeedback}

Explain this feedback as described in your instructions.
PROMPT;
    }

    private function cleanJsonResponse(string $content): string
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', (string) $content);

        $firstBrace = strpos((string) $content, '{');
        $lastBrace = strrpos((string) $content, '}');

        if ($firstBrace === false || $lastBrace === false || $lastBrace < $firstBrace) {
            return (string) $content;
        }

        return substr((string) $content, $firstBrace, $lastBrace - $firstBrace + 1);
    }
}
