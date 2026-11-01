<?php

namespace Database\Seeders;

use App\Models\TeacherClassAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TrialAccountSeeder extends Seeder
{
    public function run(): void
    {
        $teacherPassword = Hash::make('UjiGuru26!');
        $studentPassword = Hash::make('UjiSiswa26!');

        $schools = [
            [
                'teacher' => [
                    'name' => 'Guru Uji SMKN 2',
                    'email' => 'guru.smkn2@speakverse.id',
                    'nip' => '197001012006041001',
                    'school' => 'SMK N 2 Surakarta',
                ],
                'major' => 'Pemrograman Perangkat Lunak & GIM',
                'grade' => 'X',
                'parallel' => 'A',
                'students' => [
                    ['name' => 'Siswa Uji SMKN 2 A', 'email' => 'siswa.smkn2.a@speakverse.id'],
                    ['name' => 'Siswa Uji SMKN 2 B', 'email' => 'siswa.smkn2.b@speakverse.id'],
                    ['name' => 'Siswa Uji SMKN 2 C', 'email' => 'siswa.smkn2.c@speakverse.id'],
                ],
            ],
            [
                'teacher' => [
                    'name' => 'Guru Uji SMKN 5',
                    'email' => 'guru.smkn5@speakverse.id',
                    'nip' => '197002022006042002',
                    'school' => 'SMK N 5 Surakarta',
                ],
                'major' => 'Pengembangan Perangkat Lunak dan Gim (PPLG)',
                'grade' => 'X',
                'parallel' => 'A',
                'students' => [
                    ['name' => 'Siswa Uji SMKN 5 A', 'email' => 'siswa.smkn5.a@speakverse.id'],
                    ['name' => 'Siswa Uji SMKN 5 B', 'email' => 'siswa.smkn5.b@speakverse.id'],
                    ['name' => 'Siswa Uji SMKN 5 C', 'email' => 'siswa.smkn5.c@speakverse.id'],
                ],
            ],
            [
                'teacher' => [
                    'name' => 'Guru Uji SMKN 6',
                    'email' => 'guru.smkn6@speakverse.id',
                    'nip' => '197003032006043003',
                    'school' => 'SMK N 6 Surakarta',
                ],
                'major' => 'Usaha Layanan Pariwisata',
                'grade' => 'X',
                'parallel' => 'A',
                'students' => [
                    ['name' => 'Siswa Uji SMKN 6 A', 'email' => 'siswa.smkn6.a@speakverse.id'],
                    ['name' => 'Siswa Uji SMKN 6 B', 'email' => 'siswa.smkn6.b@speakverse.id'],
                    ['name' => 'Siswa Uji SMKN 6 C', 'email' => 'siswa.smkn6.c@speakverse.id'],
                ],
            ],
        ];

        // 'role', 'xp', 'coins', dan 'level' tidak lagi mass-assignable
        // (lihat App\Models\User). Seeder adalah kode tepercaya yang
        // dijalankan developer, jadi User::unguarded() dipakai untuk
        // mengizinkan mass assignment atribut-atribut tersebut khusus
        // di dalam closure ini saja.
        User::unguarded(function () use ($schools, $teacherPassword, $studentPassword) {
            foreach ($schools as $pack) {
                $teacher = User::query()->updateOrCreate(
                    ['email' => $pack['teacher']['email']],
                    [
                        'name' => $pack['teacher']['name'],
                        'password' => $teacherPassword,
                        'role' => 'teacher',
                        'nip' => $pack['teacher']['nip'],
                        'school' => $pack['teacher']['school'],
                        'major' => null,
                        'grade' => null,
                        'parallel' => null,
                        'email_verified_at' => now(),
                    ]
                );

                TeacherClassAssignment::query()->firstOrCreate([
                    'user_id' => $teacher->id,
                    'school' => $pack['teacher']['school'],
                    'major' => $pack['major'],
                    'grade' => $pack['grade'],
                    'parallel' => $pack['parallel'],
                ]);

                foreach ($pack['students'] as $student) {
                    User::query()->updateOrCreate(
                        ['email' => $student['email']],
                        [
                            'name' => $student['name'],
                            'password' => $studentPassword,
                            'role' => 'student',
                            'school' => $pack['teacher']['school'],
                            'major' => $pack['major'],
                            'grade' => $pack['grade'],
                            'parallel' => $pack['parallel'],
                            'email_verified_at' => now(),
                            'xp' => 0,
                            'coins' => 0,
                            'level' => 1,
                        ]
                    );
                }
            }
        });
    }
}
