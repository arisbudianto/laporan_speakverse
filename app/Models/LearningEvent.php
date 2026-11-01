<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modul 1 — Interaction Logger.
 *
 * Satu baris = satu aksi siswa dalam sesi kuis (start, select,
 * revise, hint_request, submit, abandon). Log ini append-only untuk
 * atribut DASAR-nya (user_id, event_type, selected_answer, dst — ini
 * tidak pernah berubah setelah insert).
 *
 * PENGECUALIAN (Modul 2 — Diagnostic Engine): kolom is_correct,
 * error_code, error_confidence, dan classifier boleh di-UPDATE
 * setelah insert, karena benar/salah baru diketahui saat kuis
 * dinilai (di akhir sesi) — bukan saat siswa memilih jawaban.
 * Satu-satunya yang boleh melakukan update ini adalah
 * InteractionLogger::enrichAttempt(), supaya tetap satu titik tulis
 * yang konsisten (lihat catatan di InteractionLogger).
 *
 * Ditulis HANYA lewat App\Services\Learning\InteractionLogger, tidak
 * langsung dari controller/request, supaya aturan penentuan
 * event_type (select vs revise) konsisten di satu tempat.
 */
class LearningEvent extends Model
{
    use HasFactory;

    /**
     * Log ini tidak pernah diedit setelah ditulis, KECUALI lewat
     * InteractionLogger::enrichAttempt() (lihat docblock class).
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'quiz_session_id',
        'lesson_id',
        'question_id',
        'skill',
        'event_type',
        'selected_answer',
        'is_correct',
        'hint_level_shown',
        'response_ms',
        'attempt_no',
        'payload',

        /*
        |--------------------------------------------------------------------------
        | Modul 2 — Diagnostic Engine
        |--------------------------------------------------------------------------
        */
        'error_code',
        'error_confidence',
        'classifier',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'hint_level_shown' => 'integer',
            'response_ms' => 'integer',
            'attempt_no' => 'integer',
            'payload' => 'array',
            'error_confidence' => 'float',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
