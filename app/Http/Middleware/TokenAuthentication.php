<?php

namespace App\Http\Middleware;

use App\Models\AuthToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TokenAuthentication
{
    /**
     * Token-based authentication middleware
     * Token URL query, POST body veya session'dan alınır
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Zaten authenticated ise devam et
        if (Auth::check()) {
            return $this->addSecurityHeaders($next($request));
        }

        // Token'ı al: URL query, POST body veya session'dan
        $token = $request->query('_auth')   // URL'den (GET)
              ?? $request->input('_auth')    // Form body'den (POST)
              ?? session('auth_token');      // Session'dan
        
        // Debug log
        Log::info('TokenAuthentication', [
            'has_token' => !empty($token),
            'token_source' => $request->query('_auth') ? 'query' : ($request->input('_auth') ? 'input' : (session('auth_token') ? 'session' : 'none')),
            'url' => $request->url(),
            'method' => $request->method(),
        ]);

        if ($token) {
            // Token validation - IP kontrolü devre dışı (Railway proxy sorunu)
            $authToken = AuthToken::findValidToken($token, null);

            if ($authToken) {
                // Kullanıcıyı authenticate et
                Auth::login($authToken->user);

                // Token'ı session'a kaydet
                session(['auth_token' => $token]);
                
                Log::info('TokenAuthentication: User authenticated', ['user_id' => $authToken->user->id]);
            } else {
                // Geçersiz token
                session()->forget('auth_token');
                Log::warning('TokenAuthentication: Invalid token');
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
