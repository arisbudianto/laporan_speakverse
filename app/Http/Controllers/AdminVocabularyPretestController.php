<?php

namespace App\Http\Controllers;

use App\Models\VocabularyPretest;
use App\Services\Learning\DiagnosticEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVocabularyPretestController extends Controller
{
    public function index(): View
    {
        $pretests = VocabularyPretest::orderByDesc('id')->get();

        return view(
            'admin.missions.vocabulary-pretests.index',
            compact('pretests')
        );
    }

    public function create(): View
    {
        return view(
            'admin.missions.vocabulary-pretests.create'
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedPayload($request);

        VocabularyPretest::create($validated);

        return redirect()
            ->route('admin.vocabulary-pretests.index')
            ->with('success', 'Question added successfully.');
    }

    public function edit(VocabularyPretest $vocabularyPretest): View
    {
        return view(
            'admin.missions.vocabulary-pretests.edit',
            [
                'pretest' => $vocabularyPretest,
            ]
        );
    }

    public function update(Request $request, VocabularyPretest $vocabularyPretest): RedirectResponse
    {
        $validated = $this->validatedPayload($request);

        $vocabularyPretest->update($validated);

        return redirect()
            ->route('admin.vocabulary-pretests.index')
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(VocabularyPretest $vocabularyPretest): RedirectResponse
    {
        $vocabularyPretest->delete();

        return back()->with('success', 'Question deleted successfully.');
    }

    /**
     * Semua kolom di tabel vocabulary_pretests (kecuali category)
     * wajib diisi di level database (lihat migration), jadi
     * validasi di sini menyamakan aturan itu di level HTTP supaya
     * error-nya ramah (pesan form), bukan error SQL mentah.
     *
     * Modul 2 — Diagnostic Engine: form mengirim error_code_a s.d.
     * error_code_e terpisah (UX lebih mudah daripada minta admin
     * mengetik JSON manual) — di sini digabung jadi kolom
     * error_if_wrong (JSON map opsi -> kode error).
     */
    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string', 'max:255'],
            'option_b' => ['required', 'string', 'max:255'],
            'option_c' => ['required', 'string', 'max:255'],
            'option_d' => ['required', 'string', 'max:255'],
            'option_e' => ['required', 'string', 'max:255'],
            'correct_answer' => ['required', 'in:A,B,C,D,E'],

            'skill_target' => ['nullable', 'string', 'max:100'],
            'rationale' => ['nullable', 'string'],
            'text_span' => ['nullable', 'string'],

            'error_code_a' => ['nullable', 'in:' . implode(',', DiagnosticEngine::ERROR_CODES)],
            'error_code_b' => ['nullable', 'in:' . implode(',', DiagnosticEngine::ERROR_CODES)],
            'error_code_c' => ['nullable', 'in:' . implode(',', DiagnosticEngine::ERROR_CODES)],
            'error_code_d' => ['nullable', 'in:' . implode(',', DiagnosticEngine::ERROR_CODES)],
            'error_code_e' => ['nullable', 'in:' . implode(',', DiagnosticEngine::ERROR_CODES)],
        ]);

        $errorIfWrong = [];
        foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E'] as $key => $label) {
            $code = $validated['error_code_' . $key] ?? null;

            // Tidak masuk akal melabeli opsi yang justru jawaban
            // benar sebagai "error" — abaikan kalau itu terjadi
            // (mis. admin lupa mengosongkannya setelah ganti kunci
            // jawaban).
            if ($code && $label !== $validated['correct_answer']) {
                $errorIfWrong[$label] = $code;
            }

            unset($validated['error_code_' . $key]);
        }

        $validated['error_if_wrong'] = $errorIfWrong === [] ? null : $errorIfWrong;

        // Saran guru menyimpan lewat form ini = ditinjau manusia,
        // walau sebelumnya berasal dari usulan AI.
        $validated['error_labels_source'] = 'manual';

        return $validated;
    }
}
