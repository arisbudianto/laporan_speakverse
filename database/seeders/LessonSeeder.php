<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            'listening',
            'reading',
            'writing',
            'speaking'
        ];

        $units = Unit::where('type', 'unit')->get();

        foreach ($units as $unit) {

            $order = 1;

            foreach ($skills as $skill) {

                Lesson::create([
                    'unit_id' => $unit->id,
                    'skill_type' => $skill,
                    'title' => ucfirst($skill),
                    'description' => ucfirst($skill) . ' lesson for ' . $unit->title,
                    'order_number' => $order++,
                    'status' => 'active'
                ]);
            }
        }
    }
}