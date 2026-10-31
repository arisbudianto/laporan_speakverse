<?php

namespace App\Http\Controllers;

use App\Models\GamificationTransaction;
use App\Models\RewardItem;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GamificationController extends Controller
{
    /**
     * Menampilkan halaman utama gamifikasi:
     *
     * - XP
     * - Level
     * - SpeakCoins
     * - Badge Collection
     * - Reward Shop
     * - Recent Rewards
     */
    public function index(
        GamificationService $gamificationService
    ): View {
        /**
         * User yang sedang login.
         *
         * @var User $user
         */
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Sinkronisasi progress lama
        |--------------------------------------------------------------------------
        |
        | User yang sudah pernah menyelesaikan lesson sebelum sistem
        | gamifikasi dibuat tetap akan memperoleh XP, Coins, dan Badge.
        |
        | Aman dijalankan berkali-kali karena GamificationService
        | menggunakan event_key unik.
        |
        */

        $gamificationService
            ->syncHistoricalProgress(
                $user
            );

        $user->refresh();

        /*
        |--------------------------------------------------------------------------
        | Data overview
        |--------------------------------------------------------------------------
        */

        $gamification =
            $gamificationService
                ->overview(
                    $user
                );

        /*
        |--------------------------------------------------------------------------
        | Reward Shop
        |--------------------------------------------------------------------------
        */

        $rewardItems =
            RewardItem::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy(
                    'sort_order'
                )
                ->orderBy('id')
                ->get();

        /*
        |--------------------------------------------------------------------------
        | Reward yang sudah dimiliki user
        |--------------------------------------------------------------------------
        */

        $ownedRewardItemIds =
            DB::table(
                'user_reward_items'
            )
                ->where(
                    'user_id',
                    $user->id
                )
                ->pluck(
                    'reward_item_id'
                )
                ->map(
                    fn ($id) =>
                        (int) $id
                );

        /*
        |--------------------------------------------------------------------------
        | Riwayat XP dan SpeakCoins terbaru
        |--------------------------------------------------------------------------
        */

        $recentTransactions =
            GamificationTransaction::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->latest(
                    'created_at'
                )
                ->latest('id')
                ->limit(12)
                ->get();

        return view(
            'gamification.index',
            compact(
                'user',
                'gamification',
                'rewardItems',
                'ownedRewardItemIds',
                'recentTransactions'
            )
        );
    }

    /**
     * Membeli reward menggunakan SpeakCoins.
     */
    public function purchase(
        RewardItem $rewardItem
    ): RedirectResponse {
        /**
         * @var User $user
         */
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Reward tidak aktif
        |--------------------------------------------------------------------------
        */

        if (!$rewardItem->is_active) {
            return back()->with(
                'error',
                'Reward tersebut sedang tidak tersedia.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        |
        | User di-lock agar Coin tidak bisa terpotong secara tidak konsisten
        | jika terdapat dua request pembelian dalam waktu bersamaan.
        |
        */

        $result = DB::transaction(
            function () use (
                $user,
                $rewardItem
            ) {
                $lockedUser =
                    User::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $user->id
                        );

                /*
                |--------------------------------------------------------------------------
                | Cek apakah sudah dimiliki
                |--------------------------------------------------------------------------
                */

                $alreadyOwned =
                    DB::table(
                        'user_reward_items'
                    )
                        ->where(
                            'user_id',
                            $lockedUser->id
                        )
                        ->where(
                            'reward_item_id',
                            $rewardItem->id
                        )
                        ->exists();

                if ($alreadyOwned) {
                    return [
                        'ok' =>
                            false,

                        'message' =>
                            'Reward ini sudah kamu miliki.',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Cek saldo SpeakCoins
                |--------------------------------------------------------------------------
                */

                if (
                    (int) $lockedUser->coins
                    <
                    (int) $rewardItem->price_coins
                ) {
                    return [
                        'ok' =>
                            false,

                        'message' =>
                            'SpeakCoins kamu belum cukup untuk membeli reward ini.',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Potong SpeakCoins
                |--------------------------------------------------------------------------
                */

                $lockedUser->coins =
                    (int) $lockedUser->coins
                    -
                    (int) $rewardItem
                        ->price_coins;

                $lockedUser->save();

                /*
                |--------------------------------------------------------------------------
                | Simpan reward ke inventory user
                |--------------------------------------------------------------------------
                */

                DB::table(
                    'user_reward_items'
                )->insert([
                    'user_id' =>
                        $lockedUser->id,

                    'reward_item_id' =>
                        $rewardItem->id,

                    'quantity' =>
                        1,

                    'acquired_at' =>
                        now(),

                    'created_at' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Catat transaksi pengeluaran SpeakCoins
                |--------------------------------------------------------------------------
                */

                GamificationTransaction::create([
                    'user_id' =>
                        $lockedUser->id,

                    'type' =>
                        'coin',

                    'amount' =>
                        -1
                        *
                        (int) $rewardItem
                            ->price_coins,

                    'reason' =>
                        'Reward shop purchase: '
                        .
                        $rewardItem->name,

                    /*
                     * Purchase sengaja menggunakan UUID
                     * karena pembelian adalah transaksi unik.
                     */
                    'event_key' =>
                        'purchase:'
                        .
                        $rewardItem->id
                        .
                        ':'
                        .
                        Str::uuid(),

                    'metadata' => [
                        'reward_item_id' =>
                            $rewardItem->id,

                        'reward_item_code' =>
                            $rewardItem->code,
                    ],
                ]);

                return [
                    'ok' =>
                        true,

                    'message' =>
                        $rewardItem->name
                        .
                        ' berhasil dibeli.',
                ];
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Redirect kembali
        |--------------------------------------------------------------------------
        */

        return back()->with(
            $result['ok']
                ? 'success'
                : 'error',

            $result['message']
        );
    }

    /**
     * Menampilkan leaderboard XP.
     */
    public function leaderboard(
        GamificationService $gamificationService
    ): View {
        /**
         * @var User $user
         */
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Sinkronisasi semua siswa
        |--------------------------------------------------------------------------
        |
        | Agar akun yang sudah belajar sebelum gamifikasi dibuat
        | tetap memperoleh ranking XP yang benar.
        |
        */

        $user->refresh();

        $scope = request('scope', ($user->grade && $user->parallel) ? 'class' : 'school');
        $sameClass = $scope === 'class';

        /*
        |--------------------------------------------------------------------------
        | Ambil leaderboard
        |--------------------------------------------------------------------------
        |
        | Admin tidak dimasukkan.
        |
        */

        $leaders = $gamificationService
            ->studentQuery($user, $sameClass)
            ->orderByDesc('xp')
            ->orderBy('id')
            ->limit(100)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Ranking user saat ini
        |--------------------------------------------------------------------------
        |
        | Jika XP sama, ID lebih kecil memperoleh posisi lebih tinggi.
        |
        */

        $currentRank = $gamificationService->rankAmong($user, $sameClass);

        return view(
            'gamification.leaderboard',
            compact(
                'leaders',
                'user',
                'currentRank',
                'scope'
            )
        );
    }
}