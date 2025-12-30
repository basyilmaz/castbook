<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseController extends Controller
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Lisans durumunu göster
     */
    public function index(): View
    {
        $licenseInfo = $this->licenseService->getDisplayInfo();
        $licenseTypes = LicenseService::LICENSE_TYPES;

        return view('settings.tabs.license', compact('licenseInfo', 'licenseTypes'));
    }

    /**
     * Lisans aktive et
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => ['required', 'string', 'regex:/^[A-Za-z0-9]{3,4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}$/'],
        ], [
            'license_key.required' => 'Lisans key gereklidir.',
            'license_key.regex' => 'Geçersiz lisans key formatı. Format: XXXX-XXXX-XXXX-XXXX',
        ]);

        $result = $this->licenseService->activate($request->license_key);

        if ($result['success']) {
            return redirect()->route('settings.license')
                ->with('success', $result['message']);
        }

        return redirect()->route('settings.license')
            ->with('error', $result['message']);
    }

    /**
     * Lisansı kaldır
     */
    public function deactivate()
    {
        $this->licenseService->deactivate();

        return redirect()->route('settings.license')
            ->with('success', 'Lisans kaldırıldı. Deneme sürümüne geçildi.');
    }

    /**
     * Lisans durumunu JSON olarak döndür (API)
     */
    public function status()
    {
        $license = $this->licenseService->check();
        
        return response()->json([
            'valid' => $license['valid'],
            'type' => $license['type'] ?? 'trial',
            'expires_at' => $license['expires_at'] ?? null,
            'message' => $license['message'] ?? '',
        ]);
    }
}
