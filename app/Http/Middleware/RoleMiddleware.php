<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ApiResponseResources;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check() || (Auth::user()->role != $role)) {
            return new ApiResponseResources(false, 'Role Anda Tidak Sesuai, Anda Tidak Bisa Mengakses Routes Ini!', null, 403);
        }

        return $next($request);
    }
}
