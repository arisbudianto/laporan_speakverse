<?php

namespace App\Services\LearningAnalytics\Outcome;

use App\Services\LearningAnalytics\Stats\Descriptive as D;

/**
 * Apakah bentuk pretest dan posttest sebanding? (kelas murni)
 *
 * Masukan: butir dengan 'form' ("type|skill|unit|lesson"), 'item' ("question_type:id"), 'p' (kesukaran), 'n'.
 * Per keterampilan:
 *  - rerata p butir pretest vs posttest dan selisihnya (bobot butir sama);
 *  - butir jangkar = butir yang sama (kode butir identik) muncul di pretest dan posttest; selisih rerata p pada jangkar
 *    adalah perubahan "apel-dengan-apel" (n pengamatan ≥ minN per butir pada kedua bentuk);
 *  - sebanding bila |Δp rerata| ≤ ambang (default 0,10).
 * Semua bentuk digabung ('semua') memakai butir seluruh keterampilan.
 */
final class FormComparability
{
    public function __construct(private float $delta = 0.10, private int $minN = 15)
    {
    }

    /** @param array<int,array{form:string,item:string,p:float,n:int}> $items */
    public function analyze(array $items): array
    {
        $by = [];
        foreach ($items as $it) {
            $parts = explode('|', $it['form']);
            $type = $parts[0] ?? '';
            $skill = $parts[1] ?? '';
            if (! in_array($type, ['pretest', 'posttest'], true) || $it['n'] < $this->minN) {
                continue;
            }
            foreach ([$skill, 'semua'] as $k) {
                $by[$k][$type][$it['item']][] = (float) $it['p'];
            }
        }
        ksort($by);
        $rows = [];
        foreach ($by as $skill => $t) {
            $pre = array_map(fn ($v) => D::mean($v), $t['pretest'] ?? []);
            $post = array_map(fn ($v) => D::mean($v), $t['posttest'] ?? []);
            if (! $pre || ! $post) {
                continue;
            }
            $anchor = array_intersect_key($pre, $post);
            $da = $anchor ? D::mean(array_map(fn ($k) => $post[$k] - $pre[$k], array_keys($anchor))) : null;
            $d = D::mean($post) - D::mean($pre);
            $rows[] = ['skill' => $skill, 'items_pre' => count($pre), 'items_post' => count($post), 'p_pre' => round(D::mean($pre), 3), 'p_post' => round(D::mean($post), 3),
                'delta' => round($d, 3), 'anchors' => count($anchor), 'anchor_delta' => $da === null ? null : round($da, 3), 'comparable' => abs($d) <= $this->delta];
        }
        $all = null;
        foreach ($rows as $r) {
            if ($r['skill'] === 'semua') {
                $all = $r;
            }
        }
        $perSkill = array_values(array_filter($rows, fn ($r) => $r['skill'] !== 'semua'));
        $bad = $all ? ! $all['comparable'] : (bool) array_filter($perSkill, fn ($r) => ! $r['comparable']);

        return [
            'rows' => array_merge($perSkill, $all ? [$all] : []),
            'comparable' => ! $bad,
            'has_anchors' => (bool) array_filter($perSkill, fn ($r) => $r['anchors'] > 0),
            'delta_limit' => $this->delta,
            'message' => $bad
                ? 'Skor pre dan post tidak sebanding: butir posttest ' . ($all && $all['delta'] < 0 ? 'lebih sulit' : 'berbeda tingkat kesukarannya') . ' daripada pretest. Selisih skor tidak boleh ditafsirkan sebagai gain belajar.'
                : 'Tingkat kesukaran butir pre dan post relatif sebanding.',
        ];
    }
}
