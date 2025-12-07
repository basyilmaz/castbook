<?php

namespace App\Http\Middleware;

use App\Models\AuthToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TokenAuthentication
{
    /**
     * Token-based authentication middleware
     * Token URL query, POST body veya session'dan alınır
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Mevcut IP adresi
        $currentIp = $request->ip();

        // Zaten authenticated ise sadece güvenlik header'larını ekle
        if (Auth::check()) {
            return $this->addSecurityHeaders($next($request));
        }

        // Token'ı al: URL query, POST body veya session'dan
        $token = $request->query('_auth')  // URL'den (GET)
              ?? $request->input('_auth')   // Form body'den (POST)
              ?? session('auth_token');     // Session'dan
        
        if ($token) {
            // IP kontrolü ile token validation
            $authToken = AuthToken::findValidToken($token, $currentIp);

            if ($authToken) {
                // Kullanıcıyı authenticate et
                Auth::login($authToken->user);

                // Token'ı session'a kaydet
                session(['auth_token' => $token]);
            } else {
                // Geçersiz token - session'dan temizle
                session()->forget('auth_token');
            }
        }

        return $this->addSecurityHeaders($next($request));
    }

    /**
     * Güvenlik header'ları ekle
     */
    protected function addSecurityHeaders(Response $response): Response
    {
        // Token'ın dış sitelere sızmasını engelle
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Authenticated sayfalar için cache'i devre dışı bırak
        if (Auth::check()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
