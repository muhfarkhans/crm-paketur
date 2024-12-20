<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return response()->json([
                "status" => false,
                "errors" => 'Unauthenticated',
            ], 401);
        }

        $userRole = Auth::user()->role_id;

        if (!in_array($userRole, $roles)) {
            return response()->json([
                "status" => false,
                "errors" => 'Unauthorized',
            ], 403);
        }

        return $next($request);
    }
}
