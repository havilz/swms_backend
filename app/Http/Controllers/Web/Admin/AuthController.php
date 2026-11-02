<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    /**
     * Show admin login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle admin login request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $this->authService->attemptAdminLogin(
            $request->input('email'),
            $request->input('password'),
            $request->boolean('remember')
        );

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'Selamat datang kembali, Administrator.');
    }

    /**
     * Handle admin logout request.
     */
    public function logout(): RedirectResponse
    {
        $this->authService->logoutAdmin();

        return redirect()->route('admin.login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}
