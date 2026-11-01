<?php

namespace App\Services\Learning;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Saran perbaikan Modul 5 (Writing & Speaking — Opsi 2: "Bantuan
 * Sebelum Menulis/Merekam").
 *
 * BERDAMPINGAN dengan FeedbackExplainer (Opsi 3, sudah selesai) —
 * bukan menggantikannya. Kalau FeedbackExplainer membantu siswa
 * memahami feedback SETELAH dinilai, service ini membantu siswa
 * SEBELUM mulai mengerjakan sama sekali.
 *
 * Sama seperti FeedbackExplainer: TIDAK ADA guardrail anti-bocor
 * kunci jawaban — soal Writing/Speaking tidak punya "jawaban benar"
 * tunggal untuk dirahasiakan, jadi konsepnya beda total dari
 * ScaffoldingEngine (Modul 3, untuk soal pilihan ganda).
 *
 * TIDAK PERNAH menyentuh alur submit()/penilaian AI — murni
 * memberi saran berdasarkan TEKS SOAL/SKENARIO saja, sebelum siswa
 * menjawab apa pun.
 */
class PreTaskScaffoldService
{
    private const HTTP_TIMEOUT_SECONDS = 20;

    /**
     * @return array{
     *     structure_tip: string,
     *     vocabulary_suggestions: string[],
     *     grammar_tip: string,
     *     mechanics_tip: ?string,
     * }|null
     */
    public function suggest(string $skill, string $promptText): ?array
    {
        if (! $this->llmConfigured()) {
            return null;
        }

        if (trim($promptText) === '') {
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
                        ['role' => 'system', 'content' => $this->systemPrompt($skill)],
                        ['role' => 'user', 'content' => $this->buildPrompt($skill, $promptText)],
                    ],
                    'max_completion_tokens' => 500,
                ]);

            if (! $response->successful()) {
                Log::warning('PreTaskScaffoldService: LLM request failed.', [
                    'status' => $response->status(),
                ]);

                return null;
            }

            $content = data_get($response->json(), 'choices.0.message.content', '');
            $content = $this->cleanJsonResponse((string) $content);
            $decoded = json_decode($content, true);

            if (
                ! is_array($decoded)
                || ! isset($decoded['structure_tip'], $decoded['grammar_tip'])
                || trim((string) $decoded['structure_tip']) === ''
            ) {
                Log::warning('PreTaskScaffoldService: LLM returned invalid JSON.');

                return null;
            }

            $vocab = $decoded['vocabulary_suggestions'] ?? [];
            if (! is_array($vocab)) {
                $vocab = [];
            }
            $vocab = array_values(array_filter(
                array_map('strval', $vocab),
                fn ($v) => trim($v) !== ''
            ));

            return [
                'structure_tip' => trim((string) $decoded['structure_tip']),
                'vocabulary_suggestions' => $vocab,
                'grammar_tip' => trim((string) $decoded['grammar_tip']),
                'mechanics_tip' => isset($decoded['mechanics_tip']) && trim((string) $decoded['mechanics_tip']) !== ''
                    ? trim((string) $decoded['mechanics_tip'])
                    : null,
            ];
        } catch (Throwable $exception) {
            Log::warning('PreTaskScaffoldService: failed to generate suggestion.', [
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function llmConfigured(): bool
    {
        return (bool) config('services.dinoiki.key');
    }

    private function systemPrompt(string $skill): string
    {
        if ($skill === 'writing') {
            return 'You help Indonesian vocational high school (SMK) students PREPARE to write a short '
                . 'English narrative text (structure: Orientation - introduce characters/setting, '
                . 'Complication - a problem arises, Resolution - the problem is solved), BEFORE they '
                . 'start writing. Given the writing prompt, provide: '
                . '(1) structure_tip: a short reminder (in Indonesian) of the Orientation-Complication-'
                . 'Resolution structure, tailored to this specific prompt; '
                . '(2) vocabulary_suggestions: 5-8 relevant English words/phrases for THIS topic, each '
                . 'formatted as "word - short Indonesian meaning"; '
                . '(3) grammar_tip: one short tip (Indonesian) about a grammar point relevant to '
                . 'narrative writing (e.g., past tense) for this prompt; '
                . '(4) mechanics_tip: one short tip (Indonesian) about punctuation/capitalization. '
                . 'NEVER write example sentences that could be copied directly into the answer, and '
                . 'never write the story/answer yourself — only give preparation tips. '
                . 'Return ONLY valid JSON: {"structure_tip": "<string>", "vocabulary_suggestions": '
                . '["<string>", ...], "grammar_tip": "<string>", "mechanics_tip": "<string>"}. '
                . 'No markdown, no code fences, no extra commentary.';
        }

        return 'You help Indonesian vocational high school (SMK) students PREPARE for a short spoken '
            . 'English task, BEFORE they start recording. Given the speaking scenario/prompt, provide: '
            . '(1) structure_tip: a short reminder (in Indonesian) of how to organize what they will '
            . 'say for THIS specific scenario (e.g., greeting, main point, closing); '
            . '(2) vocabulary_suggestions: 5-8 relevant English words/phrases for THIS topic, each '
            . 'formatted as "word - short Indonesian meaning"; '
            . '(3) grammar_tip: one short tip (Indonesian) about a grammar point relevant to this '
            . 'scenario. Set mechanics_tip to null (not applicable for speaking). '
            . 'NEVER write example full sentences that could just be read aloud as the answer, and '
            . 'never perform the task for them — only give preparation tips. '
            . 'Return ONLY valid JSON: {"structure_tip": "<string>", "vocabulary_suggestions": '
            . '["<string>", ...], "grammar_tip": "<string>", "mechanics_tip": null}. '
            . 'No markdown, no code fences, no extra commentary.';
    }

    private function buildPrompt(string $skill, string $promptText): string
    {
        $skillLabel = $skill === 'writing' ? 'WRITING PROMPT' : 'SPEAKING SCENARIO';

        return <<<PROMPT
{$skillLabel}:
{$promptText}

Give preparation tips as described in your instructions.
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
