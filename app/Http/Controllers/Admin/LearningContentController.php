<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\VocabularyPretest;

class LearningContentController extends Controller
{
    public function index()
    {
        $units = Unit::with([

            /*
            |--------------------------------------------------------------------------
            | Reading
            |--------------------------------------------------------------------------
            */
            'lessons.readingMaterials.questions',

            /*
            |--------------------------------------------------------------------------
            | Listening
            |--------------------------------------------------------------------------
            |
            | Sekarang Listening (termasuk Pre-test & Post-test)
            | menggunakan ListeningMaterial seperti Reading.
            | Maka relasi questions wajib ikut di-load.
            |
            */
            'lessons.listeningMaterials.questions',

            /*
            |--------------------------------------------------------------------------
            | Speaking
            |--------------------------------------------------------------------------
            */
            'lessons.speakingMaterials',

            /*
            |--------------------------------------------------------------------------
            | Writing
            |--------------------------------------------------------------------------
            */
            'lessons.writingMaterials',

            /*
            |--------------------------------------------------------------------------
            | Legacy Lesson Questions
            |--------------------------------------------------------------------------
            |
            | Masih dipertahankan supaya data lama tidak error.
            | Nantinya bisa dihapus jika seluruh data sudah
            | berpindah ke Material Mode.
            |
            */
            'lessons.listeningQuestions',
            'lessons.speakingQuestions',
            'lessons.writingQuestions',

        ])
            ->orderBy('order_number')
            ->get();

        $vocabularyPretestCount = VocabularyPretest::query()->count();

        return view(
            'admin.learning.index',
            compact('units', 'vocabularyPretestCount')
        );
    }
}