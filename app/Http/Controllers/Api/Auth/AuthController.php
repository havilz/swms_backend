<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    /**
     * Register a new user via API.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->registerUser($request->validated());
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Authenticate user via API and issue token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->attemptApiLogin(
            $request->input('email'),
            $request->input('password')
        );

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => $result,
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }

    /**
     * Logout and invalidate API token.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->revokeCurrentApiToken($request->user());

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
