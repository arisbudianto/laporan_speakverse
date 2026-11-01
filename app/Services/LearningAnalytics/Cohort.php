<?php

namespace App\Services\LearningAnalytics;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Definisi kohort penelitian: siswa sungguhan, tanpa akun uji / yang dikecualikan admin.
 * Aman dijalankan sebelum migrasi tahap 2 (kolom belum ada → tidak ada yang dikecualikan).
 */
final class Cohort
{
    private static ?bool $has = null;

    public static function hasExclusion(): bool
    {
        return self::$has ??= Schema::hasColumn('users', 'exclude_from_analytics');
    }

    /**
     * Filter kohort global (sesi admin): sekolah dan/atau kelompok penelitian. Hanya berlaku pada permintaan web;
     * perintah artisan (agregasi, model, skor) tidak pernah disaring agar tabel la_* tetap lengkap.
     * @return array{school:?string,group:?string}
     */
    public static function scope(): array
    {
        $none = ['school' => null, 'group' => null];
        if (app()->runningInConsole() || ! app()->bound('session') || ! request()->hasSession()) {
            return $none;
        }
        $v = request()->session()->get('la_cohort', []);

        return ['school' => $v['school'] ?? null ?: null, 'group' => $v['group'] ?? null ?: null];
    }

    public static function setScope(?string $school, ?string $group): void
    {
        request()->session()->put('la_cohort', ['school' => $school ?: null, 'group' => in_array($group, ['kontrol', 'perlakuan'], true) ? $group : null]);
        self::$outside = null;
    }

    private static ?array $outside = null;

    public static function students(bool $scoped = true): Builder
    {
        $sc = $scoped ? self::scope() : ['school' => null, 'group' => null];
        $q = User::query()->where(function ($q) {
            $q->where('role', 'student')->orWhereNull('role')->orWhere('role', 'user');
        });
        if (self::hasExclusion()) {
            $q->where('exclude_from_analytics', false)->where('is_test', false);
        }
        if ($sc['school']) {
            $q->where('school', $sc['school']);
        }
        if ($sc['group'] && Schema::hasColumn('users', 'research_group')) {
            $q->where('research_group', $sc['group']);
        }

        return $q;
    }

    /** Saring query yang sudah join users dengan alias $alias. */
    public static function whereJoined($query, string $alias = 'u')
    {
        if (self::hasExclusion()) {
            $query->where($alias . '.exclude_from_analytics', false)->where($alias . '.is_test', false);
        }
        $sc = self::scope();
        if ($sc['school']) {
            $query->where($alias . '.school', $sc['school']);
        }
        if ($sc['group'] && Schema::hasColumn('users', 'research_group')) {
            $query->where($alias . '.research_group', $sc['group']);
        }

        return $query;
    }

    /** @return int[] id akun yang dikecualikan (untuk menyaring tabel yang hanya punya user_id) */
    public static function excludedIds(): array
    {
        $ids = self::hasExclusion() ? DB::table('users')->where(fn ($q) => $q->where('exclude_from_analytics', true)->orWhere('is_test', true))->pluck('id')->all() : [];
        $sc = self::scope();
        if ($sc['school'] || $sc['group']) {
            // siswa di luar sekolah/kelompok terpilih diperlakukan sebagai "dikecualikan" oleh layanan yang menyaring lewat id
            self::$outside ??= DB::table('users')->when($sc['school'], fn ($q) => $q->where(fn ($w) => $w->whereNull('school')->orWhere('school', '!=', $sc['school'])))
                ->when($sc['group'] && Schema::hasColumn('users', 'research_group'), fn ($q) => $q->where(fn ($w) => $w->whereNull('research_group')->orWhere('research_group', '!=', $sc['group'])))->pluck('id')->all();
            $ids = array_values(array_unique(array_merge($ids, self::$outside)));
        }

        return $ids;
    }

    /** Teks ringkas filter aktif untuk ditampilkan di setiap tab; null bila tidak ada. */
    public static function scopeLabel(): ?string
    {
        $sc = self::scope();
        if (! $sc['school'] && ! $sc['group']) {
            return null;
        }

        return implode(' · ', array_filter([$sc['school'], $sc['group'] ? 'kelompok ' . $sc['group'] : null]));
    }

    /** @return array<string,int> alasan → jumlah, untuk catatan "n dikecualikan" di halaman */
    public static function excludedSummary(): array
    {
        if (! self::hasExclusion()) {
            return [];
        }
        $rows = DB::table('users')->where(fn ($q) => $q->where('exclude_from_analytics', true)->orWhere('is_test', true))
            ->selectRaw("CASE WHEN is_test = 1 THEN 'akun uji' ELSE COALESCE(NULLIF(exclude_reason, ''), 'dikecualikan') END r, COUNT(*) c")->groupBy('r')->pluck('c', 'r')->all();

        return array_map('intval', $rows);
    }
}
