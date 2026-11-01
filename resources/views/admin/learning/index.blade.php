@extends('layouts.admin')

@section('content')
    <div class="space-y-8">

        {{-- HEADER --}}
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white">
                Learning Content Management
            </h1>

            <p class="mt-2 text-slate-500 dark:text-slate-400">
                Manage Units, Lessons, Learning Materials, and Assessments
            </p>
        </div>

        @foreach ($units as $unit)
            <div
                class="rounded-3xl border border-slate-200
                bg-white p-6 shadow-sm
                dark:border-slate-700 dark:bg-slate-800">

                {{-- UNIT HEADER --}}
                <div class="mb-6">

                    <h2
                        class="text-2xl font-black
                        text-slate-900 dark:text-white">
                        {{ $unit->title }}
                    </h2>

                    <p
                        class="mt-1
                        text-slate-500 dark:text-slate-400">
                        {{ $unit->subtitle }}
                    </p>

                </div>

                {{-- LESSON LIST --}}
                <div
                    class="grid grid-cols-1 gap-4
                    md:grid-cols-2 xl:grid-cols-4">

                    @foreach ($unit->lessons as $lesson)
                        @php
                            /*
                            |--------------------------------------------------------------------------
                            | Assessment Unit
                            |--------------------------------------------------------------------------
                            */
                            $isTest = in_array(
                                $unit->type,
                                [
                                    'pretest',
                                    'posttest',
                                ],
                                true
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | Default Values
                            |--------------------------------------------------------------------------
                            */
                            $route = '#';

                            $label = 'Materials';

                            $count = 0;

                            $secondaryCount = null;

                            $itemText = 'Material';

                            $colorClass =
                                'text-blue-600 dark:text-blue-400';


                            /*
                            |--------------------------------------------------------------------------
                            | READING
                            |--------------------------------------------------------------------------
                            |
                            | Reading selalu menggunakan ReadingMaterial,
                            | termasuk:
                            |
                            | - PRETEST
                            | - Unit 1–4
                            | - POSTTEST
                            |
                            */
                            if ($lesson->skill_type === 'reading') {

                                $route = route(
                                    'admin.reading-materials.index',
                                    $lesson->id
                                );

                                $label = $isTest
                                    ? 'Reading Passages'
                                    : 'Reading Materials';

                                $count =
                                    $lesson
                                        ->readingMaterials
                                        ->count();

                                $secondaryCount =
                                    $lesson
                                        ->readingMaterials
                                        ->sum(
                                            function ($material) {
                                                return $material
                                                    ->questions
                                                    ->count();
                                            }
                                        );

                                $itemText = 'Material';

                                $colorClass =
                                    'text-blue-600 dark:text-blue-400';
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | LISTENING
                            |--------------------------------------------------------------------------
                            |
                            | Listening selalu menggunakan ListeningMaterial,
                            | termasuk PRETEST dan POSTTEST.
                            |
                            */
                            elseif ($lesson->skill_type === 'listening') {

                                $route = route(
                                    'admin.listening-materials.index',
                                    $lesson->id
                                );

                                $label = $isTest
                                    ? 'Listening Audio Materials'
                                    : 'Listening Materials';

                                $count =
                                    $lesson
                                        ->listeningMaterials
                                        ->count();

                                $secondaryCount =
                                    $lesson
                                        ->listeningMaterials
                                        ->sum(
                                            function ($material) {
                                                return $material
                                                    ->questions
                                                    ->count();
                                            }
                                        );

                                $itemText = 'Material';

                                $colorClass =
                                    'text-orange-600 dark:text-orange-400';
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | WRITING
                            |--------------------------------------------------------------------------
                            |
                            | PRETEST / POSTTEST:
                            | WritingQuestion langsung pada Lesson.
                            |
                            | Unit 1–4:
                            | WritingMaterial.
                            |
                            */
                            elseif ($lesson->skill_type === 'writing') {

                                if ($isTest) {

                                    $route = route(
                                        'admin.writing-lesson-questions.index',
                                        $lesson->id
                                    );

                                    $label =
                                        'Writing Questions';

                                    $count =
                                        $lesson
                                            ->writingQuestions
                                            ->count();

                                    $itemText =
                                        'Question';

                                } else {

                                    $route = route(
                                        'admin.writing-materials.index',
                                        $lesson->id
                                    );

                                    $label =
                                        'Writing Materials';

                                    $count =
                                        $lesson
                                            ->writingMaterials
                                            ->count();

                                    $itemText =
                                        'Material';
                                }

                                $colorClass =
                                    'text-green-600 dark:text-green-400';
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | SPEAKING
                            |--------------------------------------------------------------------------
                            |
                            | Speaking sekarang SELALU menggunakan
                            | SpeakingMaterial.
                            |
                            | Berlaku untuk:
                            |
                            | - PRETEST
                            | - Unit 1
                            | - Unit 2
                            | - Unit 3
                            | - Unit 4
                            | - POSTTEST
                            |
                            | PRE/POST menggunakan SpeakingMaterial sebagai
                            | Pair Speaking Assessment.
                            |
                            */
                            elseif ($lesson->skill_type === 'speaking') {

                                $route = route(
                                    'admin.speaking-materials.index',
                                    $lesson->id
                                );

                                $label = $isTest
                                    ? 'Speaking Tasks'
                                    : 'Speaking Materials';

                                $count =
                                    $lesson
                                        ->speakingMaterials
                                        ->count();

                                /*
                                |--------------------------------------------------------------------------
                                | PRE/POST menggunakan istilah Task.
                                |--------------------------------------------------------------------------
                                */
                                $itemText = $isTest
                                    ? 'Task'
                                    : 'Material';

                                $colorClass =
                                    'text-purple-600 dark:text-purple-400';
                            }
                        @endphp


                        {{-- LESSON CARD --}}
                        <a
                            href="{{ $route }}"
                            class="group rounded-2xl
                            border border-slate-200
                            bg-slate-50 p-5
                            transition-all duration-300
                            hover:border-blue-500
                            hover:shadow-lg
                            dark:border-slate-700
                            dark:bg-slate-900">

                            {{-- CARD HEADER --}}
                            <div
                                class="flex items-center
                                justify-between">

                                <h3
                                    class="text-lg font-bold
                                    text-slate-900 dark:text-white">

                                    {{ ucfirst($lesson->skill_type) }}

                                </h3>

                                <span
                                    class="rounded-full
                                    bg-blue-100
                                    px-2 py-1
                                    text-xs font-semibold
                                    text-blue-700
                                    dark:bg-blue-900/40
                                    dark:text-blue-300">

                                    {{ strtoupper(
                                        substr(
                                            $lesson->skill_type,
                                            0,
                                            1
                                        )
                                    ) }}

                                </span>

                            </div>


                            {{-- CARD CONTENT --}}
                            <div class="mt-3">

                                <span
                                    class="text-sm
                                    text-slate-500
                                    dark:text-slate-400">

                                    {{ $label }}

                                </span>


                                {{-- MAIN COUNT --}}
                                <div
                                    class="mt-1 text-xl
                                    font-bold {{ $colorClass }}">

                                    {{ $count }}

                                    {{ Str::plural(
                                        $itemText,
                                        $count
                                    ) }}

                                </div>


                                {{-- QUESTION COUNT --}}
                                @if (
                                    in_array(
                                        $lesson->skill_type,
                                        [
                                            'reading',
                                            'listening',
                                        ],
                                        true
                                    )
                                )

                                    <div
                                        class="mt-2
                                        text-sm
                                        text-slate-500
                                        dark:text-slate-400">

                                        {{ $secondaryCount }}

                                        {{ Str::plural(
                                            'Question',
                                            $secondaryCount
                                        ) }}

                                    </div>

                                @endif

                            </div>

                        </a>
                    @endforeach

                </div>

            </div>
        @endforeach

        {{-- STANDALONE ASSESSMENTS (tidak terikat ke Unit/Lesson) --}}
        <div
            class="rounded-3xl border border-slate-200
            bg-white p-6 shadow-sm
            dark:border-slate-700 dark:bg-slate-800">

            <div class="mb-6">
                <h2 class="text-2xl font-black text-slate-900 dark:text-white">
                    Standalone Assessments
                </h2>

                <p class="mt-1 text-slate-500 dark:text-slate-400">
                    Assessments not tied to a specific Unit/Lesson.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <a
                    href="{{ route('admin.vocabulary-pretests.index') }}"
                    class="rounded-2xl border border-slate-200 p-5
                    transition hover:border-cyan-400 hover:shadow-md
                    dark:border-slate-700">

                    <p class="font-black text-slate-900 dark:text-white">
                        Vocabulary Pretest
                    </p>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Question Bank
                    </p>

                    <div class="mt-3 text-2xl font-black text-purple-600 dark:text-purple-400">
                        {{ $vocabularyPretestCount }}

                        {{ Str::plural('Question', $vocabularyPretestCount) }}
                    </div>
                </a>
            </div>
        </div>

    </div>

@endsection