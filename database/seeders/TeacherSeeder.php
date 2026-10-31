<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            [
                'name' => 'Wening Handayani, S.Pd, M.Pd.',
                'email' => 'wening.handayani@speakverse.id',
                'nip' => '197806222003122004',
                'school' => 'SMK N 2 Surakarta',
            ],
            [
                'name' => 'Siti Arifatun Nisak, S.S.',
                'email' => 'siti.arifatun.nisak@speakverse.id',
                'nip' => '197907052008012018',
                'school' => 'SMK N 5 Surakarta',
            ],
            [
                'name' => 'Siti Aminah, S.Pd',
                'email' => 'siti.aminah@speakverse.id',
                'nip' => '197103072006042013',
                'school' => 'SMK N 6 Surakarta',
            ],
        ];

        foreach ($teachers as $teacher) {
            User::query()->updateOrCreate(
                ['nip' => $teacher['nip']],
                [
                    'name' => $teacher['name'],
                    'email' => $teacher['email'],
                    'password' => Hash::make('SpeakVerse2026!'),
                    'role' => 'teacher',
                    'school' => $teacher['school'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
