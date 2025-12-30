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

        // Lisans bitiş uyarısı (7 gün kala)
        if ($license['valid'] && isset($license['expires_at']) && $license['expires_at']) {
            $daysRemaining = now()->diffInDays($license['expires_at'], false);
            
            if ($daysRemaining <= 7 && $daysRemaining > 0) {
                $isTrial = $license['is_trial'] ?? false;
                $message = $isTrial 
                    ? "Deneme süreniz {$daysRemaining} gün içinde sona erecek. Lisans satın alın."
                    : "Lisansınızın süresi {$daysRemaining} gün içinde dolacak. Yenileyin.";
                session()->flash('license_warning', $message);
            }
        }

        // View'a lisans bilgisini paylaş
        view()->share('licenseInfo', $this->licenseService->getDisplayInfo());

        return $next($request);
    }
}
