<?php

namespace App\Http\Middleware;

use App\Services\InstallService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallation
{
    protected InstallService $installService;

    public function __construct(InstallService $installService)
    {
        $this->installService = $installService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kurulum route'larına izin ver
        if ($request->is('install*')) {
            // Eğer zaten kuruluysa kurulum sayfasına erişimi engelle
            if ($this->installService->isInstalled() && !$request->is('install/complete')) {
                return redirect('/');
            }
            return $next($request);
        }

        // Uygulama kurulu değilse kurulum sayfasına yönlendir
        if (!$this->installService->isInstalled()) {
            return redirect()->route('install.index');
        }

        return $next($request);
    }
}
