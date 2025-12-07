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
     * URL'deki _token parametresi ile authentication yap
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Zaten authenticated ise devam et
        if (Auth::check()) {
            return $this->appendTokenToResponse($request, $next($request));
        }

        // URL'den token al
        $token = $request->query('_token') ?? $request->cookie('auth_token');

        if ($token) {
            $authToken = AuthToken::findValidToken($token);

            if ($authToken) {
                // Kullanıcıyı authenticate et
                Auth::login($authToken->user);

                // Token'ı session'a kaydet (sayfa içi navigasyon için)
                session(['auth_token' => $token]);
            }
        }

        // Session'dan token kontrol et
        if (!Auth::check() && session('auth_token')) {
            $authToken = AuthToken::findValidToken(session('auth_token'));
            if ($authToken) {
                Auth::login($authToken->user);
            }
        }

        return $this->appendTokenToResponse($request, $next($request));
    }

    /**
     * Response'daki tüm internal URL'lere token ekle
     */
    protected function appendTokenToResponse(Request $request, Response $response): Response
    {
        $token = session('auth_token');

        if (!$token || !Auth::check()) {
            return $response;
        }

        // HTML response ise URL'leri güncelle
        if ($response->headers->get('Content-Type') && 
            str_contains($response->headers->get('Content-Type'), 'text/html')) {
            
            $content = $response->getContent();
            $baseUrl = config('app.url');

            // href ve action URL'lerine token ekle
            $content = preg_replace_callback(
                '/(href|action)=["\'](' . preg_quote($baseUrl, '/') . '[^"\']*|\/[^"\']*)["\']/',
                function ($matches) use ($token) {
                    $url = $matches[2];
                    
                    // Dış linkler, asset'ler ve logout hariç
                    if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)(\?|$)/', $url)) {
                        return $matches[0];
                    }
                    
                    // Logout için token ekleme
                    if (str_contains($url, 'logout')) {
                        return $matches[0];
                    }

                    // Token zaten varsa ekleme
                    if (str_contains($url, '_token=')) {
                        return $matches[0];
                    }

                    // Token ekle
                    $separator = str_contains($url, '?') ? '&' : '?';
                    return $matches[1] . '="' . $url . $separator . '_token=' . $token . '"';
                },
                $content
            );

            $response->setContent($content);
        }

        return $response;
    }
}
