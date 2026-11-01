<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\SchoolLearningAnalytics;
use App\Services\LearningAnalytics\Cohort;
use App\Services\LearningAnalytics\Outcome\FormComparability;
use App\Services\LearningAnalytics\Outcome\OutcomeAnalysis;
use App\Support\SimpleXlsxWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/** Analitik Lanjutan Fase 2: tab Hasil (inferensi) dan Kualitas Soal (CTT). */
class LearningAnalyticsOutcomeController extends Controller
{
    public function outcomes(Request $request, SchoolLearningAnalytics $base, OutcomeAnalysis $analysis): View
    {
        $v = $request->validate(['school' => ['nullable', 'string', 'max:150'], 'grade' => ['nullable', 'in:X,XI,XII']]);
        $school = ($v['school'] ?? null) ?: null;
        $grade = ($v['grade'] ?? null) ?: null;
        $report = $base->report($school, $grade);

        $a = $analysis->analyze($report['students']);
        $cfg = config('learning_analytics.outcomes');
        $eq = null;
        if (Schema::hasTable('la_item_stats')) {
            $eq = (new FormComparability((float) $cfg['nonequivalence_delta_p']))->analyze(
                DB::table('la_item_stats')->get(['form', 'item', 'p', 'n'])->map(fn ($i) => ['form' => $i->form, 'item' => $i->item, 'p' => (float) $i->p, 'n' => (int) $i->n])->all());
        }
        $grp = Schema::hasColumn('users', 'research_group') ? DB::table('users')->whereIn('id', array_column($report['students'], 'id'))->pluck('research_group', 'id')->all() : [];
        $ml = (new \App\Services\LearningAnalytics\Outcome\MultilevelOutcome())->analyze($report['students'], $grp);
        $total = count(array_filter($report['students'], fn ($s) => ! $school || ($s['school'] ?: '(Belum diisi)') === $school));
        $ex = Cohort::excludedSummary();

        return view('admin.learning-analytics.outcomes', [
            'eq' => $eq, 'ml' => $ml, 'coverage' => $total ? ['students' => $total, 'pct' => round(100 * $a['pairs'] / $total, 1)] : null,
            'cohortNote' => $ex ? 'Dikecualikan dari analisis: ' . implode(', ', array_map(fn ($k, $v) => "$v $k", array_keys($ex), $ex)) . '.' : null,
            'a' => $a,
            'schoolOptions' => array_column($report['schools'], 'school'),
            'filters' => ['school' => $school, 'grade' => $grade],
        ]);
    }

    public const SEVERITY = ['kunci diduga salah' => 100, 'daya beda negatif' => 80, 'daya beda rendah' => 40, 'terlalu sulit' => 15, 'terlalu mudah' => 15];
    public const STATUS = ['belum' => 'Belum ditinjau', 'ditinjau' => 'Ditinjau', 'diperbaiki' => 'Diperbaiki', 'dibuang' => 'Dibuang'];

    public static function severity(array $flags): int
    {
        $s = 0;
        foreach ($flags as $f) {
            $s += self::SEVERITY[$f] ?? (str_starts_with($f, 'distraktor') ? 20 : 5);
        }

        return $s;
    }

    public function items(Request $request): View
    {
        $v = $request->validate(['form' => ['nullable', 'string', 'max:120'], 'all' => ['nullable', 'in:1'], 'status' => ['nullable', 'in:belum,ditinjau,diperbaiki,dibuang,semua'], 'page' => ['nullable', 'integer', 'min:1']]);
        $ready = Schema::hasTable('la_item_stats');
        $forms = collect();
        $items = null;
        $computed = null;
        $only = ($v['all'] ?? null) !== '1';
        $form = $v['form'] ?? null;
        $status = $v['status'] ?? ($only ? 'belum' : 'semua');
        $hasReviews = Schema::hasTable('la_item_reviews');
        $summary = [];
        if ($ready) {
            $forms = DB::table('la_form_stats')->orderBy('form')->get();
            $q = DB::table('la_item_stats as i');
            if ($hasReviews) {
                $q->leftJoin('la_item_reviews as r', fn ($j) => $j->on('r.form', '=', 'i.form')->on('r.item', '=', 'i.item'));
            }
            if ($form) {
                $q->where('i.form', $form);
            }
            if ($only) {
                $q->whereRaw('JSON_LENGTH(i.flags) > 0');
            }
            $cols = ['i.form', 'i.item', 'i.n', 'i.p', 'i.r_pb', 'i.d_index', 'i.key_option', 'i.options', 'i.flags', 'i.indicative'];
            $rows = $q->get($hasReviews ? array_merge($cols, ['r.status as review_status', 'r.note as review_note']) : $cols)->map(function ($i) {
                $i->flags = json_decode($i->flags ?? '[]', true) ?: [];
                $i->options = json_decode($i->options ?? '{}', true) ?: [];
                $i->severity = self::severity($i->flags);
                $i->review_status = $i->review_status ?? 'belum';
                $parts = explode('|', $i->form);
                $lesson = (int) ($parts[3] ?? 0);
                $i->link = null;
                if ($lesson > 0 && ($parts[1] ?? '') === 'reading') {
                    $i->link = route('admin.reading-lesson-questions.index', $lesson);
                } elseif ($lesson > 0 && ($parts[1] ?? '') === 'listening') {
                    $i->link = route('admin.listening-lesson-questions.index', $lesson);
                }

                return $i;
            });
            foreach (array_keys(self::STATUS) as $st) {
                $summary[$st] = $rows->where('review_status', $st)->count();
            }
            if ($status !== 'semua') {
                $rows = $rows->where('review_status', $status);
            }
            $rows = $rows->sortByDesc('severity')->values();
            $page = (int) ($v['page'] ?? 1);
            $items = new LengthAwarePaginator($rows->slice(($page - 1) * 30, 30)->values(), $rows->count(), 30, $page, ['path' => $request->url(), 'query' => $request->query()]);
            $computed = DB::table('la_form_stats')->max('computed_at');
        }

        return view('admin.learning-analytics.items', compact('ready', 'forms', 'items', 'computed', 'only', 'form', 'status', 'summary', 'hasReviews'));
    }

    public function reviewItem(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('la_item_reviews'), 404);
        $v = $request->validate(['form' => ['required', 'string', 'max:120'], 'item' => ['required', 'string', 'max:60'], 'status' => ['required', 'in:belum,ditinjau,diperbaiki,dibuang'], 'note' => ['nullable', 'string', 'max:500']]);
        DB::table('la_item_reviews')->updateOrInsert(['form' => $v['form'], 'item' => $v['item']], ['status' => $v['status'], 'note' => $v['note'] ?? null, 'reviewed_by' => auth()->id(), 'updated_at' => now()]);

        return back()->with('status', 'Status butir ' . $v['item'] . ' disimpan.');
    }

    public function itemsExport()
    {
        abort_unless(Schema::hasTable('la_item_stats'), 404);
        $rows = DB::table('la_item_stats')->orderBy('form')->orderBy('item')->get()->map(fn ($i) => [
            $i->form, $i->item, $i->n, (float) $i->p, $i->r_pb === null ? null : (float) $i->r_pb, $i->d_index === null ? null : (float) $i->d_index,
            $i->key_option, implode('; ', json_decode($i->flags ?? '[]', true) ?: []), $i->indicative ? 'ya' : 'tidak'])->all();

        return SimpleXlsxWriter::download('analisis-butir-' . now()->format('Ymd') . '.xlsx',
            ['Form', 'Butir', 'n', 'Kesukaran (p)', 'Daya beda (r_pb sisa)', 'D 27%', 'Kunci', 'Penanda', 'n kecil'], $rows);
    }
}
