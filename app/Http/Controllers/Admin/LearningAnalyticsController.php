<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LearningAnalytics\Engagement\EngagementMetrics;
use App\Services\LearningAnalytics\Engagement\EngagementReport;
use App\Support\SimpleXlsxWriter;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Menu admin "Analitik Lanjutan". Fase 1: tab Keterlibatan & Konsistensi. */
class LearningAnalyticsController extends Controller
{
    public function engagement(Request $request, EngagementReport $reports): View
    {
        $f = $this->filters($request);

        return view('admin.learning-analytics.engagement', [
            'report' => $reports->build($f['school'], $f['grade']),
            'filters' => $f,
            'labels' => EngagementMetrics::PATTERN_LABELS,
        ]);
    }

    public function engagementExport(Request $request, EngagementReport $reports)
    {
        $f = $this->filters($request);
        $r = $reports->build($f['school'], $f['grade']);
        abort_unless($r['ready'] ?? false, 404);
        $labels = EngagementMetrics::PATTERN_LABELS;
        $headers = ['Nama', 'Sekolah', 'Jurusan', 'Kelas', 'Paralel', 'Hari aktif (28 hr)', 'Minggu aktif (dari 4)', 'Sesi', 'Menit (estimasi)',
            'Hari sejak aktif', 'Streak saat ini', 'Streak terpanjang', 'CV jeda', 'Konsentrasi (Gini)', 'Porsi hari tersibuk',
            'Kemiringan tren', 'Jam puncak', 'Indeks keteraturan', 'Pola'];
        $rows = [];
        foreach ($r['students'] as $p) {
            $s = $p['student'];
            $rows[] = [$s['name'], $s['school'], $s['major'], $s['grade'], $s['parallel'], $p['active_days'], $p['weeks_active'], $p['sessions'], $p['minutes'],
                $p['days_since_last'], $p['current_streak'], $p['longest_streak'], $p['gap_cv'], $p['concentration'], $p['top_day_share'],
                $p['trend_slope'], $p['peak_hour'], $p['regularity_index'], $labels[$p['pattern']] ?? $p['pattern']];
        }

        return SimpleXlsxWriter::download('keterlibatan-' . now()->format('Ymd') . '.xlsx', $headers, $rows);
    }

    private function filters(Request $request): array
    {
        $v = $request->validate([
            'school' => ['nullable', 'string', 'max:150'],
            'grade' => ['nullable', 'in:X,XI,XII'],
        ]);

        return ['school' => $v['school'] ?? null ?: null, 'grade' => $v['grade'] ?? null ?: null];
    }
}
