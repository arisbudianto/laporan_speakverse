<x-app-layout>

    <div class="space-y-8 max-w-3xl mx-auto">

        {{-- HEADER --}}
        <section
            class="relative overflow-hidden rounded-[32px]
            border border-slate-200 dark:border-white/10
            bg-white/70 dark:bg-white/5
            backdrop-blur-2xl
            p-6 md:p-8">

            <div
                class="absolute top-[-100px] right-[-100px]
                w-[250px] h-[250px]
                bg-cyan-400/10 rounded-full blur-3xl">
            </div>

            <div class="relative z-10">
                <div
                    class="inline-flex items-center gap-2
                    px-4 py-2 rounded-full
                    bg-cyan-500/10 border border-cyan-400/20
                    text-cyan-400 text-sm font-medium mb-4">
                    📖 Vocabulary Pretest
                </div>

                <h1 class="text-3xl md:text-4xl font-black leading-tight">
                    Test Your Vocabulary
                </h1>

                <p class="mt-3 text-slate-600 dark:text-slate-400">
                    Answer each of the {{ $questions->count() }}
                    {{ \Illuminate\Support\Str::plural('question', $questions->count()) }}
                    below, then click Check. You can ask for a hint if you get stuck.
                </p>
            </div>
        </section>

        @if ($questions->isEmpty())
            {{-- EMPTY STATE --}}
            <section
                class="rounded-[32px] border border-slate-200 dark:border-white/10
                bg-white/70 dark:bg-white/5 backdrop-blur-xl
                p-10 text-center">

                <h2 class="text-xl font-black">No Questions Yet</h2>

                <p class="mt-2 text-slate-500 dark:text-slate-400">
                    The administrator has not added Vocabulary Pretest
                    questions yet. Please check back later.
                </p>

                <a href="{{ route('missions') }}"
                    class="mt-6 inline-flex items-center justify-center
                    px-6 py-3 rounded-2xl
                    bg-gradient-to-r from-cyan-500 to-blue-600
                    text-white font-bold">
                    Back to Missions
                </a>
            </section>
        @else
            {{-- RESULT (Modul 5 — ditampilkan setelah Finish, awalnya tersembunyi) --}}
            <section
                id="vocabResultSection"
                class="rounded-[32px] border border-slate-200 dark:border-white/10
                bg-white/70 dark:bg-white/5 backdrop-blur-xl
                p-8 text-center"
                hidden>

                <h2 class="text-xl font-black">Pretest Completed</h2>

                <p class="mt-2 text-slate-600 dark:text-slate-400" id="vocabFinalMessage">
                    Your result has been calculated and saved.
                </p>

                <div class="mt-5 text-5xl font-black text-cyan-500">
                    <span id="vocabFinalScore">0</span>
                    <span class="text-lg text-slate-400 font-bold">/ {{ $questions->count() * 5 }}</span>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <a href="#" id="vocabReviewLink" hidden
                        class="inline-flex items-center justify-center
                        px-6 py-3 rounded-2xl
                        border border-slate-200 dark:border-white/10
                        font-bold hover:bg-slate-100 dark:hover:bg-white/5 transition">
                        Lihat Pembahasan
                    </a>

                    <a href="{{ route('missions') }}"
                        class="inline-flex items-center justify-center
                        px-6 py-3 rounded-2xl
                        bg-gradient-to-r from-cyan-500 to-blue-600
                        text-white font-bold">
                        Back to Missions
                    </a>
                </div>
            </section>

            <div id="vocabWorkspace" class="space-y-5">

                @foreach ($questions as $index => $question)
                    <section
                        data-question-id="{{ $question->id }}"
                        class="vocab-question-card rounded-[28px] border border-slate-200 dark:border-white/10
                        bg-white/70 dark:bg-white/5 backdrop-blur-xl
                        p-6 md:p-7">

                        <div class="flex items-start gap-3">
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center
                                rounded-xl bg-cyan-500/10 text-cyan-500
                                font-black text-sm">
                                {{ $index + 1 }}
                            </span>

                            <h2 class="text-lg font-bold leading-snug">
                                {{ $question->question }}
                            </h2>
                        </div>

                        <div class="mt-4 grid gap-2 pl-12" data-options>
                            @foreach (['A', 'B', 'C', 'D', 'E'] as $option)
                                @php
                                    $field = 'option_' . strtolower($option);
                                @endphp

                                <label
                                    class="flex items-center gap-3 rounded-2xl border
                                    border-slate-200 dark:border-white/10
                                    px-4 py-3 cursor-pointer
                                    hover:border-cyan-400 hover:bg-cyan-500/5
                                    has-[:checked]:border-cyan-500
                                    has-[:checked]:bg-cyan-500/10
                                    transition">
                                    <input
                                        type="radio"
                                        name="answers[{{ $question->id }}]"
                                        value="{{ $option }}"
                                        class="vocab-option-input accent-cyan-500">

                                    <span class="text-sm font-medium">
                                        {{ $question->$field }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <p class="mt-2 pl-12 text-xs text-red-500" data-answer-error hidden>
                            Please select one answer before checking.
                        </p>

                        {{-- MODUL 5 — In-Quiz Tutor UX --}}
                        <div class="mt-4 pl-12 flex flex-wrap items-center gap-3">
                            <button
                                type="button"
                                data-check-btn
                                class="inline-flex items-center justify-center
                                px-5 py-2.5 rounded-xl
                                bg-gradient-to-r from-cyan-500 to-blue-600
                                text-white text-sm font-bold
                                hover:scale-[1.02] transition-all duration-200">
                                Check
                            </button>

                            <button
                                type="button"
                                data-hint-btn
                                class="inline-flex items-center gap-1.5 justify-center
                                px-5 py-2.5 rounded-xl
                                border border-slate-200 dark:border-white/10
                                text-cyan-600 dark:text-cyan-400 text-sm font-bold
                                hover:bg-cyan-500/5 transition">
                                💡 I need a hint
                            </button>

                            <span class="text-xs font-bold text-slate-400" data-checks-remaining hidden></span>
                        </div>

                        <div class="mt-3 pl-12 rounded-2xl px-4 py-3 text-sm font-bold" data-feedback hidden></div>

                        <div
                            class="mt-3 ml-12 rounded-2xl border border-cyan-200 dark:border-cyan-500/20
                            bg-cyan-50 dark:bg-cyan-500/10 p-4"
                            data-hint-panel
                            hidden>
                            <p class="text-xs font-bold text-cyan-700 dark:text-cyan-400" data-hint-error-label></p>
                            <div class="mt-2 space-y-2" data-hint-history></div>
                            <div class="mt-3 space-y-2" data-hint-socratic hidden>
                                <textarea
                                    data-socratic-input
                                    rows="2"
                                    placeholder="Tulis jawaban singkatmu di sini..."
                                    class="w-full rounded-xl border border-slate-200 dark:border-white/10
                                    bg-white dark:bg-white/5 px-3 py-2 text-sm"></textarea>
                                <button
                                    type="button"
                                    data-socratic-submit
                                    class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/10
                                    text-sm font-bold hover:bg-slate-100 dark:hover:bg-white/5 transition">
                                    Kirim Jawaban
                                </button>
                            </div>
                        </div>
                    </section>
                @endforeach

                <div class="flex justify-end pt-2">
                    <button
                        type="button"
                        id="vocabFinishBtn"
                        disabled
                        class="inline-flex items-center justify-center
                        px-8 py-4 rounded-2xl
                        bg-gradient-to-r from-emerald-500 to-cyan-600
                        text-white font-bold shadow-lg shadow-emerald-500/20
                        hover:scale-[1.02] transition-all duration-200
                        disabled:opacity-40 disabled:hover:scale-100">
                        <span id="vocabFinishLabel">Finish Pretest</span>
                    </button>
                </div>
            </div>
        @endif

    </div>

    @if ($questions->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const maxChecksPerQuestion = {{ (int) config('learning.scoring.max_checks_per_question', 3) }};
                const questionIds = @json($questions->pluck('id')->values());

                const checkUrl = @json(route('vocabulary.pretest.check'));
                const hintUrl = @json(route('vocabulary.pretest.hint'));
                const finishUrl = @json(route('vocabulary.pretest.finish'));
                const shownUrl = @json(route('vocabulary.pretest.shown'));
                const abandonUrl = @json(route('vocabulary.pretest.abandon'));

                // Modul 1 — Interaction Logger: sesi kuis ini (null
                // kalau logging gagal dibuka server-side).
                const quizSessionId = @json($quizSessionId);
                const csrfToken = @json(csrf_token());

                const cards = Array.from(document.querySelectorAll('.vocab-question-card'));
                const workspace = document.getElementById('vocabWorkspace');
                const resultSection = document.getElementById('vocabResultSection');
                const finalScore = document.getElementById('vocabFinalScore');
                const finalMessage = document.getElementById('vocabFinalMessage');
                const finishBtn = document.getElementById('vocabFinishBtn');
                const finishLabel = document.getElementById('vocabFinishLabel');

                // Semua soal tampil sekaligus di halaman ini (beda
                // dari Reading yang satu per satu), jadi "waktu soal
                // pertama tampil" = saat halaman selesai dimuat, sama
                // untuk semua soal.
                const pageLoadedAt = Date.now();
                let quizFinished = false;

                // Saran perbaikan Modul 1: beri tahu server bahwa SEMUA
                // soal sudah tampil sekarang (karena tidak ada navigasi
                // satu-per-satu seperti Reading), supaya response_ms
                // nanti dihitung dari jam SERVER. Fire-and-forget per
                // soal — kalau ada yang gagal, check() tetap jalan
                // dengan fallback ke waktu klien.
                if (quizSessionId) {
                    questionIds.forEach(function (questionId) {
                        fetch(shownUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                quiz_session_id: quizSessionId,
                                question_id: Number(questionId)
                            }),
                            keepalive: true
                        }).catch(function (error) {
                            console.warn('markShown gagal (tidak fatal):', error);
                        });
                    });
                }

                // Saran perbaikan Modul 1: catat 'abandon' kalau siswa
                // menutup tab / pindah halaman SEBELUM Finish.
                window.addEventListener('pagehide', function () {
                    if (quizFinished || !quizSessionId) {
                        return;
                    }
                    const data = new URLSearchParams();
                    data.append('_token', csrfToken);
                    data.append('quiz_session_id', quizSessionId);
                    navigator.sendBeacon(abandonUrl, data);
                });

                const state = {};
                cards.forEach(function (card) {
                    state[card.dataset.questionId] = {
                        locked: false,
                        isCorrect: null,
                        checksUsed: 0,
                        checksRemaining: maxChecksPerQuestion,
                        socraticStep: 0
                    };
                });

                let isChecking = false;
                let isRequestingHint = false;
                let isFinishing = false;

                function getSelectedAnswer(card) {
                    return card.querySelector('.vocab-option-input:checked');
                }

                function setOptionsDisabled(card, disabled) {
                    card.querySelectorAll('.vocab-option-input').forEach(function (input) {
                        input.disabled = disabled;
                    });
                }

                function clearError(card) {
                    const error = card.querySelector('[data-answer-error]');
                    if (error) {
                        error.hidden = true;
                    }
                }

                function showError(card) {
                    const error = card.querySelector('[data-answer-error]');
                    if (error) {
                        error.hidden = false;
                    }
                }

                function updateFinishAvailability() {
                    const allLocked = questionIds.every(function (id) {
                        return state[id] && state[id].locked;
                    });
                    finishBtn.disabled = !allLocked;
                }

                function renderCardState(card) {
                    const questionId = card.dataset.questionId;
                    const qState = state[questionId];

                    const checkBtn = card.querySelector('[data-check-btn]');
                    const hintBtn = card.querySelector('[data-hint-btn]');
                    const checksRemainingEl = card.querySelector('[data-checks-remaining]');
                    const feedbackEl = card.querySelector('[data-feedback]');

                    setOptionsDisabled(card, qState.locked);

                    if (qState.checksUsed === 0) {
                        checkBtn.hidden = false;
                        checkBtn.disabled = false;
                        checkBtn.textContent = 'Check';
                        checksRemainingEl.hidden = true;
                        feedbackEl.hidden = true;
                        hintBtn.hidden = false;
                        return;
                    }

                    checksRemainingEl.hidden = false;
                    checksRemainingEl.textContent = qState.checksRemaining > 0
                        ? qState.checksRemaining + ' check' + (qState.checksRemaining === 1 ? '' : 's') + ' left'
                        : 'No checks left';

                    feedbackEl.hidden = false;

                    if (qState.isCorrect) {
                        feedbackEl.className = 'mt-3 pl-12 rounded-2xl px-4 py-3 text-sm font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
                        feedbackEl.textContent = 'Correct! This question is locked in.';
                        checkBtn.hidden = true;
                        hintBtn.hidden = true;
                    } else if (qState.locked) {
                        feedbackEl.className = 'mt-3 pl-12 rounded-2xl px-4 py-3 text-sm font-bold bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400';
                        feedbackEl.textContent = "You've used all your checks for this question. You can still read hints to understand it.";
                        checkBtn.hidden = true;
                        hintBtn.hidden = false;
                    } else {
                        feedbackEl.className = 'mt-3 pl-12 rounded-2xl px-4 py-3 text-sm font-bold bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400';
                        feedbackEl.textContent = 'Not quite — try again, or ask for a hint.';
                        checkBtn.hidden = false;
                        checkBtn.disabled = false;
                        checkBtn.textContent = 'Check Again';
                        hintBtn.hidden = false;
                    }

                    updateFinishAvailability();
                }

                async function performCheck(card) {
                    if (isChecking) {
                        return;
                    }

                    const questionId = card.dataset.questionId;
                    const selected = getSelectedAnswer(card);

                    if (!selected) {
                        showError(card);
                        return;
                    }
                    clearError(card);

                    const checkBtn = card.querySelector('[data-check-btn]');
                    isChecking = true;
                    checkBtn.disabled = true;
                    checkBtn.textContent = 'Checking...';

                    try {
                        const response = await fetch(checkUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                quiz_session_id: quizSessionId,
                                question_id: Number(questionId),
                                selected_answer: selected.value,
                                response_ms: Date.now() - pageLoadedAt
                            })
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            throw new Error(result.message || 'Could not check this answer.');
                        }

                        state[questionId] = {
                            locked: Boolean(result.locked),
                            isCorrect: Boolean(result.is_correct),
                            checksUsed: result.checks_used,
                            checksRemaining: result.checks_remaining,
                            socraticStep: state[questionId].socraticStep
                        };

                        renderCardState(card);
                    } catch (error) {
                        console.error('Vocabulary check error:', error);
                        alert(error.message || 'Could not check this answer. Please try again.');
                        const qState = state[questionId];
                        checkBtn.disabled = false;
                        checkBtn.textContent = qState.checksUsed > 0 ? 'Check Again' : 'Check';
                    } finally {
                        isChecking = false;
                    }
                }

                function appendHintHistoryItem(card, levelLabel, text) {
                    const history = card.querySelector('[data-hint-history]');
                    const item = document.createElement('div');
                    item.className = 'rounded-xl bg-white dark:bg-white/5 border border-cyan-200 dark:border-cyan-500/20 px-3 py-2 text-sm';

                    const badge = document.createElement('span');
                    badge.className = 'inline-block mb-1 px-2 py-0.5 rounded-full bg-cyan-500 text-white text-[10px] font-black';
                    badge.textContent = levelLabel;

                    const body = document.createElement('div');
                    body.className = 'text-slate-700 dark:text-slate-300';
                    body.textContent = text;

                    item.appendChild(badge);
                    item.appendChild(body);
                    history.appendChild(item);
                }

                async function requestHint(card, socraticAnswer, socraticStep) {
                    if (isRequestingHint) {
                        return;
                    }

                    const questionId = card.dataset.questionId;
                    const hintBtn = card.querySelector('[data-hint-btn]');
                    const hintPanel = card.querySelector('[data-hint-panel]');
                    const hintErrorLabel = card.querySelector('[data-hint-error-label]');
                    const hintSocratic = card.querySelector('[data-hint-socratic]');
                    const socraticInput = card.querySelector('[data-socratic-input]');
                    const socraticSubmitBtn = card.querySelector('[data-socratic-submit]');

                    isRequestingHint = true;
                    hintBtn.disabled = true;
                    socraticSubmitBtn.disabled = true;

                    try {
                        const payload = {
                            quiz_session_id: quizSessionId,
                            question_id: Number(questionId)
                        };
                        if (socraticAnswer !== undefined) {
                            payload.socratic_answer = socraticAnswer;
                            payload.socratic_step = socraticStep;
                        }

                        const response = await fetch(hintUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            throw new Error(result.message || 'Could not load a hint right now.');
                        }

                        hintPanel.hidden = false;
                        hintErrorLabel.textContent = result.error_hint_label || '';
                        hintErrorLabel.hidden = !result.error_hint_label;

                        if (result.socratic_complete === true) {
                            appendHintHistoryItem(
                                card,
                                'L3',
                                "You've worked through all the guiding questions. Try selecting your answer again!"
                            );
                            hintSocratic.hidden = true;
                        } else if (result.socratic_question) {
                            appendHintHistoryItem(card, 'L3 · Q' + (result.socratic_step + 1), result.socratic_question);
                            state[questionId].socraticStep = result.socratic_step;
                            hintSocratic.hidden = false;
                            socraticInput.value = '';
                            socraticInput.focus();
                        } else if (result.hint_text) {
                            appendHintHistoryItem(card, 'L' + result.level, result.hint_text);
                            hintSocratic.hidden = true;
                        }
                    } catch (error) {
                        console.error('Vocabulary hint error:', error);
                        alert(error.message || 'Could not load a hint right now. Please try again.');
                    } finally {
                        isRequestingHint = false;
                        hintBtn.disabled = false;
                        socraticSubmitBtn.disabled = false;
                    }
                }

                cards.forEach(function (card) {
                    card.querySelectorAll('.vocab-option-input').forEach(function (input) {
                        input.addEventListener('change', function () {
                            clearError(card);
                        });
                    });

                    card.querySelector('[data-check-btn]').addEventListener('click', function () {
                        performCheck(card);
                    });

                    card.querySelector('[data-hint-btn]').addEventListener('click', function () {
                        requestHint(card);
                    });

                    card.querySelector('[data-socratic-submit]').addEventListener('click', function () {
                        const socraticInput = card.querySelector('[data-socratic-input]');
                        const answer = socraticInput.value.trim();
                        if (answer === '') {
                            socraticInput.focus();
                            return;
                        }
                        const questionId = card.dataset.questionId;
                        requestHint(card, answer, state[questionId].socraticStep);
                    });
                });

                finishBtn.addEventListener('click', async function () {
                    if (isFinishing || finishBtn.disabled) {
                        return;
                    }

                    isFinishing = true;
                    finishBtn.disabled = true;
                    finishLabel.textContent = 'Saving result...';

                    try {
                        const response = await fetch(finishUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                quiz_session_id: quizSessionId,
                                question_ids: questionIds
                            })
                        });

                        const result = await response.json();

                        if (!response.ok || !result.success) {
                            throw new Error(result.message || 'The result could not be saved.');
                        }

                        finalScore.textContent = result.score ?? 0;
                        finalMessage.textContent = result.message || 'Your result has been calculated and saved.';

                        // Saran perbaikan Modul 1: pretest selesai sah —
                        // pagehide setelah ini bukan 'abandon' lagi.
                        quizFinished = true;

                        // Saran perbaikan Modul 5 — Halaman Pembahasan.
                        const reviewLink = document.getElementById('vocabReviewLink');
                        if (result.review_url) {
                            reviewLink.href = result.review_url;
                            reviewLink.hidden = false;
                        }

                        workspace.hidden = true;
                        resultSection.hidden = false;

                        resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    } catch (error) {
                        console.error('Vocabulary finish error:', error);
                        alert(error.message || 'The result could not be saved. Please try again.');
                        isFinishing = false;
                        finishBtn.disabled = false;
                        finishLabel.textContent = 'Finish Pretest';
                    }
                });
            });
        </script>
    @endif

</x-app-layout>
