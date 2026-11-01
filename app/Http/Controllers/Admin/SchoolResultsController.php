<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\SchoolLearningAnalytics;
use App\Support\SimpleXlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Menu admin: analisis hasil belajar untuk SEMUA sekolah.
 *
 * Hanya admin (rute berada di grup middleware 'admin'). Angka dihitung
 * dengan aturan yang sama seperti dasbor guru per kelas.
 */
class SchoolResultsController extends Controller
{
    public function index(Request $request, SchoolLearningAnalytics $analytics): View
    {
        $filters = $this->filters($request);
        $report = $analytics->report($filters['school'], $filters['grade']);

        return view('admin.school-results.index', [
            'report' => $report,
            'filters' => $filters,
            'skillLabels' => [
                'listening' => 'Listening',
                'reading' => 'Reading',
                'writing' => 'Writing',
                'speaking' => 'Speaking',
            ],
        ]);
    }

    /**
     * Ekspor Excel. mode=sekolah (ringkasan per sekolah, atau per kelas bila
     * satu sekolah dipilih) atau mode=siswa (satu baris per siswa pada
     * cakupan yang dipilih).
     */
    public function export(Request $request, SchoolLearningAnalytics $analytics)
    {
        $filters = $this->filters($request);
        $mode = $request->query('mode') === 'siswa' ? 'siswa' : 'sekolah';
        $report = $analytics->report($filters['school'], $filters['grade']);
        $f = fn ($v) => $v === null ? null : round((float) $v, 1);
        $name = 'analisis-hasil-' . $mode . '-' . now()->format('Ymd') . '.xlsx';

        if ($mode === 'siswa') {
            $headers = ['Nama', 'Email', 'Sekolah', 'Jurusan', 'Kelas', 'Paralel', 'XP',
                'Listening', 'Reading', 'Writing', 'Speaking', 'Pretest', 'Posttest', 'Kenaikan',
                'Pelajaran Selesai', 'Progres (%)', 'Status', 'Perlu Intervensi',
                'Salah Inferensial (percobaan 1)', 'Hint Level 3'];
            $bandText = ['belum' => 'Belum mulai', 'berkembang' => 'Berkembang', 'mahir' => 'Mahir'];
            $rows = [];
            foreach ($report['students'] as $p) {
                $rows[] = [
                    $p['name'], $p['email'], $p['school'], $p['major'], $p['grade'], $p['parallel'], $p['xp'],
                    $f($p['skills']['listening']), $f($p['skills']['reading']),
                    $f($p['skills']['writing']), $f($p['skills']['speaking']),
                    $f($p['pre']), $f($p['post']), $f($p['gain']),
                    $p['completed'], round($p['progress'] * 100, 1), $bandText[$p['band']] ?? $p['band'],
                    $p['intervention'] ? 'Ya' : 'Tidak', $p['infer'], $p['l3'],
                ];
            }

            return SimpleXlsxWriter::download($name, $headers, $rows);
        }

        $perClass = $report['scope_school'] !== null;
        $headers = array_merge(
            $perClass ? ['Sekolah', 'Jurusan', 'Kelas'] : ['Sekolah'],
            ['Siswa', 'Sudah Mulai', 'Aktif 7 Hari', 'Rata-rata XP', 'Pretest', 'Posttest', 'Kenaikan',
                'n Kenaikan', 'Listening', 'Reading', 'Writing', 'Speaking', 'Selesai Semua (%)',
                'Progres Rata-rata (%)', 'Mahir (%)', 'Perlu Intervensi', 'Perlu Intervensi (%)']
        );
        $groups = $perClass ? $report['classes'] : $report['schools'];
        $rows = [];
        foreach ($groups as $g) {
            $lead = $perClass
                ? [$report['scope_school'], $g['major'], $g['label']]
                : [$g['school']];
            $rows[] = array_merge($lead, [
                $g['n'], $g['started'], $g['active7'], $g['avg_xp'], $f($g['pre']), $f($g['post']), $f($g['gain']),
                $g['gain_n'], $f($g['skills']['listening']), $f($g['skills']['reading']),
                $f($g['skills']['writing']), $f($g['skills']['speaking']), $g['completed_pct'],
                $g['progress_pct'], $g['mahir_pct'], $g['intervention'], $g['intervention_pct'],
            ]);
        }

        return SimpleXlsxWriter::download($name, $headers, $rows);
    }

    private function filters(Request $request): array
    {
        $v = $request->validate([
            'school' => ['nullable', 'string', 'max:150'],
            'grade' => ['nullable', Rule::in(['X', 'XI', 'XII'])],
        ]);

        return [
            'school' => ($v['school'] ?? '') !== '' ? $v['school'] : null,
            'grade' => ($v['grade'] ?? '') !== '' ? $v['grade'] : null,
        ];
    }
}
