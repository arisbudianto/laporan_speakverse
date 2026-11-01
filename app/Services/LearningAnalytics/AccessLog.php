<?php

namespace App\Services\LearningAnalytics;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Mencatat akses ke data tingkat-siswa di fitur Learning Analytics (akuntabilitas, UU PDP). Gagal diam-diam bila tabel belum ada. */
final class AccessLog
{
    /** Catat paling banyak sekali per jam untuk kombinasi (pengguna, aksi, target): kurangi kebisingan log pada tampilan halaman. */
    public static function recordHourly(string $action, ?string $target = null, array $meta = []): void
    {
        try {
            if (! \Illuminate\Support\Facades\Cache::add('la:log:' . auth()->id() . ':' . $action . ':' . $target, 1, 3600)) {
                return;
            }
        } catch (\Throwable $e) {
            // cache tak tersedia: tetap catat
        }
        self::record($action, $target, $meta);
    }

    public static function record(string $action, ?string $target = null, array $meta = []): void
    {
        try {
            if (Schema::hasTable('la_access_log')) {
                DB::table('la_access_log')->insert(['actor_id' => auth()->id(), 'action' => $action, 'target' => $target, 'meta' => $meta ? json_encode($meta) : null, 'created_at' => now()]);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
