<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\LogAktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly LogAktivitasService $logService
    ) {}

    public function tampilLogin(): View
    {
        return view('auth.login');
    }

    public function prosesLogin(LoginRequest $request): RedirectResponse
    {
        $throttleKey = Str::transliterate(Str::lower($request->input('username')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $detik = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$detik} detik."]);
        }

        $kredensial = [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'status' => 'aktif',
        ];

        if (! Auth::attempt($kredensial, false)) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Username atau kata sandi salah, atau akun sedang nonaktif.']);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $user = Auth::user()->load('role');

        $this->logService->catat(
            aksi: 'login_berhasil',
            entitas: 'users',
            entitasId: $user->id,
            detail: ['ip' => $request->ip()],
            userId: $user->id
        );

        // Arahkan kasir langsung ke halaman POS (/kasir), peran lain ke /dasbor
        if ($user->hasRole('kasir')) {
            return redirect()->intended(route('kasir.index'));
        }

        return redirect()->intended(route('dasbor'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        if ($userId) {
            $this->logService->catat(
                aksi: 'logout',
                entitas: 'users',
                entitasId: $userId,
                detail: [],
                userId: $userId
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
