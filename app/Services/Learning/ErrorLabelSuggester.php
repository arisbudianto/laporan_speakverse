<?php

namespace App\Services\Learning;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Saran perbaikan Modul 2 — Diagnostic Engine.
 *
 * Mengusulkan isi error_if_wrong (peta opsi salah -> kode error) dan
 * rationale untuk soal yang belum dilabel, memakai LLM. SESUAI BATAS
 * yang sudah ditulis di dokumen rencana Modul 2: "LLM hanya boleh
 * mengusulkan label, rule-based yang memutuskan jika opsi sudah
 * dipetakan" — jadi hasil dari class ini SELALU perlu ditinjau
 * manusia (lihat kolom error_labels_source = 'ai_suggested') sebelum
 * benar-benar dipakai DiagnosticEngine untuk menilai siswa.
 *
 * Ini BUKAN bagian dari alur siswa — dipanggil dari
 * `php artisan learning:suggest-error-labels`, bukan dari
 * controller mana pun yang diakses siswa.
 */
class ErrorLabelSuggester
{
    private const HTTP_TIMEOUT_SECONDS = 30;

    /**
     * @param array{
     *     question: string,
     *     options: array<string,string>,
     *     correct_answer: string,
     *     passage?: ?string,
     *     skill: 'reading'|'vocabulary',
     * } $context
     *
     * @return array{error_if_wrong: array<string,string>, rationale: ?string}|null
     */
    public function suggest(array $context): ?array
    {
        if (! $this->llmConfigured()) {
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
                        ['role' => 'user', 'content' => $this->buildPrompt($context)],
                    ],
                    // CATATAN: parameter 'temperature' SENGAJA tidak
                    // dikirim — model yang dipakai (lihat
                    // services.dinoiki.chat_model) menolak nilai
                    // selain default (1) dan akan mengembalikan HTTP
                    // 400 kalau dipaksakan. Biarkan API pakai default-nya.
                    'max_completion_tokens' => 500,
                ]);

            if (! $response->successful()) {
                Log::warning('ErrorLabelSuggester: LLM request failed.', [
                    'status' => $response->status(),
                ]);

                return null;
            }

            $content = data_get($response->json(), 'choices.0.message.content', '');
            $content = $this->cleanJsonResponse((string) $content);
            $decoded = json_decode($content, true);

            if (! is_array($decoded) || ! isset($decoded['error_if_wrong']) || ! is_array($decoded['error_if_wrong'])) {
                Log::warning('ErrorLabelSuggester: LLM returned invalid JSON.');

                return null;
            }

            $validMap = $this->validateMap($decoded['error_if_wrong'], $context);

            if ($validMap === []) {
                return null;
            }

            return [
                'error_if_wrong' => $validMap,
                'rationale' => isset($decoded['rationale']) && trim((string) $decoded['rationale']) !== ''
                    ? trim((string) $decoded['rationale'])
                    : null,
            ];
        } catch (Throwable $exception) {
            Log::warning('ErrorLabelSuggester: failed to get suggestion.', [
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function llmConfigured(): bool
    {
        return (bool) config('services.dinoiki.key');
    }

    /**
     * Tolak apa pun yang bukan salah satu dari 4 kode error tertutup,
     * dan tolak kalau kuncinya adalah opsi yang justru BENAR.
     */
    private function validateMap(array $rawMap, array $context): array
    {
        $validCodes = DiagnosticEngine::ERROR_CODES;
        $correctAnswer = $context['correct_answer'] ?? null;
        $optionLetters = array_keys($context['options'] ?? []);

        $clean = [];

        foreach ($rawMap as $letter => $code) {
            $letter = strtoupper((string) $letter);

            if (! in_array($letter, $optionLetters, true)) {
                continue;
            }

            if ($letter === $correctAnswer) {
                continue;
            }

            if (! is_string($code) || ! in_array($code, $validCodes, true)) {
                continue;
            }

            $clean[$letter] = $code;
        }

        return $clean;
    }

    /**
     * v3 — v2 memperbaiki bias "selalu inferential" (v1), tapi data
     * dari pemakaian nyata menunjukkan bias itu cuma PINDAH ke
     * "selalu context_misconception". Penyebabnya: banyak distraktor
     * reading comprehension sifatnya "sekadar tidak sesuai fakta
     * yang tertulis" — bukan soal kata, tata bahasa, penalaran
     * berlapis, MAUPUN pengetahuan umum yang keliru. Itu jenis
     * kesalahan ke-5 yang TIDAK ADA di 4 kategori taksonomi proposal
     * ini, jadi model asal pilih kategori yang "kedengarannya paling
     * pas" — dan context_misconception jadi favorit baru karena
     * terdengar cocok untuk "opsi yang salah menurut teks".
     *
     * Perbaikan v3: definisi context_misconception dipersempit
     * secara eksplisit TIDAK termasuk kasus itu, plus instruksi ulang
     * yang lebih tegas: kalau tidak ada kategori yang benar-benar pas,
     * LEWATI opsi itu — jangan paksakan ke kategori mana pun,
     * termasuk context_misconception.
     */
    private function systemPrompt(): string
    {
        return <<<PROMPT
You are an English reading/vocabulary assessment expert helping a teacher label multiple-choice questions for Indonesian vocational high school students.

For EACH WRONG option (never the correct one), determine the MOST SPECIFIC reason a student might mistakenly pick it, using EXACTLY ONE of these four codes:

- lexical: The option is attractive because of a WORD-LEVEL misunderstanding — the student misread the meaning of a specific word or phrase (wrong denotation, wrong collocation, wrong word form), NOT because of misreading the overall sentence logic.
  Example: text says "he was reluctant to leave"; a wrong option says "he refused to leave" — picking a near-synonym with a different/stronger meaning than what's actually stated.

- syntactic: The option is attractive because of a GRAMMATICAL or REFERENCE misreading — misidentifying who/what a pronoun refers to, misreading clause structure, or confusing subject/object roles.
  Example: text says "the manager told the clerk that he needed to leave early"; a wrong option assumes "he" refers to the clerk when the text intends the manager.

- context_misconception: The option is attractive SPECIFICALLY because it matches common/general knowledge, stereotypes, or assumptions about the topic that a person could hold WITHOUT ever having read this text — and that outside belief conflicts with what the text actually says.
  Example: assuming a text about London is about rainy weather (a common stereotype), when the text is actually about something else entirely and never mentions weather.
  DO NOT use this code just because an option contradicts an explicitly stated fact. "The text says X but the option says Y" is NOT by itself context_misconception — it only qualifies if Y specifically reflects a plausible outside/prior assumption about the topic, not merely a wrong restatement of the text's content.

- inferential: The option requires COMBINING two or more explicit pieces of information in the text to reach a conclusion, and the wrong option represents a plausible but INCORRECT combination or conclusion — genuinely a multi-step reasoning failure, not simply a word, grammar, or prior-knowledge issue.

A great many wrong options in reading comprehension questions are simply "not what the text states" with no deeper word/grammar/outside-knowledge/multi-step-reasoning explanation for why a student might pick them — for example, a option that just swaps a detail the text stated plainly. THIS COMMON CASE DOES NOT FIT ANY OF THE FOUR CODES WELL. For each wrong option, ask yourself: "Does this specifically fit one of the four definitions above, precisely as defined — not just loosely?" If the honest answer is no (including when the option is simply a plain factual mismatch with no special word/grammar/outside-knowledge/reasoning angle), OMIT that option from error_if_wrong entirely. Do not force it into whichever code sounds closest — an incomplete map that only contains genuinely well-fitting labels is far more useful than a complete one full of loose guesses.

Also write one short rationale (1-2 sentences, in Indonesian) explaining why the correct answer is correct.

Return ONLY valid JSON: {"error_if_wrong": {"<letter>": "<code>", ...}, "rationale": "<string>"}. No markdown, no code fences, no extra commentary.
PROMPT;
    }

    private function buildPrompt(array $context): string
    {
        $optionsList = '';
        foreach (($context['options'] ?? []) as $letter => $text) {
            $optionsList .= "{$letter}. {$text}\n";
        }

        $passage = trim((string) ($context['passage'] ?? ''));
        $passageBlock = $passage !== ''
            ? "PASSAGE:\n{$passage}\n\n"
            : '';

        return <<<PROMPT
{$passageBlock}QUESTION:
{$context['question']}

OPTIONS:
{$optionsList}
CORRECT ANSWER: {$context['correct_answer']}
SKILL: {$context['skill']}

Classify each wrong option and give the rationale, as the JSON object described in your instructions.
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
