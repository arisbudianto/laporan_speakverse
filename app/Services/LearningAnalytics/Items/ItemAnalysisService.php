<?php

namespace App\Services\LearningAnalytics\Items;

use Illuminate\Support\Facades\DB;

/** Loader DB tipis untuk ItemAnalyzer; hasil ditulis ke la_form_stats / la_item_stats (ganti seluruhnya). */
class ItemAnalysisService
{
    public function run(): array
    {
        $rows = [];
        $q = DB::table('assessment_answers as a')
            ->join('assessment_submissions as s', 's.id', '=', 'a.assessment_submission_id')
            ->whereNotNull('a.is_correct')
            ->whereIn('a.question_type', ['reading', 'listening', 'vocabulary'])
            ->orderBy('a.id')
            ->select('a.id', 'a.assessment_submission_id as sub', 's.user_id as user', 's.type', 's.skill', 's.unit_id', 's.lesson_id',
                'a.question_type', 'a.question_id', 'a.selected_option', 'a.is_correct');
        if ($ex = \App\Services\LearningAnalytics\Cohort::excludedIds()) {
            $q->whereNotIn('s.user_id', $ex);
        }
        foreach ($q->cursor() as $r) {
            $rows[] = [
                'sub' => (int) $r->sub, 'user' => (int) $r->user,
                'form' => implode('|', [$r->type, $r->skill, $r->unit_id ?? 0, $r->lesson_id ?? 0]),
                'item' => $r->question_type . ':' . $r->question_id,
                'option' => $r->selected_option, 'correct' => (int) (bool) $r->is_correct,
            ];
        }
        $res = (new ItemAnalyzer())->analyze($rows);
        $now = now();
        DB::transaction(function () use ($res, $now) {
            DB::table('la_item_stats')->delete();
            DB::table('la_form_stats')->delete();
            $ext = \Illuminate\Support\Facades\Schema::hasColumn('la_form_stats', 'n_flagged');
            foreach (array_chunk($res['forms'], 200) as $c) {
                DB::table('la_form_stats')->insert(array_map(fn ($f) => [
                    'form' => $f['form'], 'n' => $f['n'], 'k' => $f['k'], 'mean' => $f['mean'], 'sd' => $f['sd'],
                    'alpha' => $f['alpha'], 'sem' => $f['sem'], 'indicative' => $f['indicative'], 'computed_at' => $now,
                ] + ($ext ? ['n_flagged' => $f['n_flagged'], 'n_flagged_severe' => $f['n_flagged_severe'], 'alpha_without_flagged' => $f['alpha_without_flagged']] : []), $c));
            }
            foreach (array_chunk($res['items'], 300) as $c) {
                DB::table('la_item_stats')->insert(array_map(fn ($i) => [
                    'form' => $i['form'], 'item' => $i['item'], 'n' => $i['n'], 'p' => $i['p'], 'r_pb' => $i['r_pb'], 'd_index' => $i['d_index'],
                    'key_option' => $i['key'], 'options' => json_encode($i['options']), 'flags' => json_encode($i['flags']),
                    'indicative' => $i['indicative'], 'computed_at' => $now,
                ], $c));
            }
        });

        return ['answers' => count($rows), 'forms' => count($res['forms']), 'items' => count($res['items'])];
    }
}
