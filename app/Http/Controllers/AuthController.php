<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isAdmin() || $user->isSuperAdmin()) {
                return $this->redirectBasedOnRole($user);
            }

            // If currently in a tenant user session, invalidate it so admin can access login form
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        $activeEvent = Event::getActive();
        return view('auth.login', compact('activeEvent'));
    }

    public function login(LoginRequest $request): JsonResponse|RedirectResponse
    {
        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $request->login,
            'password' => $request->password,
        ];

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email/Username atau kata sandi tidak cocok.',
                ], 422);
            }

            throw ValidationException::withMessages([
                'login' => ['Email/Username atau kata sandi yang Anda masukkan salah.'],
            ]);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // Prevent tenant users from logging in via login form
        // Tenants should access via UUID link only
        if ($user->role === 'user') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses tenant hanya melalui link yang diberikan oleh admin/EO.',
                ], 403);
            }

            throw ValidationException::withMessages([
                'login' => ['Akses tenant hanya melalui link yang diberikan oleh admin/EO.'],
            ]);
        }

        $redirectUrl = match ($user->role) {
            'superadmin' => route('superadmin.dashboard'),
            'admin' => route('admin.dashboard'),
            default => route('admin.dashboard'),
        };

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil. Mengalihkan...',
                'redirect' => $redirectUrl,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'store_id' => $user->store_id,
                ],
            ]);
        }

        return redirect()->intended($redirectUrl);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah keluar dari sistem.');
    }

    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            'superadmin' => redirect()->route('superadmin.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('user.kasir'),
        };
    }
}
