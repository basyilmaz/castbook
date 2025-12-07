<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\AuthToken;
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

        if (! Auth::user()->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Hesabınız pasif durumda. Lütfen yöneticinizle iletişime geçin.',
            ]);
        }

        // Auth token oluştur (cookie-less authentication için)
        $authToken = AuthToken::createForUser(
            Auth::user(),
            $request->ip(),
            $request->userAgent()
        );

        // Token'ı session'a kaydet
        session(['auth_token' => $authToken->token]);

        // Login audit log
        AuditLog::log(
            action: 'login',
            description: 'Kullanıcı giriş yaptı: ' . Auth::user()->email
        );

        // Token ile redirect
        $redirectUrl = route('dashboard') . '?_token=' . $authToken->token;
        return redirect($redirectUrl);
    }

    public function logout(Request $request)
    {
        // Logout audit log (kullanıcı henüz çıkış yapmadan)
        if (Auth::check()) {
            AuditLog::log(
                action: 'logout',
                description: 'Kullanıcı çıkış yaptı: ' . Auth::user()->email
            );

            // Tüm auth token'ları sil
            AuthToken::revokeAllForUser(Auth::user()->id);
        }

        // Session'dan token'ı temizle
        session()->forget('auth_token');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
