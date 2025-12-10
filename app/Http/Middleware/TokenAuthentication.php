<?php

namespace App\Http\Middleware;

use App\Models\AuthToken;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TokenAuthentication
{
    /**
     * Token-based authentication middleware
     * Railway'de session cookie çalışmadığı için URL token kullanıyoruz
     * Token URL query, POST body veya session'dan alınır
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Zaten authenticated ise devam et
        if (Auth::check()) {
            $response = $next($request);
            return $this->addSecurityHeaders($this->appendTokenToRedirect($response));
        }

        // Token'ı al: URL query (GET/POST), POST body veya session'dan
        // Axios interceptor params kullanıyor, bu query string'e gider
        $token = $request->query('_auth')     // URL query string'den (GET veya POST)
              ?? $request->input('_auth')     // Form body'den (POST)
              ?? $request->header('X-Auth-Token')  // Header'dan (alternatif)
              ?? session('auth_token');       // Session'dan

        if ($token) {
            // Token validation
            $authToken = AuthToken::findValidToken($token, null);

            if ($authToken) {
                // Kullanıcıyı authenticate et
                Auth::login($authToken->user);

                // Token'ı session'a kaydet
                session(['auth_token' => $token]);
            } else {
                // Geçersiz token
                session()->forget('auth_token');
            }
        }

        $response = $next($request);
        return $this->addSecurityHeaders($this->appendTokenToRedirect($response));
    }

    /**
     * Redirect response'lara token ekle
     */
    protected function appendTokenToRedirect(Response $response): Response
    {
        if (!($response instanceof RedirectResponse)) {
            return $response;
        }
        
        $token = session('auth_token');
        if (!$token) {
            return $response;
        }
        
        $targetUrl = $response->getTargetUrl();
        
        // Logout, login ve dış URL'ler hariç
        if (str_contains($targetUrl, 'logout') || str_contains($targetUrl, 'login')) {
            return $response;
        }
        
        // Zaten token varsa ekleme
        if (str_contains($targetUrl, '_auth=')) {
            return $response;
        }
        
        // Token ekle
        $separator = str_contains($targetUrl, '?') ? '&' : '?';
        $newUrl = $targetUrl . $separator . '_auth=' . $token;
        $response->setTargetUrl($newUrl);
        
        return $response;
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
