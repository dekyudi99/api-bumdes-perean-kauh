<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ApiResponseResources;

class EmailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::user()->email_verified) {
            return new ApiResponseResources(false, "Email Anda Belum Terverifikasi!", Null, 403);
        }

        return $next($request);
    }
}
