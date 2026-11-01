<?php

namespace App\Services\Analytics;

use DateTimeImmutable;

/**
 * Perhitungan murni (tanpa akses database) untuk analisis hasil belajar
 * lintas sekolah di menu admin.
 *
 * Aturan hitung sengaja sama dengan dasbor guru
 * (Staff\ClassResultsController::buildReport) supaya angka di menu admin
 * dan di dasbor guru tidak saling bertentangan:
 *  - skor skill  = skor submission TERAKHIR per skill (berdasarkan id);
 *  - pretest/posttest = rata-rata final_score submission bertipe itu;
 *  - band: Belum (0 skill) / Berkembang (<4 skill atau rata-rata <70) / Mahir;
 *  - perlu intervensi: salah inferensial > ambang ATAU hint level 3 >= ambang.
 */
class SchoolLearningMetrics
{
    public const SKILLS = ['listening', 'reading', 'writing', 'speaking'];

    public const NO_SCHOOL = '(Belum diisi)';

    /**
     * @param  array  $data  lihat SchoolLearningAnalytics::load()
     */
    public function compute(array $data, ?string $school = null, ?string $grade = null, ?DateTimeImmutable $now = null): array
    {
        $now ??= new DateTimeImmutable('now');
        $totalLessons = (int) ($data['total_lessons'] ?? 0);
        $inferThr = (int) ($data['inferential_threshold'] ?? 2);
        $l3Thr = (int) ($data['hint_l3_threshold'] ?? 2);

        // ---- skor terakhir per (siswa, skill), rata-rata pre/post per siswa ----
        $latest = [];
        $preSum = $preN = $postSum = $postN = [];
        $weekly = [];   // uid => [weekKey => [n, sum]]
        foreach ($data['subs'] ?? [] as $s) {
            $uid = (int) $s['user_id'];
            $skill = (string) ($s['skill'] ?? '');
            if ($skill !== '') {
                // data diurutkan menaik berdasarkan id: yang terakhir menimpa
                $latest[$uid][$skill] = $s['final_score'] === null ? null : (float) $s['final_score'];
            }
            if ($s['final_score'] !== null) {
                $score = (float) $s['final_score'];
                if (($s['type'] ?? '') === 'pretest') {
                    $preSum[$uid] = ($preSum[$uid] ?? 0) + $score;
                    $preN[$uid] = ($preN[$uid] ?? 0) + 1;
                } elseif (($s['type'] ?? '') === 'posttest') {
                    $postSum[$uid] = ($postSum[$uid] ?? 0) + $score;
                    $postN[$uid] = ($postN[$uid] ?? 0) + 1;
                }
                $when = $s['when'] ?? null;
                if ($when) {
                    $wk = $this->weekStart($when);
                    if ($wk !== null) {
                        $weekly[$uid][$wk][0] = ($weekly[$uid][$wk][0] ?? 0) + 1;
                        $weekly[$uid][$wk][1] = ($weekly[$uid][$wk][1] ?? 0) + $score;
                    }
                }
            }
        }

        // ---- ringkasan per siswa ----
        $per = [];
        $cutoff = $now->modify('-7 days')->format('Y-m-d');
        foreach ($data['students'] ?? [] as $st) {
            $uid = (int) $st['id'];
            $skills = [];
            foreach (self::SKILLS as $sk) {
                $skills[$sk] = $latest[$uid][$sk] ?? null;
            }
            $have = array_values(array_filter($skills, fn ($v) => $v !== null));
            $avgFour = $have === [] ? null : array_sum($have) / count($have);
            if (count($have) === 0) {
                $band = 'belum';
            } elseif (count($have) < 4 || $avgFour < 70) {
                $band = 'berkembang';
            } else {
                $band = 'mahir';
            }

            $pre = isset($preN[$uid]) ? $preSum[$uid] / $preN[$uid] : null;
            $post = isset($postN[$uid]) ? $postSum[$uid] / $postN[$uid] : null;
            $completed = (int) ($data['completed'][$uid] ?? 0);
            $fully = $totalLessons > 0 && $completed >= $totalLessons;
            $infer = (int) ($data['infer'][$uid] ?? 0);
            $l3 = (int) ($data['l3'][$uid] ?? 0);
            $last = $st['last_activity'] ?? null;

            $per[$uid] = [
                'id' => $uid,
                'name' => (string) $st['name'],
                'email' => (string) ($st['email'] ?? ''),
                'school' => $this->schoolLabel($st['school'] ?? null),
                'major' => (string) ($st['major'] ?? ''),
                'grade' => (string) ($st['grade'] ?? ''),
                'parallel' => (string) ($st['parallel'] ?? ''),
                'xp' => (int) ($st['xp'] ?? 0),
                'skills' => $skills,
                'pre' => $pre,
                'post' => $post,
                'gain' => ($pre !== null && $post !== null) ? $post - $pre : null,
                'band' => $band,
                'completed' => $completed,
                'progress' => $totalLessons > 0 ? min(1.0, $completed / $totalLessons) : 0.0,
                'fully' => $fully,
                'started' => $have !== [] || $completed > 0 || (int) ($st['xp'] ?? 0) > 0,
                'intervention' => $infer > $inferThr || $l3 >= $l3Thr,
                'infer' => $infer,
                'l3' => $l3,
                'active7' => $last !== null && substr((string) $last, 0, 10) >= $cutoff,
            ];
        }

        // ---- kelompok: semua sekolah (hanya filter kelas/tingkat) ----
        $bySchool = [];
        foreach ($per as $p) {
            if ($grade !== null && $grade !== '' && $p['grade'] !== $grade) {
                continue;
            }
            $bySchool[$p['school']][] = $p;
        }
        $schoolNames = array_values(array_unique(array_merge(
            array_map([$this, 'schoolLabel'], $data['school_names'] ?? []),
            array_keys($bySchool)
        )));
        sort($schoolNames, SORT_NATURAL | SORT_FLAG_CASE);
        $schools = [];
        foreach ($schoolNames as $name) {
            $schools[] = ['school' => $name] + $this->metrics($bySchool[$name] ?? []);
        }

        // ---- cakupan (scope) untuk ringkasan, tren, dan sebaran error ----
        $scopeList = [];
        foreach ($bySchool as $name => $list) {
            if ($school === null || $school === '' || $name === $school) {
                foreach ($list as $p) {
                    $scopeList[] = $p;
                }
            }
        }
        $overview = $this->metrics($scopeList);

        // ---- kelas (jurusan x tingkat x paralel) untuk satu sekolah ----
        $classes = [];
        if ($school !== null && $school !== '') {
            $group = [];
            foreach ($bySchool[$school] ?? [] as $p) {
                $group[$p['major'] . '|' . $p['grade'] . '|' . $p['parallel']][] = $p;
            }
            ksort($group, SORT_NATURAL | SORT_FLAG_CASE);
            foreach ($group as $list) {
                $f = $list[0];
                $classes[] = [
                    'major' => $f['major'],
                    'grade' => $f['grade'],
                    'parallel' => $f['parallel'],
                    'label' => trim($f['grade'] . ' ' . $f['parallel']),
                ] + $this->metrics($list);
            }
        }

        // ---- tren mingguan (8 minggu terakhir) untuk scope ----
        $trend = [];
        $thisWeek = $this->weekStart($now->format('Y-m-d H:i:s'));
        for ($i = 7; $i >= 0; $i--) {
            $wk = (new DateTimeImmutable($thisWeek))->modify("-{$i} weeks")->format('Y-m-d');
            $n = 0;
            $sum = 0.0;
            foreach ($scopeList as $p) {
                if (isset($weekly[$p['id']][$wk])) {
                    $n += $weekly[$p['id']][$wk][0];
                    $sum += $weekly[$p['id']][$wk][1];
                }
            }
            $trend[] = ['week' => $wk, 'n' => $n, 'avg' => $n > 0 ? round($sum / $n, 1) : null];
        }

        // ---- sebaran jenis kesalahan Reading (scope) ----
        $errors = [];
        if (is_callable($data['error_loader'] ?? null) && $scopeList !== []) {
            $errors = (array) ($data['error_loader'])(array_column($scopeList, 'id'));
        }
        $labels = [
            'lexical' => 'Lexical',
            'inferential' => 'Inferential',
            'syntactic' => 'Syntactic',
            'context_misconception' => 'Context Misconception',
        ];
        $errTotal = array_sum(array_map('intval', $errors));
        $errorRows = [];
        foreach ($labels as $code => $label) {
            $c = (int) ($errors[$code] ?? 0);
            $errorRows[] = ['code' => $code, 'label' => $label, 'count' => $c,
                'pct' => $errTotal > 0 ? round($c * 100 / $errTotal, 1) : 0.0];
        }

        // ---- daftar siswa perlu perhatian (scope) ----
        $attention = array_values(array_filter($scopeList, fn ($p) => $p['intervention']));
        usort($attention, fn ($a, $b) => ($b['infer'] + $b['l3']) <=> ($a['infer'] + $a['l3']));

        return [
            'scope_school' => ($school === null || $school === '') ? null : $school,
            'scope_grade' => ($grade === null || $grade === '') ? null : $grade,
            'overview' => $overview,
            'schools' => $schools,
            'classes' => $classes,
            'trend' => $trend,
            'errors' => $errorRows,
            'errors_total' => $errTotal,
            'attention' => array_slice($attention, 0, 15),
            'students' => $scopeList,
            'insights' => $this->insights($overview, $schools, $school),
            'thresholds' => ['inferential' => $inferThr, 'hint_l3' => $l3Thr],
        ];
    }

    /** Metrik agregat untuk sekelompok siswa (hasil ringkasan per siswa). */
    public function metrics(array $list): array
    {
        $n = count($list);
        $out = [
            'n' => $n, 'started' => 0, 'active7' => 0, 'avg_xp' => null,
            'pre' => null, 'post' => null, 'gain' => null, 'gain_n' => 0,
            'skills' => array_fill_keys(self::SKILLS, null),
            'bands' => ['belum' => 0, 'berkembang' => 0, 'mahir' => 0],
            'completed_all' => 0, 'progress_pct' => null, 'completed_pct' => null,
            'started_pct' => null, 'active_pct' => null,
            'intervention' => 0, 'intervention_pct' => null, 'mahir_pct' => null,
        ];
        if ($n === 0) {
            return $out;
        }
        $xp = 0;
        $pre = $post = $gain = [];
        $skillVals = array_fill_keys(self::SKILLS, []);
        $prog = 0.0;
        foreach ($list as $p) {
            $xp += $p['xp'];
            $out['started'] += $p['started'] ? 1 : 0;
            $out['active7'] += $p['active7'] ? 1 : 0;
            $out['bands'][$p['band']]++;
            $out['completed_all'] += $p['fully'] ? 1 : 0;
            $out['intervention'] += $p['intervention'] ? 1 : 0;
            $prog += $p['progress'];
            if ($p['pre'] !== null) {
                $pre[] = $p['pre'];
            }
            if ($p['post'] !== null) {
                $post[] = $p['post'];
            }
            if ($p['gain'] !== null) {
                $gain[] = $p['gain'];
            }
            foreach (self::SKILLS as $sk) {
                if ($p['skills'][$sk] !== null) {
                    $skillVals[$sk][] = $p['skills'][$sk];
                }
            }
        }
        $avg = fn (array $a) => $a === [] ? null : round(array_sum($a) / count($a), 1);
        $out['avg_xp'] = (int) round($xp / $n);
        $out['pre'] = $avg($pre);
        $out['post'] = $avg($post);
        $out['gain'] = $avg($gain);
        $out['gain_n'] = count($gain);
        foreach (self::SKILLS as $sk) {
            $out['skills'][$sk] = $avg($skillVals[$sk]);
        }
        $pct = fn ($v) => round($v * 100 / $n, 1);
        $out['progress_pct'] = round($prog * 100 / $n, 1);
        $out['completed_pct'] = $pct($out['completed_all']);
        $out['started_pct'] = $pct($out['started']);
        $out['active_pct'] = $pct($out['active7']);
        $out['intervention_pct'] = $pct($out['intervention']);
        $out['mahir_pct'] = $pct($out['bands']['mahir']);

        return $out;
    }

    /** Temuan otomatis dalam bahasa Indonesia. Hanya dari kelompok dengan data cukup. */
    private function insights(array $overview, array $schools, ?string $school): array
    {
        $out = [];
        if ($overview['n'] === 0) {
            return ['Belum ada siswa pada cakupan yang dipilih.'];
        }
        $f = fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format((float) $v, 1, ',', '.'), '0'), ',');

        $skills = array_filter($overview['skills'], fn ($v) => $v !== null);
        if ($skills !== []) {
            asort($skills);
            $low = array_key_first($skills);
            $high = array_key_last($skills);
            $out[] = 'Keterampilan dengan rata-rata terendah adalah ' . ucfirst($low) . ' (' . $f($skills[$low]) . '), '
                . 'dan tertinggi ' . ucfirst($high) . ' (' . $f($skills[$high]) . ').';
        }
        if ($overview['gain'] !== null) {
            $out[] = 'Rata-rata kenaikan Pretest ke Posttest ' . $f($overview['gain']) . ' poin '
                . '(dari ' . $overview['gain_n'] . ' siswa yang sudah punya kedua tes).';
        } else {
            $out[] = 'Belum ada siswa yang menyelesaikan Pretest dan Posttest, sehingga kenaikan belum dapat dihitung.';
        }
        if ($school === null || $school === '') {
            $rank = array_values(array_filter($schools, fn ($s) => $s['gain'] !== null && $s['gain_n'] >= 3));
            if (count($rank) >= 2) {
                usort($rank, fn ($a, $b) => $b['gain'] <=> $a['gain']);
                $top = $rank[0];
                $bot = $rank[count($rank) - 1];
                $out[] = 'Kenaikan tertinggi: ' . $top['school'] . ' (' . $f($top['gain']) . ' poin, n=' . $top['gain_n'] . '); '
                    . 'terendah: ' . $bot['school'] . ' (' . $f($bot['gain']) . ' poin, n=' . $bot['gain_n'] . ').';
            }
            $risk = array_values(array_filter($schools, fn ($s) => $s['n'] >= 3 && $s['intervention'] > 0));
            if ($risk !== []) {
                usort($risk, fn ($a, $b) => $b['intervention_pct'] <=> $a['intervention_pct']);
                $r = $risk[0];
                $out[] = 'Proporsi siswa perlu intervensi tertinggi: ' . $r['school'] . ' (' . $r['intervention'] . ' dari ' . $r['n']
                    . ' siswa, ' . $f($r['intervention_pct']) . '%).';
            }
            $idle = array_values(array_filter($schools, fn ($s) => $s['n'] >= 3 && $s['started_pct'] !== null && $s['started_pct'] < 50));
            if ($idle !== []) {
                $names = array_map(fn ($s) => $s['school'] . ' (' . $f($s['started_pct']) . '%)', array_slice($idle, 0, 3));
                $out[] = 'Kurang dari separuh siswa sudah mulai belajar di: ' . implode(', ', $names) . '.';
            }
        }
        if ($overview['intervention'] > 0) {
            $out[] = $overview['intervention'] . ' dari ' . $overview['n'] . ' siswa (' . $f($overview['intervention_pct'])
                . '%) memenuhi kriteria perlu intervensi.';
        }

        return $out;
    }

    private function schoolLabel(?string $school): string
    {
        $s = trim((string) $school);

        return $s === '' ? self::NO_SCHOOL : $s;
    }

    /** Senin pada minggu tanggal tsb, format Y-m-d. */
    private function weekStart(string $when): ?string
    {
        try {
            $d = new DateTimeImmutable(substr($when, 0, 19));
        } catch (\Throwable $e) {
            return null;
        }
        $dow = (int) $d->format('N');   // 1 = Senin

        return $d->modify('-' . ($dow - 1) . ' days')->format('Y-m-d');
    }
}
