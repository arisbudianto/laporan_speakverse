<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modul 6 — Teacher HITL Override.
 *
 * Satu baris = satu kebijakan bantuan AI yang guru pasang, untuk satu
 * siswa (scope_type='student') atau satu kelas (scope_type='class').
 * Dibaca oleh App\Services\Learning\TeacherOverrideService sebelum
 * Modul 4 (AdaptationPolicy) memutuskan level bantuan, dan sebelum
 * Modul 5 (check()) menegakkan aturan wajib-hint-dulu.
 *
 * Saran perbaikan — riwayat kebijakan yang dihapus: memakai
 * SoftDeletes, jadi query NORMAL (termasuk yang dipakai
 * TeacherOverrideService) OTOMATIS tidak melihat kebijakan yang
 * sudah dihapus, tanpa perlu perubahan apa pun di service itu.
 * Untuk melihat riwayatnya, pakai TeacherScaffoldingPolicy::onlyTrashed().
 */
class TeacherScaffoldingPolicy extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'scope_type',
        'student_id',
        'school',
        'major',
        'grade',
        'parallel',
        'freeze_level',
        'max_level',
        'min_level',
        'require_hint_before_recheck',
        'disable_llm',
        'note',
        'expires_at',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'freeze_level' => 'integer',
            'max_level' => 'integer',
            'min_level' => 'integer',
            'require_hint_before_recheck' => 'boolean',
            'disable_llm' => 'boolean',
            'expires_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Kebijakan yang belum kedaluwarsa (atau tidak punya tanggal
     * kedaluwarsa sama sekali).
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
