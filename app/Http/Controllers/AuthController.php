<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Geçersiz kullanıcı adı veya şifre.',
            ]);
        }

        $request->session()->regenerate();

        if (! Auth::user()->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Hesabınız pasif durumda. Lütfen yöneticinizle iletişime geçin.',
            ]);
        }

        // Login audit log
        AuditLog::log(
            action: 'login',
            description: 'Kullanıcı giriş yaptı: ' . Auth::user()->email
        );

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        // Logout audit log (kullanıcı henüz çıkış yapmadan)
        if (Auth::check()) {
            AuditLog::log(
                action: 'logout',
                description: 'Kullanıcı çıkış yaptı: ' . Auth::user()->email
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
