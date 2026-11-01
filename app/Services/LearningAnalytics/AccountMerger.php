<?php

namespace App\Services\LearningAnalytics;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menggabungkan akun siswa ganda: riwayat belajar akun lama ($source) dipindahkan ke akun utama ($target).
 *
 * Aman dan tidak merusak:
 *  - tidak ada baris yang dihapus; akun lama tetap ada tetapi dikecualikan dari analisis ("digabung ke #id");
 *  - baris yang bentrok dengan kunci unik (mis. progres pelajaran yang sama di kedua akun) TIDAK dipindahkan: tetap di akun lama;
 *  - pratinjau menjalankan penggabungan sungguhan di dalam transaksi lalu membatalkannya, sehingga hitungannya persis;
 *  - id baris yang dipindahkan disimpan (la_account_merges) sehingga dapat dibatalkan; tabel tanpa kolom id hanya dihitung.
 * Tabel turunan la_* tidak disentuh; hitung ulang dengan la:aggregate / la:fit-models / la:score-risk.
 * Saldo XP/koin/lencana pada tabel users tidak digabung.
 */
final class AccountMerger
{
    private const SKIP = ['users', 'sessions', 'personal_access_tokens', 'password_reset_tokens', 'teacher_class_assignments'];

    /** @return string[] tabel sumber yang punya kolom user_id */
    public function tables(): array
    {
        $out = [];
        foreach (Schema::getTables() as $t) {
            $name = $t['name'];
            if (in_array($name, self::SKIP, true) || str_starts_with($name, 'la_')) {
                continue;
            }
            if (Schema::hasColumn($name, 'user_id')) {
                $out[] = $name;
            }
        }
        sort($out);

        return $out;
    }

    /** @return string|null pesan galat bila pasangan tidak boleh digabung */
    public function validate(int $source, int $target): ?string
    {
        if ($source === $target) {
            return 'Akun asal dan tujuan sama.';
        }
        $u = User::whereIn('id', [$source, $target])->get()->keyBy('id');
        if ($u->count() !== 2) {
            return 'Akun tidak ditemukan.';
        }
        foreach ($u as $x) {
            if (in_array($x->role, ['admin', 'teacher'], true)) {
                return 'Hanya akun siswa yang dapat digabung.';
            }
        }
        if (str_starts_with((string) ($u[$source]->exclude_reason ?? ''), 'digabung ke #')) {
            return 'Akun asal sudah pernah digabung. Batalkan dulu penggabungan sebelumnya bila perlu.';
        }

        return null;
    }

    /** @return array{counts:array<string,array{rows:int,moved:int,kept:int}>,moved:array<string,int[]>,irreversible:string[]} */
    public function run(int $source, int $target, bool $commit): array
    {
        $result = ['counts' => [], 'moved' => [], 'irreversible' => []];
        DB::beginTransaction();
        try {
            foreach ($this->tables() as $t) {
                $hasId = Schema::hasColumn($t, 'id');
                $before = (int) DB::table($t)->where('user_id', $source)->count();
                if ($before === 0) {
                    continue;
                }
                $ids = $hasId ? DB::table($t)->where('user_id', $source)->pluck('id')->all() : [];
                DB::update("UPDATE IGNORE `$t` SET `user_id` = ? WHERE `user_id` = ?", [$target, $source]);
                $kept = (int) DB::table($t)->where('user_id', $source)->count();
                $result['counts'][$t] = ['rows' => $before, 'moved' => $before - $kept, 'kept' => $kept];
                if ($hasId) {
                    $result['moved'][$t] = $ids ? DB::table($t)->whereIn('id', $ids)->where('user_id', $target)->pluck('id')->map(fn ($v) => (int) $v)->all() : [];
                } elseif ($before - $kept > 0) {
                    $result['irreversible'][] = $t;
                }
            }
            if ($commit) {
                User::where('id', $source)->update(['exclude_from_analytics' => true, 'exclude_reason' => mb_substr('digabung ke #' . $target, 0, 120)]);
                $result['merge_id'] = DB::table('la_account_merges')->insertGetId([
                    'source_id' => $source, 'target_id' => $target, 'moved' => json_encode($result['moved']),
                    'counts' => json_encode($result['counts']), 'actor_id' => auth()->id(), 'created_at' => now(),
                ]);
                DB::commit();
            } else {
                DB::rollBack();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $result;
    }

    /** @return array{restored:int,irreversible:string[]} */
    public function undo(int $mergeId): array
    {
        $m = DB::table('la_account_merges')->where('id', $mergeId)->whereNull('undone_at')->first();
        abort_unless($m, 404);
        $moved = json_decode($m->moved, true) ?: [];
        $counts = json_decode($m->counts, true) ?: [];
        $restored = 0;
        DB::transaction(function () use ($m, $moved, &$restored) {
            foreach ($moved as $t => $ids) {
                foreach (array_chunk($ids, 500) as $chunk) {
                    $restored += DB::update("UPDATE IGNORE `$t` SET `user_id` = ? WHERE `user_id` = ? AND `id` IN (" . implode(',', array_fill(0, count($chunk), '?')) . ')', array_merge([$m->source_id, $m->target_id], $chunk));
                }
            }
            User::where('id', $m->source_id)->where('exclude_reason', 'like', 'digabung ke #%')->update(['exclude_from_analytics' => false, 'exclude_reason' => null]);
            DB::table('la_account_merges')->where('id', $m->id)->update(['undone_at' => now()]);
        });
        $irrev = array_values(array_filter(array_keys($counts), fn ($t) => ! isset($moved[$t]) && ($counts[$t]['moved'] ?? 0) > 0));

        return ['restored' => $restored, 'irreversible' => $irrev];
    }
}
