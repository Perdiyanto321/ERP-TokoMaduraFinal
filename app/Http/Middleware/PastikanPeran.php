<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PastikanPeran
{
    public function handle(Request $request, Closure $next, string ...$peranDiizinkan): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Pastikan relasi role ter-load agar tidak memicu preventLazyLoading
        $user->loadMissing('role');

        if (! $user->isAktif()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['username' => 'Akun Anda berstatus nonaktif. Hubungi Admin.']);
        }

        if (! empty($peranDiizinkan) && ! $user->hasRole($peranDiizinkan)) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman atau tindakan ini.');
        }

        return $next($request);
    }
}
