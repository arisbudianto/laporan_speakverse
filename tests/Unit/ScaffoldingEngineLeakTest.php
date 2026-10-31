<?php

namespace Tests\Unit;

use App\Services\Learning\ScaffoldingEngine;
use PHPUnit\Framework\TestCase;

/**
 * Modul 3 — LLM Scaffolding Engine.
 *
 * "Unit test: 20 kasus 'apakah output membocorkan kunci'. Ini wajib
 * sebelum M5 live ke siswa." (dokumen rencana modul)
 *
 * Ini menguji ScaffoldingEngine::outputLeaks() langsung — TIDAK
 * memanggil LLM sungguhan (tidak butuh jaringan/API key), murni
 * menguji logika guardrail-nya sendiri secara deterministik.
 */
class ScaffoldingEngineLeakTest extends TestCase
{
    private ScaffoldingEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new ScaffoldingEngine();
    }

    // ------------------------------------------------------------------
    // Kasus yang HARUS terdeteksi sebagai kebocoran (11 kasus)
    // ------------------------------------------------------------------

    public function test_leak_01_explicit_english_the_correct_answer_is(): void
    {
        $this->assertLeaks(
            'The correct answer is B because it matches the passage.',
            [],
            'B'
        );
    }

    public function test_leak_02_explicit_indonesian_jawaban_yang_benar_adalah(): void
    {
        $this->assertLeaks(
            'Jawaban yang benar adalah opsi C.',
            [],
            'C'
        );
    }

    public function test_leak_03_explicit_kunci_jawaban(): void
    {
        $this->assertLeaks(
            'Ingat, kunci jawabannya ada di paragraf dua.',
            [],
            'A'
        );
    }

    public function test_leak_04_option_letter_pattern_english(): void
    {
        $this->assertLeaks(
            'Option D is correct because it matches the context.',
            [],
            'D'
        );
    }

    public function test_leak_05_pilihan_letter_pattern_indonesian(): void
    {
        $this->assertLeaks(
            'Pilihan A benar karena sesuai dengan teks.',
            [],
            'A'
        );
    }

    public function test_leak_06_verbatim_correct_answer_text(): void
    {
        $correctText = 'the government implemented stricter regulations to protect the environment';

        $this->assertLeaks(
            'The government implemented stricter regulations to protect the environment is mentioned in paragraph two.',
            [],
            'B',
            $correctText
        );
    }

    public function test_leak_07_verbatim_correct_answer_text_different_case(): void
    {
        $correctText = 'the government implemented stricter regulations to protect the environment';

        $this->assertLeaks(
            'THE GOVERNMENT IMPLEMENTED STRICTER REGULATIONS TO PROTECT THE ENVIRONMENT — that is the key idea.',
            [],
            'B',
            $correctText
        );
    }

    public function test_leak_08_leak_inside_socratic_question(): void
    {
        $this->assertLeaks(
            null,
            [
                'What is the main topic of this paragraph?',
                'The correct answer is B, do you see why?',
            ],
            'B'
        );
    }

    public function test_leak_09_english_answer_is_option_pattern(): void
    {
        $this->assertLeaks(
            'Just so you know, the answer is option E.',
            [],
            'E'
        );
    }

    public function test_leak_10_opsi_letter_pattern(): void
    {
        $this->assertLeaks(
            'Opsi B adalah jawaban yang tepat di sini.',
            [],
            'B'
        );
    }

    public function test_leak_11_letter_adalah_benar_with_filler_words(): void
    {
        $this->assertLeaks(
            'B adalah pilihan yang benar untuk soal ini.',
            [],
            'B'
        );
    }

    // ------------------------------------------------------------------
    // Kasus yang HARUS dinyatakan AMAN, tidak bocor (9 kasus)
    // ------------------------------------------------------------------

    public function test_safe_01_level1_lexical_hint(): void
    {
        $this->assertSafe(
            'Cek ulang frasa di kalimat kedua paragraf 1. Fokus pada arti contextual, bukan arti kamus pertama.',
            [],
            'B'
        );
    }

    public function test_safe_02_level2_inferential_elaboration(): void
    {
        $this->assertSafe(
            'Soal ini meminta inferensi. Jawaban tidak tertulis eksplisit; gabungkan petunjuk X dan Y.',
            [],
            'C'
        );
    }

    public function test_safe_03_level3_socratic_questions(): void
    {
        $this->assertSafe(
            null,
            [
                'Informasi apa saja di teks yang kamu temukan berhubungan dengan pertanyaan ini?',
                'Kalau informasi itu digabungkan, kesimpulan apa yang masuk akal?',
                'Apakah kesimpulan itu cocok dengan salah satu pilihan jawaban?',
            ],
            'A'
        );
    }

    public function test_safe_04_standalone_letter_as_article_not_leak(): void
    {
        // "A" di sini adalah kata sandang bahasa Inggris biasa, bukan
        // menyebut opsi A — guardrail tidak boleh salah tangkap ini.
        $this->assertSafe(
            'A key detail is mentioned in paragraph two.',
            [],
            'A'
        );
    }

    public function test_safe_05_generic_definition_hint_short_correct_text(): void
    {
        $this->assertSafe(
            'Kata itu punya beberapa arti; coba pikirkan definisi yang paling cocok dengan konteks kalimat ini.',
            [],
            'D',
            'beautiful scenery'
        );
    }

    public function test_safe_06_short_accidental_word_overlap_under_threshold(): void
    {
        // correct_answer_text pendek ("run") sengaja di bawah ambang
        // MIN_LEAK_MATCH_LENGTH supaya kata pendek yang kebetulan
        // muncul di hint tidak salah dianggap kebocoran.
        $this->assertSafe(
            'Coba baca lagi teksnya untuk cari petunjuk lebih lanjut.',
            [],
            'C',
            'run'
        );
    }

    public function test_safe_07_long_correct_text_but_paraphrased_not_verbatim(): void
    {
        $correctText = 'photosynthesis converts sunlight into chemical energy for plant growth';

        $this->assertSafe(
            'Plants use sunlight to create energy through a biological process — think about what that process is called.',
            [],
            'B',
            $correctText
        );
    }

    public function test_safe_08_syntactic_hint_no_letter_mentioned(): void
    {
        $this->assertSafe(
            'Perhatikan kata ganti di awal kalimat kedua — ke mana kira-kira rujukannya?',
            [],
            'C'
        );
    }

    public function test_safe_09_socratic_generic_pilihan_mana_no_specific_letter(): void
    {
        $this->assertSafe(
            null,
            [
                'Apa yang kamu tahu tentang topik ini secara umum?',
                'Sekarang, abaikan itu sebentar — apa yang benar-benar tertulis di paragraf ini?',
                'Berdasarkan itu saja, pilihan mana yang paling didukung teks?',
            ],
            'D'
        );
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function assertLeaks(?string $hintText, array $socraticQuestions, ?string $correctLetter, ?string $correctText = null): void
    {
        $this->assertTrue(
            $this->engine->outputLeaks(
                ['hint_text' => $hintText, 'socratic_questions' => $socraticQuestions],
                ['correct_answer' => $correctLetter, 'correct_answer_text' => $correctText]
            ),
            'Diharapkan guardrail mendeteksi ini sebagai kebocoran, tapi dinyatakan aman.'
        );
    }

    private function assertSafe(?string $hintText, array $socraticQuestions, ?string $correctLetter, ?string $correctText = null): void
    {
        $this->assertFalse(
            $this->engine->outputLeaks(
                ['hint_text' => $hintText, 'socratic_questions' => $socraticQuestions],
                ['correct_answer' => $correctLetter, 'correct_answer_text' => $correctText]
            ),
            'Diharapkan guardrail menyatakan ini aman, tapi malah terdeteksi sebagai kebocoran (false positive).'
        );
    }
}
