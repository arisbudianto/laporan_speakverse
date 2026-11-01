<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VocabularyPretest;
use App\Models\VocabularyPretestResult;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::query()->count();

        $totalQuestions = VocabularyPretest::query()->count();

        $totalAttempts = VocabularyPretestResult::query()->count();

        $averageScore = round(
            VocabularyPretestResult::query()->avg('score') ?? 0,
            1
        );

        $recentUsers = User::query()
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalQuestions',
            'totalAttempts',
            'averageScore',
            'recentUsers'
        ));
    }
}