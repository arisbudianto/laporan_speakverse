<?php

namespace App\Services\Learning;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

/**
 * Modul 3 — LLM Scaffolding Engine.
 *
 * Satu service yang menghasilkan bantuan 3 level (hint ringan,
 * elaborasi, guided reasoning/Socratic) TANPA membocorkan kunci
 * jawaban. Dipakai oleh Modul 4 (menentukan level) dan Modul 5 (UI
 * kuis) — service ini sendiri TIDAK punya endpoint HTTP, murni logic
 * layer yang dipanggil dari controller lain nanti.
 *
 * URUTAN WAJIB sesuai dokumen rencana: template rule-based DULU
 * (baseline & fallback), LLM baru MENAMBAH di atasnya — bukan
 * menggantikan. Kalau LLM gagal, timeout, atau lolos guardrail
 * dengan hasil yang membocorkan kunci, sistem otomatis jatuh ke
 * template rule-based, bukan gagal total.
 */
class ScaffoldingEngine
{
    /**
     * Dicatat di setiap hasil supaya eksperimen DBR bisa diulang
     * persis dengan prompt yang sama. Naikkan angka ini setiap kali
     * isi prompt di buildPrompt() diubah.
     */
    private const PROMPT_VERSION = 'v1';

    /**
     * Panjang minimal (karakter, setelah dinormalisasi) potongan
     * correct_answer_text yang kalau ditemukan verbatim di output
     * dianggap kebocoran. Angka kecil berisiko false positive
     * (kata pendek yang kebetulan sama), angka besar berisiko lolos
     * kebocoran kalimat pendek — 20 dipilih sebagai titik awal,
     * silakan di-tuning setelah lihat data nyata dari DBR.
     */
    private const MIN_LEAK_MATCH_LENGTH = 20;

    private const HTTP_TIMEOUT_SECONDS = 20;

    /**
     * Titik masuk utama.
     *
     * @param array{
     *     skill?: string,              // 'reading'|'vocabulary' — dipakai untuk kunci cache, opsional tapi disarankan
     *     question_id?: int,           // dipakai untuk kunci cache, opsional tapi disarankan
     *     text: string,               // potongan passage yang relevan SAJA, bukan seluruh teks
     *     question: string,
     *     options: array<string,string>, // ['A' => '...', 'B' => '...', ...] TANPA menandai mana yang benar
     *     error_code: ?string,        // dari Modul 2, salah satu App\Services\Learning\DiagnosticEngine::ERROR_CODES
     *     level: int,                 // 1-3, dari Modul 4
     *     hint_history?: string[],    // hint yang sudah pernah ditampilkan untuk soal ini, supaya tidak diulang
     *     cefr_band?: ?string,        // mis. 'A2', 'B1' — dipakai LLM untuk menyesuaikan kompleksitas bahasa
     *     correct_answer?: ?string,   // HANYA untuk guardrail internal — TIDAK PERNAH dikirim ke LLM
     *     correct_answer_text?: ?string, // HANYA untuk guardrail internal — TIDAK PERNAH dikirim ke LLM
     * } $context
     * @param bool $forceTemplateOnly Modul 6: paksa selalu pakai
     *        template rule-based, walau LLM terkonfigurasi (guru
     *        menonaktifkan LLM untuk siswa/kelas ini).
     * @param int|null $userId Saran perbaikan (kontrol biaya): dipakai
     *        untuk batas jumlah panggilan LLM per siswa per hari. Null
     *        berarti tidak ada pembatasan (dipakai lewat command/tes
     *        internal, bukan dari request siswa sungguhan).
     *
     * @return array{level:int, error_code:?string, hint_text:?string, socratic_questions:string[], reveal_risk:string, source:'rule'|'llm', prompt_version:string}
     */
    public function generate(array $context, bool $forceTemplateOnly = false, ?int $userId = null): array
    {
        $level = max(1, min(3, (int) ($context['level'] ?? 1)));
        $errorCode = $context['error_code'] ?? null;

        $ruleBased = $this->ruleBasedHint($errorCode, $level);

        // Modul 6 — Teacher HITL Override: guru bisa menonaktifkan
        // LLM untuk siswa/kelas tertentu ("pakai template saja").
        // Dicek DULU, sebelum llmConfigured(), supaya override guru
        // selalu dihormati terlepas dari apakah LLM terkonfigurasi.
        if ($forceTemplateOnly || ! $this->llmConfigured()) {
            return $ruleBased;
        }

        /*
        |--------------------------------------------------------------------------
        | Saran perbaikan — kontrol biaya LLM, bagian 1: cache.
        |--------------------------------------------------------------------------
        |
        | Hint level 1/2 & set pertanyaan Socratic level 3 untuk soal
        | yang SAMA + error_code + level + rentang CEFR yang sama,
        | dengan hint_history KOSONG (permintaan hint pertama untuk
        | soal itu — kasus paling sering terjadi, dan paling banyak
        | "diulang" antar siswa berbeda yang kebetulan dapat soal &
        | error_code yang sama), dibagi lintas SEMUA siswa lewat
        | cache. Begitu hint_history sudah terisi (siswa sudah minta
        | hint sebelumnya untuk soal ini, butuh personalisasi supaya
        | tidak mengulang), cache SENGAJA dilewati — selalu panggilan
        | baru, demi menghormati hint_history.
        |
        | Efek samping yang disengaja: ini juga menghemat panggilan
        | LLM saat SATU siswa menekan "lanjut" beberapa kali di level
        | 3 (Socratic) untuk soal yang sama — sebelumnya tiap langkah
        | memicu panggilan LLM baru walau isinya semestinya sama,
        | sekarang cukup 1 panggilan nyata untuk seluruh rangkaian
        | pertanyaan Socratic itu.
        */
        $cacheKey = $this->cacheKeyFor($context, $level, $errorCode);

        if ($cacheKey !== null) {
            $cached = Cache::get($cacheKey);

            if (is_array($cached)) {
                return $cached;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Saran perbaikan — kontrol biaya LLM, bagian 2: rate limit.
        |--------------------------------------------------------------------------
        |
        | Dicek SETELAH cache (cache hit tidak memanggil API sama
        | sekali, jadi tidak perlu dihitung ke jatah harian). Begitu
        | limit tercapai, fallback ke template — siswa TETAP dapat
        | bantuan, cuma bukan dari LLM lagi hari itu.
        */
        if ($userId !== null && ! $this->withinRateLimit($userId)) {
            Log::info('ScaffoldingEngine: LLM daily rate limit reached, falling back to template.', [
                'user_id' => $userId,
            ]);

            return $ruleBased;
        }

        try {
            $llmResult = $this->generateWithLlm($context, $level, $errorCode);

            if ($llmResult === null) {
                return $ruleBased;
            }

            if ($this->outputLeaks($llmResult, $context)) {
                Log::warning('ScaffoldingEngine: LLM output failed leak guardrail, falling back to template.', [
                    'error_code' => $errorCode,
                    'level' => $level,
                ]);

                return $ruleBased;
            }

            $result = [
                'level' => $level,
                'error_code' => $errorCode,
                'hint_text' => $llmResult['hint_text'] ?? null,
                'socratic_questions' => $llmResult['socratic_questions'] ?? [],
                'reveal_risk' => $llmResult['reveal_risk'] ?? 'unknown',
                'source' => 'llm',
                'prompt_version' => self::PROMPT_VERSION,
            ];

            if ($cacheKey !== null) {
                Cache::put($cacheKey, $result, now()->addDays((int) config('learning.llm.cache_days', 14)));
            }

            return $result;
        } catch (Throwable $exception) {
            // Sama seperti service Modul 1/2/4 lainnya: kegagalan LLM
            // TIDAK BOLEH menggagalkan pengalaman siswa. Selalu ada
            // fallback yang bekerja.
            Log::warning('ScaffoldingEngine: LLM call failed, falling back to template.', [
                'error_code' => $errorCode,
                'level' => $level,
                'exception' => $exception->getMessage(),
            ]);

            return $ruleBased;
        }
    }

    private function llmConfigured(): bool
    {
        return (bool) config('services.dinoiki.key');
    }

    /**
     * Saran perbaikan — kontrol biaya: kunci cache dari isi soal, BUKAN
     * dari siapa siswanya, supaya bisa dibagi antar siswa berbeda.
     * Sengaja return null (= "jangan pakai cache") kalau:
     * - question_id/skill tidak disediakan pemanggil (tidak bisa
     *   membuat kunci yang aman), atau
     * - hint_history TIDAK kosong (siswa ini sudah pernah dapat hint
     *   untuk soal ini — perlu personalisasi supaya tidak mengulang,
     *   cache di sini justru bisa merugikan, bukan menghemat).
     */
    private function cacheKeyFor(array $context, int $level, ?string $errorCode): ?string
    {
        $skill = $context['skill'] ?? null;
        $questionId = $context['question_id'] ?? null;

        if ($skill === null || $questionId === null) {
            return null;
        }

        if (! empty($context['hint_history'])) {
            return null;
        }

        // A1/A2 dapat glossary Indonesia singkat di prompt (lihat
        // systemPrompt()), band lain tidak — cukup 2 kelompok supaya
        // cache tidak pecah jadi terlalu banyak varian kecil.
        $cefrBucket = in_array($context['cefr_band'] ?? null, ['A1', 'A2'], true)
            ? 'needs_gloss'
            : 'standard';

        return sprintf(
            'scaffolding_hint:%s:%s:%s:%s:%s:%s',
            self::PROMPT_VERSION,
            $skill,
            $questionId,
            $level,
            $errorCode ?? 'none',
            $cefrBucket
        );
    }

    /**
     * Saran perbaikan — kontrol biaya: batas panggilan LLM per siswa
     * per hari. Memakai RateLimiter bawaan Laravel dengan jendela 24
     * jam bergulir (bukan reset jam 00:00 — cukup untuk kebutuhan
     * ini, lebih sederhana daripada job terjadwal).
     */
    private function withinRateLimit(int $userId): bool
    {
        $maxPerDay = (int) config('learning.llm.max_hint_calls_per_user_per_day', 30);
        $key = 'scaffolding-llm:' . $userId;

        if (RateLimiter::tooManyAttempts($key, $maxPerDay)) {
            return false;
        }

        RateLimiter::hit($key, 86400);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | TEMPLATE RULE-BASED (baseline & fallback — dibangun LEBIH DULU
    | dari LLM, sesuai instruksi dokumen rencana)
    |--------------------------------------------------------------------------
    */

    /**
     * Template generik per error_code × level. Ini SENGAJA generik
     * (tidak spesifik ke satu soal tertentu) karena tidak bergantung
     * konten soal — begitu LLM aktif dan lolos guardrail, siswa akan
     * dapat hint yang lebih kontekstual dari generate(); template ini
     * murni jaring pengaman.
     */
    private function ruleBasedHint(?string $errorCode, int $level): array
    {
        $templates = $this->templates();
        $code = $errorCode && isset($templates[$errorCode]) ? $errorCode : 'inferential';

        $entry = $templates[$code][$level];

        return [
            'level' => $level,
            'error_code' => $errorCode,
            'hint_text' => $entry['hint_text'],
            'socratic_questions' => $entry['socratic_questions'],
            'reveal_risk' => 'none',
            'source' => 'rule',
            'prompt_version' => self::PROMPT_VERSION,
        ];
    }

    private function templates(): array
    {
        return [
            'lexical' => [
                1 => [
                    'hint_text' => 'Perhatikan lagi kata atau frasa yang jadi fokus soal ini. Kata itu bisa punya makna khusus sesuai konteks kalimat — bukan arti kamus yang paling umum kamu tahu.',
                    'socratic_questions' => [],
                ],
                2 => [
                    'hint_text' => 'Soal ini menguji vocabulary in context: kata yang sama bisa berarti berbeda tergantung kalimatnya. Baca satu kalimat penuh di sekitar kata itu dulu, jangan menilai artinya dari kata itu sendiri saja.',
                    'socratic_questions' => [],
                ],
                3 => [
                    'hint_text' => null,
                    'socratic_questions' => [
                        'Kata atau frasa apa yang paling membuatmu ragu di soal ini?',
                        'Kalau kata itu diganti sinonim yang kamu tahu, apakah kalimatnya tetap masuk akal?',
                        'Dari semua pilihan, mana yang paling dekat dengan makna itu dalam konteks kalimat ini?',
                    ],
                ],
            ],

            'inferential' => [
                1 => [
                    'hint_text' => 'Jawaban soal ini tidak tertulis langsung kata per kata di teks — coba cari petunjuk yang tersebar di lebih dari satu kalimat.',
                    'socratic_questions' => [],
                ],
                2 => [
                    'hint_text' => 'Soal ini meminta kesimpulan (inference), bukan kutipan langsung. Kumpulkan dua atau lebih informasi dari teks, lalu tanyakan pada diri sendiri: kesimpulan apa yang paling masuk akal dari gabungan itu?',
                    'socratic_questions' => [],
                ],
                3 => [
                    'hint_text' => null,
                    'socratic_questions' => [
                        'Informasi apa saja di teks yang terasa berhubungan dengan pertanyaan ini?',
                        'Kalau informasi-informasi itu digabungkan, kesimpulan apa yang paling masuk akal?',
                        'Apakah kesimpulan itu cocok dengan salah satu pilihan jawaban yang ada?',
                    ],
                ],
            ],

            'syntactic' => [
                1 => [
                    'hint_text' => 'Perhatikan struktur kalimatnya. Ada kata ganti (pronoun) atau kata rujukan (reference) di sekitar situ — kira-kira merujuk ke bagian mana dari teks?',
                    'socratic_questions' => [],
                ],
                2 => [
                    'hint_text' => 'Soal ini tentang rujukan gramatikal dalam kalimat. Coba telusuri mundur dari kata ganti tersebut ke kata benda terdekat sebelumnya yang cocok secara makna dan tata bahasa.',
                    'socratic_questions' => [],
                ],
                3 => [
                    'hint_text' => null,
                    'socratic_questions' => [
                        'Kata ganti atau rujukan mana yang dimaksud soal ini?',
                        'Coba baca ulang satu-dua kalimat sebelumnya — kata benda apa yang paling mungkin dirujuk?',
                        'Apakah itu cocok secara tata bahasa dan makna dengan jawabanmu?',
                    ],
                ],
            ],

            'context_misconception' => [
                1 => [
                    'hint_text' => 'Coba fokus lagi ke apa yang benar-benar tertulis di teks ini, bukan pengetahuan umum yang mungkin sudah kamu tahu sebelumnya tentang topik ini.',
                    'socratic_questions' => [],
                ],
                2 => [
                    'hint_text' => 'Kadang pengetahuan dari luar teks justru membuat kita salah pilih jawaban. Untuk soal ini, jawab HANYA berdasarkan apa yang tertulis eksplisit di paragraf, bukan yang kamu tahu secara umum.',
                    'socratic_questions' => [],
                ],
                3 => [
                    'hint_text' => null,
                    'socratic_questions' => [
                        'Apa yang kamu tahu tentang topik ini dari luar teks?',
                        'Sekarang coba abaikan itu sebentar — apa yang PERSIS tertulis di paragraf soal ini?',
                        'Berdasarkan itu saja (bukan pengetahuan umummu), pilihan mana yang paling didukung teks?',
                    ],
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | LLM LAYER (guardrail SEBELUM dan SESUDAH panggilan API)
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{hint_text:?string, socratic_questions:string[], reveal_risk:string}|null
     */
    private function generateWithLlm(array $context, int $level, ?string $errorCode): ?array
    {
        $apiKey = config('services.dinoiki.key');

        $baseUrl = rtrim(
            (string) config('services.dinoiki.base_url', 'https://ai.dinoiki.com/v1'),
            '/'
        );

        $model = config('services.dinoiki.chat_model', 'gpt-4o');

        $prompt = $this->buildPrompt($context, $level, $errorCode);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout(self::HTTP_TIMEOUT_SECONDS)
            ->post($baseUrl . '/chat/completions', [
                'model' => $model,

                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->systemPrompt(),
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],

                // CATATAN: parameter 'temperature' SENGAJA tidak
                // dikirim — model yang dipakai (services.dinoiki.chat_model)
                // menolak nilai selain default (1) dan akan
                // mengembalikan HTTP 400 kalau dipaksakan.
                'max_completion_tokens' => 400,
            ]);

        if (! $response->successful()) {
            Log::warning('ScaffoldingEngine: LLM request failed.', [
                'status' => $response->status(),
            ]);

            return null;
        }

        $content = data_get($response->json(), 'choices.0.message.content', '');
        $content = $this->cleanJsonResponse((string) $content);
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            Log::warning('ScaffoldingEngine: LLM returned invalid JSON.');

            return null;
        }

        $socraticQuestions = $decoded['socratic_questions'] ?? [];
        if (! is_array($socraticQuestions)) {
            $socraticQuestions = [];
        }
        $socraticQuestions = array_values(array_filter(
            array_map('strval', $socraticQuestions),
            fn ($q) => trim($q) !== ''
        ));

        return [
            'hint_text' => isset($decoded['hint_text']) && trim((string) $decoded['hint_text']) !== ''
                ? trim((string) $decoded['hint_text'])
                : null,
            'socratic_questions' => $socraticQuestions,
            'reveal_risk' => (string) ($decoded['reveal_risk'] ?? 'unknown'),
        ];
    }

    /**
     * ATURAN GUARDRAIL INPUT (sebelum panggilan):
     * - Hanya kirim potongan teks yang relevan ($context['text']),
     *   bukan keseluruhan passage.
     * - correct_answer / correct_answer_text TIDAK PERNAH masuk ke
     *   prompt ini — keduanya hanya dipakai di outputLeaks() setelah
     *   respons LLM diterima.
     */
    private function systemPrompt(): string
    {
        return 'You are an English reading/vocabulary tutor for Indonesian vocational high school '
            . 'students. You give scaffolded hints WITHOUT EVER revealing which option is correct — '
            . 'not the letter, not the exact wording of the correct option, and not close paraphrases '
            . 'of it. Never say things like "the answer is X" or "option X is correct" in any language. '
            . 'Level 1 = a light hint pointing attention to a part of the text or a word type, no '
            . 'explanation of the answer. Level 2 = explain the relevant reading concept or strategy '
            . '(matching the given error type), still without naming the correct option. Level 3 = '
            . '2 to 3 short, sequential Socratic guiding questions (no hint_text) that lead the student '
            . 'toward reasoning it out themselves. Use simple English; if a CEFR band is given and it is '
            . 'A1/A2, add a very short Indonesian gloss in parentheses for any non-basic word you use. '
            . 'Never repeat a hint already listed in hint_history verbatim. Return ONLY a valid JSON '
            . 'object: {"hint_text": string|null, "socratic_questions": string[], "reveal_risk": '
            . '"none"|"low"|"high"}. socratic_questions must be empty for level 1 and 2, and hint_text '
            . 'must be null for level 3. Do not return Markdown or code fences.';
    }

    private function buildPrompt(array $context, int $level, ?string $errorCode): string
    {
        $optionsList = '';
        foreach (($context['options'] ?? []) as $letter => $text) {
            $optionsList .= "{$letter}. {$text}\n";
        }

        $hintHistory = $context['hint_history'] ?? [];
        $hintHistoryText = $hintHistory === []
            ? '(none yet)'
            : implode("\n", array_map(fn ($h) => '- ' . $h, $hintHistory));

        $cefrBand = $context['cefr_band'] ?? 'unknown';

        return <<<PROMPT
RELEVANT TEXT SPAN:
{$context['text']}

QUESTION:
{$context['question']}

OPTIONS (do not indicate which is correct):
{$optionsList}
ERROR TYPE (from diagnostic engine): {$errorCode}
REQUESTED HELP LEVEL: {$level}
STUDENT CEFR BAND: {$cefrBand}

HINTS ALREADY SHOWN FOR THIS QUESTION (do not repeat):
{$hintHistoryText}

Generate the level {$level} scaffolding response as the JSON object described in your instructions.
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

    /*
    |--------------------------------------------------------------------------
    | OUTPUT GUARDRAIL (leak filter — jalan setelah respons LLM
    | diterima, SEBELUM dikembalikan ke pemanggil)
    |--------------------------------------------------------------------------
    */

    /**
     * @param array{hint_text?:?string, socratic_questions?:string[]} $hintOutput
     */
    public function outputLeaks(array $hintOutput, array $context): bool
    {
        $texts = array_filter(
            array_merge(
                [(string) ($hintOutput['hint_text'] ?? '')],
                array_map('strval', $hintOutput['socratic_questions'] ?? [])
            ),
            fn ($text) => trim($text) !== ''
        );

        $correctLetter = $context['correct_answer'] ?? null;
        $correctText = $context['correct_answer_text'] ?? null;

        foreach ($texts as $text) {
            if ($this->textLeaksAnswer($text, $correctLetter, $correctText)) {
                return true;
            }
        }

        return false;
    }

    private function textLeaksAnswer(string $text, ?string $correctLetter, ?string $correctText): bool
    {
        $normalized = $this->normalize($text);

        foreach ($this->explicitLeakPatterns() as $pattern) {
            if (preg_match($pattern, $normalized) === 1) {
                return true;
            }
        }

        if ($correctLetter) {
            $letter = preg_quote(mb_strtolower($correctLetter), '/');

            $letterPatterns = [
                '/\boption ' . $letter . '\b/u',
                '/\bopsi ' . $letter . '\b/u',
                '/\bpilihan ' . $letter . '\b/u',
                '/\b' . $letter . '\s+(is|adalah)\b.{0,20}\b(correct|benar|tepat)\b/u',
            ];

            foreach ($letterPatterns as $pattern) {
                if (preg_match($pattern, $normalized) === 1) {
                    return true;
                }
            }
        }

        if ($correctText) {
            $normalizedCorrect = $this->normalize($correctText);

            if (
                mb_strlen($normalizedCorrect) >= self::MIN_LEAK_MATCH_LENGTH
                && str_contains($normalized, $normalizedCorrect)
            ) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    private function explicitLeakPatterns(): array
    {
        return [
            '/\bjawaban(nya)? (yang )?benar (adalah|itu)\b/u',
            '/\bkunci jawaban(nya)?\b/u',
            '/\bthe correct answer is\b/u',
            '/\bcorrect answer is\b/u',
            '/\banswer is option\b/u',
        ];
    }
}
