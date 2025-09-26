<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle a login request to the application.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        // Find user by phone (since we use phone as username)
        $user = User::where('phone', $credentials['phone'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password ?? '')) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is active (you can add status field later)
        // if (!$user->is_active) {
        //     throw ValidationException::withMessages([
        //         'phone' => ['Account has been deactivated.'],
        //     ]);
        // }

        // Revoke all existing tokens for this user (optional - for single session)
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Load relationships
        $user->load(['role']);

        return response()->json([
            'message' => 'Login successful.',
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Handle a logout request to the application.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }

    /**
     * Handle a logout from all devices request.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        // Revoke all tokens for the authenticated user
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout from all devices successful.',
        ]);
    }

    /**
     * Get the authenticated user's information.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['role']);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Refresh the user's access token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully.',
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'password.required' => 'Please enter a new password.',
            'password.min' => 'The new password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $user = $request->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password ?? '')) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Optionally revoke all tokens to force re-login
        // $user->tokens()->delete();

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Get user's active sessions/tokens.
     */
    public function sessions(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokens = $user->tokens()->select(['id', 'name', 'last_used_at', 'created_at'])->get();

        return response()->json([
            'sessions' => $tokens->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'last_used_at' => $token->last_used_at?->toISOString(),
                    'created_at' => $token->created_at->toISOString(),
                    'is_current' => $token->id === request()->user()->currentAccessToken()->id,
                ];
            }),
        ]);
    }

    /**
     * Revoke a specific session/token.
     */
    public function revokeSession(Request $request): JsonResponse
    {
        $request->validate([
            'token_id' => ['required', 'integer', 'exists:personal_access_tokens,id'],
        ]);

        $user = $request->user();
        $tokenId = $request->token_id;

        // Make sure the token belongs to the authenticated user
        $token = $user->tokens()->where('id', $tokenId)->first();

        if (!$token) {
            return response()->json([
                'message' => 'Session not found.',
            ], 404);
        }

        // Don't allow revoking current token via this method
        if ($token->id === $request->user()->currentAccessToken()->id) {
            return response()->json([
                'message' => 'Cannot revoke current session.',
            ], 400);
        }

        $token->delete();

        return response()->json([
            'message' => 'Session revoked successfully.',
        ]);
    }
}
