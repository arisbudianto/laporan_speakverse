<?php

namespace App\Services\Learning;

use App\Models\TeacherScaffoldingPolicy;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Modul 6 — Teacher HITL Override.
 *
 * Satu-satunya tempat yang tahu cara MENGGABUNGKAN kebijakan
 * per-siswa dan per-kelas (kalau keduanya aktif untuk siswa yang
 * sama). Dipanggil dari:
 * - StudentReadingController::hint() / check()
 * - VocabularyPretestController::hint() / check()
 *
 * Kegagalan di sini TIDAK BOLEH menggagalkan alur siswa — fallback
 * ke "tidak ada override" (null / false), sama seperti service Modul
 * 1-5 lainnya.
 */
class TeacherOverrideService
{
    /**
     * @return array{
     *     level_override: array{freeze_level?: int, max_level?: int, min_level?: int}|null,
     *     require_hint_before_recheck: bool,
     *     disable_llm: bool,
     *     policy_ids: int[],
     * }|null Null kalau tidak ada kebijakan aktif sama sekali untuk siswa ini.
     */
    public function resolveForStudent(User $student): ?array
    {
        try {
            $studentPolicy = TeacherScaffoldingPolicy::query()
                ->active()
                ->where('scope_type', 'student')
                ->where('student_id', $student->id)
                ->latest('id')
                ->first();

            $classPolicy = TeacherScaffoldingPolicy::query()
                ->active()
                ->where('scope_type', 'class')
                ->where('school', $student->school)
                ->where('major', $student->major)
                ->where('grade', $student->grade)
                ->where('parallel', $student->parallel)
                ->latest('id')
                ->first();

            if (! $studentPolicy && ! $classPolicy) {
                return null;
            }

            // Per kolom NUMERIK: kebijakan per-siswa menang kalau
            // diisi, kalau tidak baru pakai punya kelas.
            $pick = function (string $field) use ($studentPolicy, $classPolicy) {
                if ($studentPolicy && $studentPolicy->{$field} !== null) {
                    return $studentPolicy->{$field};
                }

                return $classPolicy->{$field} ?? null;
            };

            $levelOverride = [];

            $freezeLevel = $pick('freeze_level');
            if ($freezeLevel !== null) {
                $levelOverride['freeze_level'] = $freezeLevel;
            }

            $maxLevel = $pick('max_level');
            if ($maxLevel !== null) {
                $levelOverride['max_level'] = $maxLevel;
            }

            $minLevel = $pick('min_level');
            if ($minLevel !== null) {
                $levelOverride['min_level'] = $minLevel;
            }

            // Untuk flag boolean (bukan nilai numerik), dipilih yang
            // PALING KETAT: kalau salah satu (siswa ATAU kelas)
            // mengaktifkannya, itu berlaku — supaya kebijakan kelas
            // yang lebih ketat tidak bisa "dilonggarkan" begitu saja
            // oleh kebijakan per-siswa yang tidak menyebutkannya.
            $requireHintBeforeRecheck = (bool) ($studentPolicy->require_hint_before_recheck ?? false)
                || (bool) ($classPolicy->require_hint_before_recheck ?? false);

            $disableLlm = (bool) ($studentPolicy->disable_llm ?? false)
                || (bool) ($classPolicy->disable_llm ?? false);

            return [
                'level_override' => $levelOverride === [] ? null : $levelOverride,
                'require_hint_before_recheck' => $requireHintBeforeRecheck,
                'disable_llm' => $disableLlm,
                'policy_ids' => array_values(array_filter([
                    $studentPolicy->id ?? null,
                    $classPolicy->id ?? null,
                ])),
            ];
        } catch (Throwable $exception) {
            Log::warning('TeacherOverrideService: failed to resolve override.', [
                'student_id' => $student->id,
                'exception' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
