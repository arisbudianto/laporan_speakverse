<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Unit::create([
            'title' => 'PRE-TEST',
            'subtitle' => 'Baseline Assessment',
            'order_number' => 1,
            'type' => 'pretest',
        ]);

        Unit::create([
            'title' => 'UNIT 1',
            'subtitle' => 'Are We Connected to Nature?',
            'order_number' => 2,
            'type' => 'unit',
        ]);

        Unit::create([
            'title' => 'UNIT 2',
            'subtitle' => 'Discovering Ourselves',
            'order_number' => 3,
            'type' => 'unit',
        ]);

        Unit::create([
            'title' => 'UNIT 3',
            'subtitle' => 'Why is Water Important?',
            'order_number' => 4,
            'type' => 'unit',
        ]);

        Unit::create([
            'title' => 'UNIT 4',
            'subtitle' => 'Why Should We Live a Healthy Life?',
            'order_number' => 5,
            'type' => 'unit',
        ]);

        Unit::create([
            'title' => 'POST-TEST',
            'subtitle' => 'Final Assessment',
            'order_number' => 6,
            'type' => 'posttest',
        ]);
    }
}
