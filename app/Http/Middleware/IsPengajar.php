<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsPengajar
{
    public function handle(Request $request, Closure $next)
    {
        // Jika pengguna bukan pengajar, return response 403
        if (Auth::user() && Auth::user()->role !== 'pengajar') {
            return response()->json(['message' => 'Access denied. Only pengajar can access this resource.'], 403);
        }

        return $next($request);
    }
}
