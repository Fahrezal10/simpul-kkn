<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        $attempt = Auth::attempt(
            array_merge($credentials, ['status_aktif' => true]),
            $remember
        );

        if (! $attempt) {
            return back()
                ->withErrors(['email' => 'Email atau password salah, atau akun Anda non-aktif.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'login',
            'deskripsi' => 'User login ke sistem.',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'aksi' => 'logout',
            'deskripsi' => 'User logout dari sistem.',
            'ip_address' => $request->ip(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}