<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // cek apakah user login & admin
        if (Auth::check() && Auth::user()->role === 'admin') {

            return $next($request);

        }

        // jika bukan admin
        return redirect('/dashboard');
    }
}