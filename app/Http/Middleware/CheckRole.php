<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check Role Middleware
 *
 * Verifies that authenticated user has required role(s)
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ], 401);
        }

        $userRole = $request->user()->role->slug;

        if (! in_array($userRole, $roles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to access this resource',
            ], 403);
        }

        return $next($request);
    }
}
