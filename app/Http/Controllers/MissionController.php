<?php

namespace App\Http\Controllers;

use App\Models\AssessmentSubmission;
use App\Models\Unit;
use App\Models\UserLessonProgress;
use Illuminate\Support\Facades\Auth;

class MissionController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $skills = [
            'listening',
            'reading',
            'writing',
            'speaking',
        ];

        $units = Unit::with('lessons')
            ->where('status', 'active')
            ->orderBy('order_number')
            ->get();

        $completedMap = [];

        foreach ($units as $unit) {
            if ($unit->type === 'unit') {
                $completedSkills = UserLessonProgress::where('user_id', $userId)
                    ->where('unit_id', $unit->id)
                    ->where('status', 'completed')
                    ->pluck('skill_type')
                    ->unique()
                    ->values()
                    ->toArray();
            } else {
                $completedSkills = AssessmentSubmission::where('user_id', $userId)
                    ->where('unit_id', $unit->id)
                    ->where('type', $unit->type)
                    ->where('status', 'completed')
                    ->whereNotNull('final_score')
                    ->pluck('skill')
                    ->unique()
                    ->values()
                    ->toArray();
            }

            $completedMap[$unit->id] = [
                'completed_skills' => $completedSkills,
                'completed_count' => count($completedSkills),
                'is_completed' => count($completedSkills) >= count($skills),
            ];
        }

        $unlockMap = [];

        foreach ($units as $index => $unit) {
            if ($index === 0) {
                $unlockMap[$unit->id] = true;
                continue;
            }

            $previousUnit = $units[$index - 1];

            $unlockMap[$unit->id] = $completedMap[$previousUnit->id]['is_completed'] ?? false;
        }

        $completedTotal = collect($completedMap)
            ->where('is_completed', true)
            ->count();

        $totalStages = $units->count();

        $overallProgress = $totalStages > 0
            ? round(($completedTotal / $totalStages) * 100)
            : 0;

        return view('missions.index', [
            'units' => $units,
            'skills' => $skills,
            'completedMap' => $completedMap,
            'unlockMap' => $unlockMap,
            'completedTotal' => $completedTotal,
            'totalStages' => $totalStages,
            'overallProgress' => $overallProgress,
        ]);
    }
}