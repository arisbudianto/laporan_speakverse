@extends('layouts.admin')

@section('content')
    @php
        $isLessonMode = isset($lesson);

        $lessonTitle = $isLessonMode
            ? ($lesson->title ?? ucfirst($lesson->skill_type ?? 'Writing'))
            : null;

        $unitTitle = $isLessonMode
            ? optional($lesson->unit)->title
            : null;

        $unitType = $isLessonMode
            ? optional($lesson->unit)->type
            : null;

        $isAssessment = $isLessonMode
            && in_array(
                $unitType,
                [
                    'pretest',
                    'posttest',
                ],
                true
            );

        $assessmentLabel = $isAssessment
            ? strtoupper($unitType)
            : null;

        $contextTitle = $isLessonMode
            ? (
                $unitTitle
                    ? $unitTitle . ' — ' . $lessonTitle
                    : $lessonTitle
            )
            : ($material->title ?? 'Writing Material');

        $formAction = $isLessonMode
            ? route(
                'admin.writing-lesson-questions.store',
                $lesson->id
            )
            : route(
                'admin.writing-questions.store',
                $material->id
            );

        $backRoute = $isLessonMode
            ? route(
                'admin.writing-lesson-questions.index',
                $lesson->id
            )
            : route(
                'admin.writing-questions.index',
                $material->id
            );
    @endphp


    <div class="mx-auto max-w-5xl space-y-7">

        {{-- ============================================================
            HEADER
        ============================================================ --}}
        <section
            class="relative overflow-hidden
            rounded-[28px]
            border border-slate-200
            bg-white
            p-6
            shadow-sm
            dark:border-slate-700
            dark:bg-slate-800
            md:p-8">

            <div
                class="pointer-events-none absolute
                -right-24 -top-24
                h-64 w-64
                rounded-full
                bg-purple-500/10
                blur-3xl">
            </div>

            <div class="relative z-10">

                <a
                    href="{{ $backRoute }}"
                    class="mb-5 inline-flex
                    items-center gap-2
                    rounded-xl
                    border border-slate-200
                    bg-slate-50
                    px-4 py-2
                    text-sm font-semibold
                    text-slate-600
                    transition
                    hover:bg-slate-100
                    hover:text-slate-900
                    dark:border-slate-700
                    dark:bg-slate-900
                    dark:text-slate-300
                    dark:hover:bg-slate-700
                    dark:hover:text-white">

                    <span class="text-lg">
                        ←
                    </span>

                    Back to Writing Questions
                </a>


                <div
                    class="mb-4 inline-flex
                    items-center gap-2
                    rounded-full
                    border border-purple-200
                    bg-purple-50
                    px-4 py-2
                    text-sm font-bold
                    text-purple-700
                    dark:border-purple-500/20
                    dark:bg-purple-500/10
                    dark:text-purple-300">

                    <span>
                        ✍️
                    </span>

                    @if ($isAssessment)
                        {{ $assessmentLabel }} Writing Assessment
                    @elseif ($isLessonMode)
                        Writing Assessment
                    @else
                        Writing Material
                    @endif
                </div>


                <h1
                    class="text-3xl font-black
                    tracking-tight
                    text-slate-900
                    dark:text-white
                    md:text-4xl">

                    Add Writing Question
                </h1>

                <p
                    class="mt-3 max-w-2xl
                    text-base leading-7
                    text-slate-500
                    dark:text-slate-400">

                    @if ($isLessonMode)
                        Create a writing question
                        for this assessment.
                    @else
                        Create a new question
                        for this writing material.
                    @endif
                </p>

            </div>

        </section>


        {{-- ============================================================
            VALIDATION ERRORS
        ============================================================ --}}
        @if ($errors->any())
            <div
                class="rounded-2xl
                border border-red-200
                bg-red-50
                px-5 py-4
                text-red-700
                shadow-sm
                dark:border-red-500/20
                dark:bg-red-500/10
                dark:text-red-300">

                <div class="flex items-start gap-3">

                    <div
                        class="flex h-10 w-10
                        shrink-0 items-center
                        justify-center
                        rounded-xl
                        bg-red-100
                        font-black
                        dark:bg-red-500/10">

                        !
                    </div>

                    <div>
                        <h2 class="font-black">
                            Please check the form again.
                        </h2>

                        <ul
                            class="mt-2 list-disc
                            space-y-1 pl-5
                            text-sm">

                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach

                        </ul>
                    </div>

                </div>

            </div>
        @endif


        {{-- ============================================================
            CONTEXT
        ============================================================ --}}
        <section
            class="rounded-[26px]
            border border-slate-200
            bg-white
            p-6
            shadow-sm
            dark:border-slate-700
            dark:bg-slate-800">

            <div
                class="flex flex-col gap-5
                sm:flex-row
                sm:items-center
                sm:justify-between">

                <div
                    class="flex items-center gap-4">

                    <div
                        class="flex h-14 w-14
                        shrink-0
                        items-center
                        justify-center
                        rounded-2xl
                        bg-purple-100
                        text-2xl
                        dark:bg-purple-500/10">

                        ✍️
                    </div>

                    <div>

                        <p
                            class="text-sm font-semibold
                            text-slate-400">

                            {{ $isLessonMode
                                ? 'Current Assessment'
                                : 'Current Material' }}
                        </p>

                        <h2
                            class="mt-1 text-xl
                            font-black
                            text-slate-900
                            dark:text-white">

                            {{ $contextTitle }}
                        </h2>

                    </div>

                </div>


                <div
                    class="flex flex-wrap
                    items-center gap-2">

                    @if ($isAssessment)

                        <span
                            class="inline-flex
                            rounded-full
                            bg-purple-100
                            px-4 py-2
                            text-sm font-bold
                            text-purple-700
                            dark:bg-purple-500/10
                            dark:text-purple-300">

                            {{ $assessmentLabel }}
                        </span>

                    @endif

                    <span
                        class="inline-flex
                        rounded-full
                        bg-blue-100
                        px-4 py-2
                        text-sm font-bold
                        text-blue-700
                        dark:bg-blue-500/10
                        dark:text-blue-300">

                        Writing
                    </span>

                </div>

            </div>

        </section>


        {{-- ============================================================
            FORM
        ============================================================ --}}
        <form
            action="{{ $formAction }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6">

            @csrf


            {{-- ========================================================
                QUESTION
            ======================================================== --}}
            <section
                class="rounded-[26px]
                border border-slate-200
                bg-white
                p-6
                shadow-sm
                dark:border-slate-700
                dark:bg-slate-800">

                <div
                    class="mb-6
                    border-b border-slate-100
                    pb-4
                    dark:border-slate-700">

                    <h2
                        class="text-lg font-black
                        text-slate-900
                        dark:text-white">

                        Question
                    </h2>

                    <p
                        class="mt-1 text-sm
                        text-slate-500
                        dark:text-slate-400">

                        Write the instruction or prompt
                        students must respond to.
                    </p>

                </div>


                <label
                    for="question"
                    class="mb-2 block
                    text-sm font-bold
                    text-slate-800
                    dark:text-slate-200">

                    Question Prompt

                    <span class="text-red-500">
                        *
                    </span>
                </label>


                <textarea
                    id="question"
                    name="question"
                    rows="6"
                    required
                    placeholder="Example: Describe the picture using at least five sentences."
                    class="w-full rounded-xl
                    border border-slate-300
                    bg-white
                    px-4 py-3
                    text-slate-900
                    placeholder:text-slate-400
                    outline-none
                    transition
                    focus:border-blue-500
                    focus:ring-2
                    focus:ring-blue-500/20
                    dark:border-slate-600
                    dark:bg-slate-900
                    dark:text-white
                    dark:placeholder:text-slate-500">{{ old('question') }}</textarea>

                <p
                    class="mt-2 text-sm
                    text-slate-500
                    dark:text-slate-400">

                    Use a clear and specific prompt
                    so students understand what they need to write.
                </p>

                @error('question')
                    <p
                        class="mt-2
                        text-sm font-semibold
                        text-red-500">

                        {{ $message }}
                    </p>
                @enderror

            </section>


            {{-- ========================================================
                SAMPLE ANSWER
            ======================================================== --}}
            <section
                class="rounded-[26px]
                border border-slate-200
                bg-white
                p-6
                shadow-sm
                dark:border-slate-700
                dark:bg-slate-800">

                <div
                    class="mb-6
                    border-b border-slate-100
                    pb-4
                    dark:border-slate-700">

                    <h2
                        class="text-lg font-black
                        text-slate-900
                        dark:text-white">

                        Sample Answer
                    </h2>

                    <p
                        class="mt-1 text-sm
                        text-slate-500
                        dark:text-slate-400">

                        Optional reference answer
                        for assessment and evaluation.
                    </p>

                </div>


                <label
                    for="sample_answer"
                    class="mb-2 block
                    text-sm font-bold
                    text-slate-800
                    dark:text-slate-200">

                    Example Response
                </label>


                <textarea
                    id="sample_answer"
                    name="sample_answer"
                    rows="9"
                    placeholder="Write an example of an acceptable answer..."
                    class="w-full rounded-xl
                    border border-slate-300
                    bg-white
                    px-4 py-3
                    text-slate-900
                    placeholder:text-slate-400
                    outline-none
                    transition
                    focus:border-blue-500
                    focus:ring-2
                    focus:ring-blue-500/20
                    dark:border-slate-600
                    dark:bg-slate-900
                    dark:text-white
                    dark:placeholder:text-slate-500">{{ old('sample_answer') }}</textarea>

                <p
                    class="mt-2 text-sm
                    text-slate-500
                    dark:text-slate-400">

                    This can be used as a reference
                    when evaluating the student's answer.
                </p>

                @error('sample_answer')
                    <p
                        class="mt-2
                        text-sm font-semibold
                        text-red-500">

                        {{ $message }}
                    </p>
                @enderror

            </section>


            {{-- ========================================================
                IMAGE
            ======================================================== --}}
            <section
                class="rounded-[26px]
                border border-slate-200
                bg-white
                p-6
                shadow-sm
                dark:border-slate-700
                dark:bg-slate-800">

                <div
                    class="mb-6
                    border-b border-slate-100
                    pb-4
                    dark:border-slate-700">

                    <h2
                        class="text-lg font-black
                        text-slate-900
                        dark:text-white">

                        Question Image
                    </h2>

                    <p
                        class="mt-1 text-sm
                        text-slate-500
                        dark:text-slate-400">

                        Add an optional image
                        students should observe
                        before answering the question.
                    </p>

                </div>


                {{-- UPLOAD AREA --}}
                <div
                    class="rounded-2xl
                    border border-dashed
                    border-slate-300
                    bg-slate-50
                    p-5
                    dark:border-slate-600
                    dark:bg-slate-900">

                    <div
                        class="flex flex-col gap-4
                        sm:flex-row
                        sm:items-start">

                        {{-- ICON --}}
                        <div
                            class="flex h-12 w-12
                            shrink-0
                            items-center
                            justify-center
                            rounded-xl
                            bg-purple-100
                            text-2xl
                            dark:bg-purple-500/10">

                            🖼️
                        </div>


                        {{-- INPUT --}}
                        <div class="min-w-0 flex-1">

                            <label
                                for="image"
                                class="mb-2 block
                                text-sm font-bold
                                text-slate-800
                                dark:text-slate-200">

                                Select Image

                                <span
                                    class="font-normal
                                    text-slate-400">

                                    (Optional)
                                </span>
                            </label>


                            <input
                                id="image"
                                type="file"
                                name="image"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                onchange="previewSelectedImage(event)"
                                class="block w-full
                                cursor-pointer
                                rounded-xl
                                border border-slate-300
                                bg-white
                                text-sm
                                text-slate-600
                                transition
                                file:mr-4
                                file:border-0
                                file:bg-slate-100
                                file:px-4
                                file:py-3
                                file:font-bold
                                file:text-slate-700
                                hover:file:bg-slate-200
                                focus:outline-none
                                dark:border-slate-600
                                dark:bg-slate-950
                                dark:text-slate-300
                                dark:file:bg-slate-700
                                dark:file:text-white
                                dark:hover:file:bg-slate-600">


                            <p
                                class="mt-3
                                text-sm
                                text-slate-500
                                dark:text-slate-400">

                                Supported formats:
                                JPG, JPEG, PNG, or WEBP.
                            </p>


                            @error('image')
                                <p
                                    class="mt-2
                                    text-sm font-semibold
                                    text-red-500">

                                    {{ $message }}
                                </p>
                            @enderror


                            {{-- PREVIEW --}}
                            <div
                                id="imagePreviewContainer"
                                class="mt-5 hidden">

                                <p
                                    class="mb-2
                                    text-sm font-bold
                                    text-slate-700
                                    dark:text-slate-300">

                                    Image Preview
                                </p>

                                <div
                                    class="inline-flex
                                    max-w-full
                                    overflow-hidden
                                    rounded-2xl
                                    border border-slate-200
                                    bg-white
                                    p-2
                                    shadow-sm
                                    dark:border-slate-700
                                    dark:bg-slate-950">

                                    <img
                                        id="imagePreview"
                                        src=""
                                        alt="Question image preview"
                                        class="max-h-80
                                        max-w-full
                                        rounded-xl
                                        object-contain">

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </section>


            {{-- ========================================================
                ACTIONS
            ======================================================== --}}
            <div
                class="flex flex-col-reverse
                gap-3
                sm:flex-row
                sm:items-center
                sm:justify-end">

                <a
                    href="{{ $backRoute }}"
                    class="inline-flex
                    items-center
                    justify-center
                    rounded-xl
                    bg-slate-200
                    px-6 py-3
                    font-bold
                    text-slate-700
                    transition
                    hover:bg-slate-300
                    dark:bg-slate-700
                    dark:text-white
                    dark:hover:bg-slate-600">

                    Cancel
                </a>


                <button
                    type="submit"
                    class="inline-flex
                    items-center
                    justify-center
                    rounded-xl
                    bg-blue-600
                    px-6 py-3
                    font-bold
                    text-white
                    shadow-lg
                    shadow-blue-600/20
                    transition
                    hover:-translate-y-0.5
                    hover:bg-blue-700
                    hover:shadow-xl
                    focus:outline-none
                    focus:ring-2
                    focus:ring-blue-500
                    focus:ring-offset-2
                    dark:bg-blue-600
                    dark:text-white
                    dark:hover:bg-blue-500
                    dark:focus:ring-offset-slate-900">

                    Save Question
                </button>

            </div>

        </form>

    </div>


    {{-- ================================================================
        IMAGE PREVIEW SCRIPT
    ================================================================= --}}
    <script>
        function previewSelectedImage(event) {
            const input =
                event.target;

            const container =
                document.getElementById(
                    'imagePreviewContainer'
                );

            const preview =
                document.getElementById(
                    'imagePreview'
                );

            if (
                !container ||
                !preview
            ) {
                return;
            }


            const file =
                input.files &&
                input.files[0]
                    ? input.files[0]
                    : null;


            if (!file) {
                preview.src = '';

                container.classList.add(
                    'hidden'
                );

                return;
            }


            if (
                !file.type.startsWith(
                    'image/'
                )
            ) {
                input.value = '';

                preview.src = '';

                container.classList.add(
                    'hidden'
                );

                alert(
                    'Please select a valid image file.'
                );

                return;
            }


            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp',
            ];


            if (
                !allowedTypes.includes(
                    file.type
                )
            ) {
                input.value = '';

                preview.src = '';

                container.classList.add(
                    'hidden'
                );

                alert(
                    'Only JPG, JPEG, PNG, and WEBP images are supported.'
                );

                return;
            }


            const maxFileSize =
                2 * 1024 * 1024;


            if (
                file.size >
                maxFileSize
            ) {
                input.value = '';

                preview.src = '';

                container.classList.add(
                    'hidden'
                );

                alert(
                    'The image size must not exceed 2 MB.'
                );

                return;
            }


            const reader =
                new FileReader();


            reader.onload =
                function (loadEvent) {
                    preview.src =
                        loadEvent.target.result;

                    container.classList.remove(
                        'hidden'
                    );
                };


            reader.readAsDataURL(
                file
            );
        }
    </script>
@endsection