<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Route yang tetap boleh diakses walau must_change_password masih
     * true, supaya user tidak terjebak dalam redirect loop dan tetap
     * bisa mengganti password atau logout.
     */
    private const ALLOWED_ROUTES = [
        'profile.edit',
        'profile.update',
        'password.update',
        'logout',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (
            $user
            && $user->must_change_password
            && ! $request->routeIs(...self::ALLOWED_ROUTES)
        ) {
            return redirect()
                ->route('profile.edit')
                ->with(
                    'error',
                    'Anda wajib mengganti password sebelum melanjutkan.'
                );
        }

        return $next($request);
    }
}
