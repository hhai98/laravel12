<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roleCodes
     */
    public function handle(Request $request, Closure $next, string ...$roleCodes): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return $this->unauthorizedResponse('Authentication required.');
        }

        $user = $request->user();
        
        // Load role relationship if not already loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        // Check if user has a role
        if (!$user->role) {
            return $this->forbiddenResponse('User has no assigned role.');
        }

        // Check if user's role code matches any of the required roles
        if (!in_array($user->role->code, $roleCodes, true)) {
            return $this->forbiddenResponse(
                'Insufficient permissions. Required role(s): ' . implode(', ', $roleCodes)
            );
        }

        return $next($request);
    }

    /**
     * Return unauthorized response.
     */
    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'error' => 'Unauthorized'
        ], 401);
    }

    /**
     * Return forbidden response.
     */
    private function forbiddenResponse(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'error' => 'Forbidden'
        ], 403);
    }
}
