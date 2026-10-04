<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Izinkan hanya user dengan role tertentu.
     * Contoh pemakaian: middleware('role:admin') atau middleware('role:admin,pemilik').
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Maaf, Anda tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}
