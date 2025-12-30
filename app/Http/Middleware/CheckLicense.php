<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bazı route'ları muaf tut
        $exemptRoutes = [
            'login', 'logout', 'install', 'install/*', 'health',
            'settings', 'settings/license', 'settings/license/*',
            'api/license/*',
        ];

        foreach ($exemptRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // Lisans kontrolü
        $license = $this->licenseService->check();

        if (!$license['valid']) {
            // Lisans geçersizse uyarı göster ama engelleme
            session()->flash('license_warning', $license['message'] ?? 'Lisans geçersiz veya süresi dolmuş.');
        }

        // Trial uyarısı
        if (($license['is_trial'] ?? false) && ($license['days_remaining'] ?? 0) <= 3) {
            session()->flash('license_warning', "Deneme süreniz {$license['days_remaining']} gün içinde sona erecek.");
        }

        // View'a lisans bilgisini paylaş
        view()->share('licenseInfo', $this->licenseService->getDisplayInfo());

        return $next($request);
    }
}
