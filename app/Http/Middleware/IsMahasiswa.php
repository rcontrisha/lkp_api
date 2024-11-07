<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsMahasiswa
{
    public function handle(Request $request, Closure $next)
    {
        // Jika pengguna bukan mahasiswa, return response 403
        if (Auth::user() && Auth::user()->role !== 'user') {
            return response()->json(['message' => 'Access denied. Only mahasiswa can access this resource.'], 403);
        }

        return $next($request);
    }
}
