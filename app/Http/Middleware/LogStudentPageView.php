<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencatat kunjungan halaman siswa untuk Learning Analytics (keterlibatan).
 * Minimal: hanya nama rute dan waktu; tanpa URL, parameter, IP, atau isi. Maks. satu catatan per rute per 5 menit.
 * Dilewati bagi admin/guru, siswa yang menolak persetujuan penelitian, permintaan non-GET/AJAX, dan bila dimatikan (LA_PAGE_LOG=false).
 * Berjalan setelah respons dikirim (terminate) sehingga tidak menambah waktu muat halaman; semua galat diabaikan.
 */
class LogStudentPageView
{
    private const SKIP = ['admin.', 'staff.', 'progress.consent', 'logout', 'password.', 'verification.', 'livewire.', 'debugbar.', 'ignition.'];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            if (! config('learning_analytics.page_log.enabled', true) || ! $request->isMethod('GET') || $request->expectsJson() || $request->ajax() || $response->getStatusCode() >= 400) {
                return;
            }
            $user = $request->user();
            $route = $request->route()?->getName();
            if (! $user || ! $route || in_array($user->role, ['admin', 'teacher'], true)) {
                return;
            }
            foreach (self::SKIP as $p) {
                if (str_starts_with($route, $p)) {
                    return;
                }
            }
            if (! Cache::remember('la:pv:table', 600, fn () => Schema::hasTable('la_page_views'))) {
                return;
            }
            if (Schema::hasTable('la_consents') && Cache::remember('la:pv:optout:' . $user->id, 600, fn () => DB::table('la_consents')->where('user_id', $user->id)->where('accepted', false)->exists())) {
                return;
            }
            if (! Cache::add('la:pv:' . $user->id . ':' . $route, 1, 300)) {
                return;
            }
            DB::table('la_page_views')->insert(['user_id' => $user->id, 'route' => mb_substr($route, 0, 80), 'created_at' => now()]);
        } catch (\Throwable $e) {
            // pencatatan tidak boleh mengganggu pengguna
        }
    }
}
