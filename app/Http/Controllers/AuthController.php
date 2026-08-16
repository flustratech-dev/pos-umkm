<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Event;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
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

        $redirectUrl = match ($user->role) {
            'superadmin' => route('superadmin.dashboard'),
            'admin' => route('admin.dashboard'),
            default => route('user.kasir'),
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

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        $activeEvent = Event::getActive();
        return view('auth.register', compact('activeEvent'));
    }

    public function register(RegisterRequest $request): JsonResponse|RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $activeEvent = Event::getActive();
            if (!$activeEvent) {
                $activeEvent = Event::create([
                    'name' => 'Bazar Kuliner & UMKM Nusantara 2026',
                    'slug' => 'bazar-kuliner-umkm-2026',
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addDays(7)->toDateString(),
                    'location' => 'Parkir Timur Senayan, Jakarta',
                    'is_active' => true,
                ]);
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => 'user',
                'password' => Hash::make($request->password),
            ]);

            $store = Store::create([
                'event_id' => $activeEvent->id,
                'owner_id' => $user->id,
                'name' => $request->store_name,
                'booth_number' => $request->booth_number ?: 'Stand A-01',
                'category' => $request->category ?: 'Makanan & Minuman',
                'is_active' => true,
            ]);

            $user->update(['store_id' => $store->id]);

            Auth::login($user);
            $request->session()->regenerate();

            $redirectUrl = route('user.kasir');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi warung berhasil! Selamat berjualan.',
                    'redirect' => $redirectUrl,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'store_id' => $store->id,
                    ],
                ]);
            }

            return redirect()->route('user.kasir')->with('success', 'Registrasi warung berhasil! Selamat berjualan.');
        });
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
