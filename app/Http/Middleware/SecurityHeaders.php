<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Güvenlik başlıklarını ekle
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // XSS koruması
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Clickjacking koruması
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // MIME type sniffing koruması
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer politikası
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (eski Feature-Policy)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content Security Policy (CSP) - Temel kurallar
        // Not: Geliştirme ortamında 'unsafe-inline' ve 'unsafe-eval' gerekebilir
        if (app()->environment('production')) {
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://cdn.jsdelivr.net",
                "font-src 'self' https://fonts.bunny.net data:",
                "img-src 'self' data: blob:",
                "connect-src 'self'",
                "frame-ancestors 'self'",
                "form-action 'self'",
                "base-uri 'self'",
            ]);
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // HTTPS zorlama (production)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
