<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StudentProgressController;
use App\Services\LearningAnalytics\AccessLog;
use App\Services\LearningAnalytics\Cohort;
use App\Services\LearningAnalytics\Research\ResearchExportService;
use App\Support\SimpleXlsxWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Analitik Lanjutan: Ekspor Riset (pseudonim, persetujuan, kelompok) dan Metodologi. */
class LearningAnalyticsResearchController extends Controller
{
    public static function meta(): array
    {
        $m = ['ethics_no' => '', 'contact' => '', 'target_pct' => 80];
        if (Schema::hasTable('la_settings') && ($v = DB::table('la_settings')->where('key', 'research_meta')->value('value'))) {
            $m = array_replace($m, json_decode($v, true) ?: []);
        }

        return $m;
    }

    public function index(): View
    {
        $ready = Schema::hasTable('la_consents');
        $students = fn () => Cohort::students();
        $total = $students()->count();
        $consent = $ready ? DB::table('la_consents as c')->join('users as u', 'u.id', '=', 'c.user_id')->tap(fn ($q) => Cohort::whereJoined($q))
            ->selectRaw('SUM(c.accepted=1) yes, SUM(c.accepted=0) no')->first() : null;
        $groups = Schema::hasColumn('users', 'research_group') ? $students()->selectRaw("COALESCE(NULLIF(research_group, ''), '(belum diatur)') g, COUNT(*) c")->groupBy('g')->pluck('c', 'g')->all() : [];
        $log = $ready ? DB::table('la_access_log as l')->leftJoin('users as u', 'u.id', '=', 'l.actor_id')->orderByDesc('l.id')->limit(25)->get(['u.name', 'l.action', 'l.target', 'l.meta', 'l.created_at']) : collect();

        // dasbor persetujuan per kelas
        $classes = collect();
        if ($ready) {
            $cons = DB::table('la_consents')->pluck('accepted', 'user_id');
            $acc = [];
            foreach ($students()->get(['id', 'school', 'major', 'grade', 'parallel']) as $u) {
                $k = ($u->school ?: '(Belum diisi)') . '|' . $u->major . '|' . $u->grade . '|' . $u->parallel;
                $acc[$k] ??= ['school' => $u->school ?: '(Belum diisi)', 'major' => $u->major, 'grade' => $u->grade, 'parallel' => $u->parallel, 'n' => 0, 'yes' => 0, 'no' => 0];
                $acc[$k]['n']++;
                if ($cons->has($u->id)) {
                    $acc[$k][$cons[$u->id] ? 'yes' : 'no']++;
                }
            }
            $classes = collect($acc)->sortBy(fn ($c) => [$c['school'], $c['grade'], $c['major'], $c['parallel']])->values();
        }
        $batches = Schema::hasTable('la_consent_batches') ? DB::table('la_consent_batches as b')->leftJoin('users as u', 'u.id', '=', 'b.recorded_by')->orderByDesc('b.id')->limit(15)->get(['b.*', 'u.name as recorder']) : collect();

        return view('admin.learning-analytics.research', [
            'ready' => $ready, 'total' => $total, 'yes' => (int) ($consent->yes ?? 0), 'no' => (int) ($consent->no ?? 0),
            'groups' => $groups, 'log' => $log, 'schools' => array_keys(config('schools', [])), 'classes' => $classes, 'batches' => $batches, 'meta' => self::meta(),
            'cohortNote' => ($ex = Cohort::excludedSummary()) ? 'Dikecualikan dari analisis: ' . implode(', ', array_map(fn ($k, $n) => "$n $k", array_keys($ex), $ex)) . '.' : null,
        ]);
    }

    public function setGroup(Request $request): RedirectResponse
    {
        $v = $request->validate([
            'school' => ['required', Rule::in(array_keys(config('schools', [])))],
            'grade' => ['nullable', Rule::in(['X', 'XI', 'XII'])],
            'parallel' => ['nullable', Rule::in(['A', 'B', 'C', 'D'])],
            'group' => ['nullable', Rule::in(['kontrol', 'perlakuan'])],
        ]);
        $q = Cohort::students(false)->where('school', $v['school']);
        foreach (['grade', 'parallel'] as $f) {
            if (! empty($v[$f])) {
                $q->where($f, $v[$f]);
            }
        }
        $n = $q->update(['research_group' => $v['group'] ?: null]);
        AccessLog::record('set_research_group', $v['school'], $v + ['affected' => $n]);

        return back()->with('status', "Kelompok '" . ($v['group'] ?: 'kosong') . "' diterapkan pada $n siswa.");
    }

    public function saveMeta(Request $request): RedirectResponse
    {
        $v = $request->validate(['ethics_no' => ['nullable', 'string', 'max:120'], 'contact' => ['nullable', 'string', 'max:200'], 'target_pct' => ['required', 'integer', 'min:1', 'max:100']]);
        DB::table('la_settings')->updateOrInsert(['key' => 'research_meta'], ['value' => json_encode($v), 'updated_by' => auth()->id(), 'updated_at' => now()]);
        AccessLog::record('update_research_meta');

        return back()->with('status', 'Keterangan penelitian disimpan.');
    }

    /** Persetujuan kolektif oleh sekolah/orang tua. Tidak menimpa jawaban yang diberikan siswa sendiri. */
    public function consentBatch(Request $request): RedirectResponse
    {
        abort_unless(Schema::hasTable('la_consent_batches'), 404);
        $v = $request->validate([
            'school' => ['required', Rule::in(array_keys(config('schools', [])))],
            'grade' => ['nullable', Rule::in(['X', 'XI', 'XII'])], 'parallel' => ['nullable', Rule::in(['A', 'B', 'C', 'D'])],
            'basis' => ['required', 'in:sekolah,orangtua'], 'decision' => ['required', 'in:setuju,tolak'],
            'document_no' => ['required', 'string', 'min:3', 'max:120'], 'note' => ['nullable', 'string', 'max:500'],
        ]);
        $q = Cohort::students(false)->where('school', $v['school']);
        foreach (['grade', 'parallel'] as $f) {
            if (! empty($v[$f])) {
                $q->where($f, $v[$f]);
            }
        }
        $ids = $q->pluck('id')->all();
        $own = DB::table('la_consents')->whereIn('user_id', $ids)->where('source', 'siswa')->pluck('user_id')->flip();
        $batchId = DB::table('la_consent_batches')->insertGetId(['school' => $v['school'], 'grade' => $v['grade'] ?? null, 'parallel' => $v['parallel'] ?? null, 'basis' => $v['basis'],
            'decision' => $v['decision'], 'document_no' => $v['document_no'], 'note' => $v['note'] ?? null, 'recorded_by' => auth()->id(), 'created_at' => now()]);
        $n = 0;
        foreach ($ids as $id) {
            if ($own->has($id)) {
                continue;
            }
            DB::table('la_consents')->updateOrInsert(['user_id' => $id], ['version' => StudentProgressController::CONSENT_VERSION, 'accepted' => $v['decision'] === 'setuju',
                'source' => $v['basis'], 'batch_id' => $batchId, 'answered_at' => now()]);
            $n++;
        }
        DB::table('la_consent_batches')->where('id', $batchId)->update(['affected' => $n]);
        AccessLog::record('consent_batch', $v['school'], $v + ['affected' => $n, 'skipped_own_answer' => count($ids) - $n]);

        return back()->with('status', "Persetujuan kolektif dicatat untuk $n siswa (" . (count($ids) - $n) . ' siswa mempertahankan jawaban sendiri).');
    }

    public function consentRevoke(int $batch): RedirectResponse
    {
        abort_unless(Schema::hasTable('la_consent_batches'), 404);
        $b = DB::table('la_consent_batches')->where('id', $batch)->whereNull('revoked_at')->first();
        abort_unless($b, 404);
        $n = DB::table('la_consents')->where('batch_id', $batch)->update(['accepted' => false, 'answered_at' => now()]);
        DB::table('la_consent_batches')->where('id', $batch)->update(['revoked_at' => now()]);
        AccessLog::record('consent_batch_revoked', (string) $batch, ['affected' => $n]);

        return back()->with('status', "Persetujuan kolektif #$batch ditarik; $n siswa kini tercatat tidak menyetujui.");
    }

    public function export(Request $request, ResearchExportService $svc)
    {
        $v = $request->validate([
            'identified' => ['nullable', 'boolean'], 'include_unconsented' => ['nullable', 'boolean'],
            'format' => ['nullable', 'in:xlsx,csv,long'], 'reason' => ['nullable', 'string', 'max:300'],
        ]);
        $identified = (bool) ($v['identified'] ?? false);
        $include = (bool) ($v['include_unconsented'] ?? false);
        $format = $v['format'] ?? 'xlsx';
        if (($identified || $include) && mb_strlen(trim($v['reason'] ?? '')) < 10) {
            return back()->withErrors(['reason' => 'Alasan wajib diisi (≥10 karakter) untuk ekspor teridentifikasi atau yang menyertakan siswa tanpa persetujuan.'])->withInput();
        }
        $r = $svc->build($identified, $include);
        if ($r['n'] === 0) {
            return back()->withErrors(['export' => 'Tidak ada baris untuk diekspor: ' . ($r['excluded'] > 0 ? "0 siswa menyetujui ({$r['excluded']} siswa belum/tidak menyetujui). Kumpulkan persetujuan lebih dulu." : 'belum ada siswa.')]);
        }
        $stamp = now()->format('Ymd');
        $hash = substr(hash('sha256', json_encode($r['rows'])), 0, 16);
        AccessLog::record($identified ? 'export_identified' : 'export_pseudonymized', $format, ['rows' => $r['n'], 'excluded_no_consent' => $r['excluded'], 'include_unconsented' => $include, 'reason' => $v['reason'] ?? null, 'hash' => $hash]);
        $base = ($identified ? 'riset-teridentifikasi-' : 'riset-pseudonim-') . $stamp;
        if ($format === 'long') {
            $l = $svc->buildLong();

            return SimpleXlsxWriter::csv($base . '-butir-panjang.csv', $l['headers'], $l['rows']);
        }
        if ($format === 'csv') {
            return SimpleXlsxWriter::csv($base . '.csv', $r['headers'], $r['rows']);
        }
        $meta = [['Skema', 'la-research v2'], ['Tanggal ekstraksi', now()->toDateTimeString()], ['Jumlah baris', $r['n']], ['Siswa tanpa persetujuan (dikecualikan)', $r['excluded']],
            ['Hash SHA-256 (16 awal) isi data', $hash], ['Mode', $identified ? 'teridentifikasi' : 'pseudonim'], ['Catatan', 'Selisih pre-post tidak sebanding bila bentuk tes tidak setara; lihat tab Hasil Belajar.']];

        return SimpleXlsxWriter::downloadSheets($base . '.xlsx', [
            ['name' => 'Data', 'headers' => $r['headers'], 'rows' => $r['rows']],
            ['name' => 'Kamus Data', 'headers' => ['Kolom', 'Definisi', 'Nilai/satuan', 'Sumber/rumus'], 'rows' => ResearchExportService::codebook()],
            ['name' => 'Metadata', 'headers' => ['Butir', 'Nilai'], 'rows' => $meta],
        ]);
    }

    public function codebook()
    {
        return SimpleXlsxWriter::download('kamus-data-riset.xlsx', ['Kolom', 'Definisi', 'Nilai/satuan', 'Sumber/rumus'], ResearchExportService::codebook());
    }

    public function methodology(): View
    {
        return view('admin.learning-analytics.method');
    }
}
