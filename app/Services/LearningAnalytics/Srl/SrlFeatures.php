<?php

namespace App\Services\LearningAnalytics\Srl;

use App\Services\LearningAnalytics\Stats\Descriptive as D;

/**
 * Fitur perilaku belajar/bantuan (help-seeking, SRL) per siswa dari log kuis (kelas murni).
 *
 * Baris masukan (urut ts): {user, session, q, type, correct:?0|1, attempt:int, hint:?int, ms:?int, ts:int}
 *   type ∈ select|revise|hint_request.   Satu "butir" = pasangan (session, q) yang punya jawaban bernilai (correct ≠ null).
 *
 * Definisi:
 *  first_try_acc     proporsi butir benar pada percobaan pertama
 *  revision_rate     proporsi butir dengan ≥2 percobaan
 *  hint_rate         proporsi butir yang meminta ≥1 hint; hint_l3_rate: yang mencapai level 3
 *  median_ms         median waktu respons percobaan pertama (jam server)
 *  fast_wrong_rate   proporsi percobaan pertama yang SALAH dan dijawab < fastMs (default 3 dtk) — proksi menebak tergesa
 *                    (Baker dkk., 2004 "gaming the system" memakai ide serupa; ini penanda kasar, bukan bukti)
 *  hint_chain_rate   proporsi hint yang diminta ≤ chainSec (2 dtk) setelah hint sebelumnya pada soal yang sama
 *                    (hint berantai tanpa dibaca)
 *  struggle          butir dengan percobaan pertama salah, dibagi:
 *     productive      akhirnya benar dengan ≤2 permintaan hint
 *     assisted        akhirnya benar tetapi >2 permintaan hint (keberhasilan bergantung bantuan)
 *     unproductive    akhirnya salah dan (≥2 percobaan atau ≥2 hint)
 *     no_effort       akhirnya salah, 1 percobaan, <2 hint
 *  gaming_flag       fast_wrong_rate ≥ 0,30 atau (hint_chain_rate ≥ 0,50 dengan ≥5 hint); hanya bila butir ≥ minItems
 */
final class SrlFeatures
{
    public function __construct(private int $minItems = 10, private int $fastMs = 3000, private int $chainSec = 2)
    {
    }

    /** @return array<int,array<string,mixed>> kunci = user id */
    public function compute(array $rows): array
    {
        $by = [];
        foreach ($rows as $r) {
            $by[$r['user']][$r['session'] . '#' . $r['q']][] = $r;
        }
        $out = [];
        foreach ($by as $uid => $items) {
            $n = $first = $rev = $hintItems = $l3Items = 0;
            $hintEvents = $chains = $fastWrong = $msKnown = 0;
            $ms = [];
            $st = ['productive' => 0, 'assisted' => 0, 'unproductive' => 0, 'no_effort' => 0];
            foreach ($items as $ev) {
                usort($ev, fn ($a, $b) => [$a['ts'], $a['attempt']] <=> [$b['ts'], $b['attempt']]);
                $ans = array_values(array_filter($ev, fn ($e) => $e['type'] !== 'hint_request' && $e['correct'] !== null));
                if (! $ans) {
                    continue;
                }
                $hints = array_values(array_filter($ev, fn ($e) => $e['type'] === 'hint_request'));
                $f = $ans[0];
                $last = end($ans);
                $n++;
                $first += $f['correct'] ? 1 : 0;
                $rev += count($ans) >= 2 ? 1 : 0;
                if ($hints) {
                    $hintItems++;
                    $maxLv = max(array_map(fn ($h) => (int) $h['hint'], $hints));
                    $l3Items += $maxLv >= 3 ? 1 : 0;
                }
                $hintEvents += count($hints);
                for ($i = 1; $i < count($hints); $i++) {
                    if ($hints[$i]['ts'] - $hints[$i - 1]['ts'] <= $this->chainSec) {
                        $chains++;
                    }
                }
                if ($f['ms'] !== null) {
                    $ms[] = $f['ms'];
                    $msKnown++;
                    $fastWrong += (! $f['correct'] && $f['ms'] < $this->fastMs) ? 1 : 0;
                }
                if (! $f['correct']) {
                    $h = count($hints);
                    if ($last['correct']) {
                        $st[$h <= 2 ? 'productive' : 'assisted']++;
                    } elseif (count($ans) >= 2 || $h >= 2) {
                        $st['unproductive']++;
                    } else {
                        $st['no_effort']++;
                    }
                }
            }
            if ($n === 0) {
                continue;
            }
            $fw = $msKnown ? $fastWrong / $msKnown : null;
            $hc = $hintEvents ? $chains / $hintEvents : null;
            $out[$uid] = [
                'items' => $n,
                'first_try_acc' => round($first / $n, 3),
                'revision_rate' => round($rev / $n, 3),
                'hint_rate' => round($hintItems / $n, 3),
                'hint_l3_rate' => round($l3Items / $n, 3),
                'median_ms' => $ms ? (int) round(D::median($ms)) : null,
                'fast_wrong_rate' => $fw === null ? null : round($fw, 3),
                'hint_chain_rate' => $hc === null ? null : round($hc, 3),
                'hint_events' => $hintEvents,
                'struggle' => $st,
                'struggle_total' => array_sum($st),
                'gaming_flag' => $n >= $this->minItems && (($fw !== null && $fw >= 0.30) || ($hc !== null && $hc >= 0.50 && $hintEvents >= 5)),
                'enough' => $n >= $this->minItems,
            ];
        }

        return $out;
    }
}
