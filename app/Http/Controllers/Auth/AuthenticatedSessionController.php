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

        $user = Auth::user();

        // Security Checks
        if (!$user->aktif) {
            Auth::guard('web')->logout();
            return redirect()->route('login')->with('account_inactive', true);
        }

        if ($user->terkunci_sampai && now()->lessThan($user->terkunci_sampai)) {
            Auth::guard('web')->logout();
            return redirect()->route('login')->withErrors(['email' => 'Akun anda terkunci hingga ' . $user->terkunci_sampai]);
        }

        $request->session()->regenerate();

        // Reset failures & Log Success
        $user->update([
            'terakhir_login' => now(),
            'percobaan_gagal' => 0,
            'terkunci_sampai' => null,
        ]);

        \DB::table('riwayat_login')->insert([
            'id_pengguna' => $user->id,
            'alamat_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'success',
            'created_at' => now(),
        ]);

        session()->flash('show_welcome_message', true);

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

        return redirect()->route('login')->with('status', 'Parantos kaluar');
    }
}
