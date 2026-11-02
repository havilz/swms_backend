<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new regular user (forced to 'user' role).
     */
    public function registerUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => 'user',
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Attempt API login and return user with access token.
     *
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function attemptApiLogin(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
            ]);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Revoke current API token for the user.
     */
    public function revokeCurrentApiToken(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Attempt web login for administrator.
     *
     * @throws ValidationException
     */
    public function attemptAdminLogin(string $email, string $password, bool $remember = false): bool
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdmin()) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Akses ditolak. Akun Anda bukan Administrator.'],
            ]);
        }

        request()->session()->regenerate();

        return true;
    }

    /**
     * Logout web administrator.
     */
    public function logoutAdmin(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
