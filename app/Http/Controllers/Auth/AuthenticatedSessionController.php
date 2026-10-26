<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $userRole = auth()->user()->role;
        $intendedUrl = session()->get('url.intended');

        // Hapus intended URL jika akun yang login tidak berhak mengakses prefix URL tersebut
        if ($intendedUrl) {
            if (str_contains($intendedUrl, '/admin') && !in_array($userRole, ['admin', 'pimpinan'])) {
                session()->forget('url.intended');
            } elseif (str_contains($intendedUrl, '/atasan') && $userRole !== 'atasan') {
                session()->forget('url.intended');
            } elseif (str_contains($intendedUrl, '/pjlp') && $userRole !== 'pjlp') {
                session()->forget('url.intended');
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}