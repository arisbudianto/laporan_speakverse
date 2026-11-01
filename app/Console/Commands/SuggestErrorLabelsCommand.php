<?php

namespace App\Console\Commands;

use App\Models\ReadingQuestion;
use App\Models\VocabularyPretest;
use App\Services\Learning\ErrorLabelSuggester;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Saran perbaikan Modul 2 — Diagnostic Engine.
 *
 * "Isi error_if_wrong untuk lebih banyak soal" secara massal, dengan
 * bantuan LLM SEBAGAI PENGUSUL saja (lihat ErrorLabelSuggester).
 * Setiap soal yang diberi label lewat perintah ini ditandai
 * error_labels_source='ai_suggested' — WAJIB ditinjau guru/admin di
 * halaman kelola soal sebelum dianggap final. Perintah ini TIDAK
 * mengubah cara DiagnosticEngine menilai siswa sama sekali.
 */
class SuggestErrorLabelsCommand extends Command
{
    protected $signature = 'learning:suggest-error-labels
        {--skill=reading : reading atau vocabulary}
        {--limit=50 : maksimal soal yang diproses dalam satu kali jalan}
        {--dry-run : tampilkan usulan tanpa menyimpan ke database}';

    protected $description = 'Modul 2: usulkan error_if_wrong via LLM untuk soal yang belum dilabel (perlu ditinjau manusia)';

    public function handle(ErrorLabelSuggester $suggester): int
    {
        $skill = (string) $this->option('skill');
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        if (! in_array($skill, ['reading', 'vocabulary'], true)) {
            $this->error("--skill harus 'reading' atau 'vocabulary', bukan '{$skill}'.");

            return self::FAILURE;
        }

        $questions = $skill === 'reading'
            ? ReadingQuestion::with('material')->whereNull('error_if_wrong')->limit($limit)->get()
            : VocabularyPretest::where('category', 'vocabulary')->whereNull('error_if_wrong')->limit($limit)->get();

        if ($questions->isEmpty()) {
            $this->info("Tidak ada soal {$skill} yang belum dilabel saat ini.");

            return self::SUCCESS;
        }

        $this->info(
            "Memproses {$questions->count()} soal {$skill}"
            . ($dryRun ? ' (dry-run, TIDAK akan disimpan)' : '')
            . '...'
        );
        $this->newLine();

        $suggested = 0;
        $skipped = 0;

        foreach ($questions as $question) {
            $context = $skill === 'reading'
                ? $this->contextForReading($question)
                : $this->contextForVocabulary($question);

            $result = $suggester->suggest($context);

            if ($result === null) {
                $skipped++;
                $this->line("  [lewat] Soal #{$question->id}: tidak ada usulan valid dari LLM.");

                continue;
            }

            $this->line("  [dapat] Soal #{$question->id}: " . Str::limit($question->question, 70));
            foreach ($result['error_if_wrong'] as $letter => $code) {
                $this->line("           {$letter} -> {$code}");
            }
            if ($result['rationale']) {
                $this->line('           rationale: ' . Str::limit($result['rationale'], 100));
            }

            if (! $dryRun) {
                $question->error_if_wrong = $result['error_if_wrong'];
                if (empty($question->rationale)) {
                    $question->rationale = $result['rationale'];
                }
                $question->error_labels_source = 'ai_suggested';
                $question->save();
            }

            $suggested++;
        }

        $this->newLine();
        $this->info("Selesai: {$suggested} soal dapat usulan label, {$skipped} dilewati.");

        if (! $dryRun && $suggested > 0) {
            $this->warn(
                'PENTING: label di atas berasal dari AI (belum ditinjau manusia). '
                . 'Buka halaman kelola soal di admin dan tinjau satu per satu sebelum '
                . 'dianggap final — jangan langsung dipakai untuk menilai siswa tanpa dicek.'
            );
        }

        return self::SUCCESS;
    }

    private function contextForReading(ReadingQuestion $question): array
    {
        return [
            'question' => $question->question,
            'options' => $this->nonEmptyOptions($question),
            'correct_answer' => $question->correct_answer,
            'passage' => $question->material->passage ?? null,
            'skill' => 'reading',
        ];
    }

    private function contextForVocabulary(VocabularyPretest $question): array
    {
        return [
            'question' => $question->question,
            'options' => $this->nonEmptyOptions($question),
            'correct_answer' => $question->correct_answer,
            'passage' => null,
            'skill' => 'vocabulary',
        ];
    }

    private function nonEmptyOptions($question): array
    {
        $options = [
            'A' => $question->option_a,
            'B' => $question->option_b,
            'C' => $question->option_c,
            'D' => $question->option_d,
            'E' => $question->option_e,
        ];

        return array_filter($options, fn ($value) => $value !== null && trim((string) $value) !== '');
    }
}
