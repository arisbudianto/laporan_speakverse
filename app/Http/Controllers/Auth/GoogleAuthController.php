<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use RuntimeException;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect pengguna ke halaman autentikasi Google.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani callback dari Google.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Ambil data pengguna dari Google
            |--------------------------------------------------------------------------
            */

            $googleUser = Socialite::driver('google')->user();

            $googleId = trim((string) $googleUser->getId());
            $email = strtolower(
                trim((string) $googleUser->getEmail())
            );

            /*
            |--------------------------------------------------------------------------
            | Validasi data utama
            |--------------------------------------------------------------------------
            */

            if ($googleId === '' || $email === '') {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'google' => 'Google tidak mengembalikan informasi akun yang lengkap.',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Pastikan email Google sudah terverifikasi
            |--------------------------------------------------------------------------
            |
            | Google Provider milik Laravel Socialite mengembalikan
            | "email_verified" pada raw user response.
            |
            */

            $emailVerified = (bool) data_get(
                $googleUser->user,
                'email_verified',
                false
            );

            if (! $emailVerified) {
                return redirect()
                    ->route('login')
                    ->withErrors([
                        'google' => 'Email pada akun Google belum terverifikasi.',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Nama pengguna
            |--------------------------------------------------------------------------
            */

            $name = trim(
                (string) $googleUser->getName()
            );

            /*
             * Jika Google tidak memberikan nama,
             * gunakan bagian email sebelum @.
             */
            if ($name === '') {
                $name = Str::before(
                    $email,
                    '@'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Cari atau buat akun SpeakVerse
            |--------------------------------------------------------------------------
            */

            $user = DB::transaction(
                function () use (
                    $googleId,
                    $email,
                    $name,
                    $googleUser
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | 1. Cari berdasarkan Google ID
                    |--------------------------------------------------------------------------
                    */

                    $user = User::query()
                        ->where(
                            'google_id',
                            $googleId
                        )
                        ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | 2. Jika Google ID belum ada, cari berdasarkan email
                    |--------------------------------------------------------------------------
                    |
                    | Ini memungkinkan user yang sebelumnya register menggunakan
                    | email/password untuk menggunakan Google dengan email yang sama.
                    |
                    */

                    if (! $user) {
                        $user = User::query()
                            ->whereRaw(
                                'LOWER(email) = ?',
                                [$email]
                            )
                            ->first();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 3. Account sudah ada
                    |--------------------------------------------------------------------------
                    */

                    if ($user) {

                        /*
                         * Jangan izinkan satu account SpeakVerse
                         * terhubung ke dua Google ID berbeda.
                         */
                        if (
                            $user->google_id !== null &&
                            $user->google_id !== $googleId
                        ) {
                            throw new RuntimeException(
                                'This email is already connected to another Google account.'
                            );
                        }

                        /*
                         * Hubungkan akun lama dengan Google.
                         *
                         * Password dan role tidak disentuh.
                         */
                        $user->forceFill([
                            'google_id' => $googleId,

                            'google_avatar' =>
                                $googleUser->getAvatar(),

                            'email_verified_at' =>
                                $user->email_verified_at ?? now(),
                        ])->save();

                        return $user;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 4. User benar-benar baru
                    |--------------------------------------------------------------------------
                    |
                    | Password tetap dibuat agar kita tidak perlu mengubah
                    | kolom password menjadi nullable.
                    |
                    | User tidak mengetahui random password ini.
                    |
                    */

                    $user = User::create([
                        'name' => $name,

                        'email' => $email,

                        'password' => Hash::make(
                            Str::random(64)
                        ),

                        'google_id' => $googleId,

                        'google_avatar' =>
                            $googleUser->getAvatar(),
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Google sudah memverifikasi email
                    |--------------------------------------------------------------------------
                    */

                    $user->forceFill([
                        'email_verified_at' => now(),
                    ])->save();

                    return $user;
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Login user
            |--------------------------------------------------------------------------
            */

            Auth::login($user);

            /*
             * Regenerate session untuk mencegah session fixation.
             */
            $request
                ->session()
                ->regenerate();

            /*
            |--------------------------------------------------------------------------
            | Redirect berdasarkan role
            |--------------------------------------------------------------------------
            */

            if ($user->isAdmin()) {
                return redirect()->intended(
                    route('admin.dashboard')
                );
            }

            if (method_exists($user, 'isTeacher') && $user->isTeacher()) {
                return redirect()->intended(
                    route('teacher.dashboard')
                );
            }

            return redirect()->intended(
                route('dashboard')
            );

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Simpan error ke Laravel log
            |--------------------------------------------------------------------------
            |
            | Karena production menggunakan LOG_LEVEL=error,
            | gunakan Log::error(), bukan Log::warning().
            |
            */

            Log::error(
                'Google authentication failed.',
                [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Jangan tampilkan exception asli kepada pengguna
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('login')
                ->withErrors([
                    'google' => 'Login dengan Google gagal. Silakan coba lagi.',
                ]);
        }
    }
}