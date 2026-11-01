<?php

namespace App\Console\Commands;

use App\Models\TeacherClassAssignment;
use App\Models\User;
use Illuminate\Console\Command;

class SyncSchoolMajorsCommand extends Command
{
    protected $signature = 'schools:sync-majors {--dry-run : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Sesuaikan jurusan lama di users dan teacher_class_assignments ke daftar terbaru';

    public function handle(): int
    {
        $map = $this->map();
        $dry = (bool) $this->option('dry-run');
        $updatedUsers = 0;
        $updatedAssignments = 0;

        User::query()
            ->whereNotNull('major')
            ->where('major', '!=', '')
            ->orderBy('id')
            ->each(function (User $user) use ($map, $dry, &$updatedUsers) {
                $next = $this->mappedMajor($user->school, $user->major, $map);
                if ($next === null || $next === $user->major) {
                    return;
                }

                $this->line("user #{$user->id} [{$user->school}] {$user->major} => {$next}");
                if (! $dry) {
                    $user->major = $next;
                    $user->save();
                }
                $updatedUsers++;
            });

        TeacherClassAssignment::query()
            ->orderBy('id')
            ->each(function (TeacherClassAssignment $row) use ($map, $dry, &$updatedAssignments) {
                $next = $this->mappedMajor($row->school, $row->major, $map);
                if ($next === null || $next === $row->major) {
                    return;
                }

                $this->line("kelas guru #{$row->id} [{$row->school}] {$row->major} => {$next}");
                if (! $dry) {
                    $row->major = $next;
                    $row->save();
                }
                $updatedAssignments++;
            });

        $this->info("Users diubah: {$updatedUsers}");
        $this->info("Assignment guru diubah: {$updatedAssignments}");

        if ($dry) {
            $this->warn('Dry-run: tidak ada data yang disimpan.');
        }

        return self::SUCCESS;
    }

    private function mappedMajor(?string $school, ?string $major, array $map): ?string
    {
        if (! $school || ! $major) {
            return null;
        }

        if (isset($map[$school][$major])) {
            return $map[$school][$major];
        }

        $allowed = config('schools', [])[$school] ?? [];
        if (in_array($major, $allowed, true)) {
            return $major;
        }

        return $map[$school][$this->normalize($major)] ?? null;
    }

    private function normalize(string $value): string
    {
        $value = strtolower($value);
        $value = str_replace(['&', '/', '(', ')', ',', '.'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?: $value;

        return trim($value);
    }

    private function map(): array
    {
        return [
            'SMK N 2 Surakarta' => [
                'Teknik Konstruksi dan Perumahan (TKP)' => 'Teknik Konstruksi dan Perumahan',
                'Desain Pemodelan dan Informasi Bangunan (DPIB)' => 'Desain Pemodelan & Informasi Bangunan',
                'Teknik Geomatika (Geo)' => 'Teknik Geomatika',
                'Teknik Audio Video (TAV)' => 'Teknik Elektronika',
                'Teknik Instalasi Tenaga Listrik (TITL)' => 'Teknik Ketenagalistrikan',
                'Teknik Pemesinan (TP)' => 'Teknik Mesin',
                'Teknik Pengelasan (TPG)' => 'Teknik Pengelasan & Fabrikasi Logam',
                'Teknik Kendaraan Ringan (TKR)' => 'Teknik Otomotif',
                'Teknik Komputer dan Jaringan (TKJ)' => 'Teknik Jaringan Komputer & Telekomunikasi',
            ],
            'SMK N 5 Surakarta' => [
                'Teknik Pemesinan (TP)' => 'Teknik Mesin (TM)',
                'Teknik Kendaraan Ringan (TKR)' => 'Teknik Kendaraan Ringan Otomotif (TKRO)',
                'Teknik Sepeda Motor (TSM)' => 'Teknik dan Bisnis Sepeda Motor',
                'Teknik Instalasi Tenaga Listrik (TITL)' => 'Teknik Ketenagalistrikan (TK)',
                'Teknik Ketenagalistrikan / Teknik Energi Terbarukan' => 'Teknik Ketenagalistrikan (TK)',
                'Teknik Komputer dan Jaringan (TKJ)' => 'Pengembangan Perangkat Lunak dan Gim (PPLG)',
                'Teknik Fabrikasi Logam dan Manufaktur' => 'Teknik Mesin (TM)',
                'Desain Komunikasi Visual (DKV)' => 'Pengembangan Perangkat Lunak dan Gim (PPLG)',
            ],
            'SMK N 6 Surakarta' => [
                'Pengembangan Perangkat Lunak dan Gim (PPLG)' => 'Rekayasa Perangkat Lunak',
                'Manajemen Perkantoran dan Layanan Bisnis (MPLB)' => 'Manajemen Perkantoran dan Layanan Bisnis',
                'Manajeman Perkantoran dan Layanan Bisnis (MPLB)' => 'Manajemen Perkantoran dan Layanan Bisnis',
                'Akuntansi dan Keuangan Lembaga (AKL)' => 'Akuntansi Keuangan Lembaga',
                'Pemasaran (PM)' => 'Pemasaran',
                'Desain Komunikasi Visual (DKV)' => 'Desain Komunikasi Visual',
                'Broadcaster dan Perfilman (BCF)' => 'Broadcasting dan Perfilman',
                'Kuliner / Tata Boga (TB)' => 'Usaha Layanan Pariwisata',
            ],
            'SMK N 1 Banyudono (Boyolali)' => [
                'Manajemen Perkantoran dan Layanan Bisnis (MPLB)' => 'Otomatisasi dan Kelola Perkantoran',
                'Akuntansi dan Keuangan Lembaga (AKL)' => 'Akuntansi dan Keuangan Lembaga',
                'Pemasaran (PM)' => 'Bisnis Daring dan Pemasaran',
                'Teknik Komputer dan Jaringan (TKJ)' => 'Teknik Jaringan dan Komputer',
                'Desain Komunikasi Visual (DKV)' => 'Bisnis Daring dan Pemasaran',
            ],
        ];
    }
}
