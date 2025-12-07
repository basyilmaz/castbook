<?php

namespace App\Helpers;

/**
 * Token-based authentication helper
 * Railway'de session cookie çalışmadığı için URL'lere token eklememiz gerekiyor
 */
class AuthTokenHelper
{
    /**
     * URL'e auth token ekle
     */
    public static function appendToken(string $url): string
    {
        $token = session('auth_token');
        
        if (!$token) {
            return $url;
        }
        
        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . '_auth=' . $token;
    }
    
    /**
     * Route URL'ine token ekle
     */
    public static function route(string $name, array $parameters = []): string
    {
        $url = route($name, $parameters);
        return self::appendToken($url);
    }
    
    /**
     * Redirect response'a token ekle
     */
    public static function redirect(string $url): \Illuminate\Http\RedirectResponse
    {
        return redirect(self::appendToken($url));
    }
    
    /**
     * Route'a redirect ve token ekle
     */
    public static function redirectRoute(string $name, array $parameters = []): \Illuminate\Http\RedirectResponse
    {
        return redirect(self::route($name, $parameters));
    }
}
