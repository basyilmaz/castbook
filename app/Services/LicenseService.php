<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    // Lisans tipleri ve limitleri
    public const LICENSE_TYPES = [
        'trial' => [
            'name' => 'Trial',
            'max_firms' => 10,
            'max_domains' => 1,
            'duration_days' => 14,
            'features' => ['all'],
        ],
        'basic' => [
            'name' => 'Basic',
            'max_firms' => 50,
            'max_domains' => 1,
            'duration_days' => 365,
            'features' => ['firms', 'invoices', 'reports'],
        ],
        'pro' => [
            'name' => 'Pro',
            'max_firms' => 500,
            'max_domains' => 3,
            'duration_days' => 365,
            'features' => ['all'],
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'max_firms' => -1, // Sınırsız
            'max_domains' => -1,
            'duration_days' => -1, // Ömür boyu
            'features' => ['all'],
        ],
    ];

    protected string $cacheKey = 'license_data';
    protected int $cacheDuration = 86400; // 24 saat

    /**
     * Lisans durumunu kontrol et
     */
    public function check(): array
    {
        // Önce cache'den kontrol et
        $cachedLicense = Cache::get($this->cacheKey);
        if ($cachedLicense && $this->isValidCachedLicense($cachedLicense)) {
            return $cachedLicense;
        }

        // Veritabanından lisans bilgilerini al
        $licenseKey = $this->getLicenseKey();
        if (!$licenseKey) {
            return $this->getTrialLicense();
        }

        // Lisansı doğrula
        return $this->validateLicense($licenseKey);
    }

    /**
     * Lisans key'i kaydet ve aktive et
     */
    public function activate(string $licenseKey): array
    {
        // Lisans key formatını kontrol et
        if (!$this->isValidKeyFormat($licenseKey)) {
            return [
                'success' => false,
                'message' => 'Geçersiz lisans key formatı.',
            ];
        }

        // Lisansı doğrula (API veya local)
        $result = $this->validateLicense($licenseKey);

        if ($result['valid']) {
            // Lisansı kaydet
            $this->saveLicenseKey($licenseKey);
            
            // Cache'e kaydet
            Cache::put($this->cacheKey, $result, $this->cacheDuration);

            return [
                'success' => true,
                'message' => 'Lisans başarıyla aktive edildi.',
                'license' => $result,
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'] ?? 'Lisans doğrulanamadı.',
        ];
    }

    /**
     * Lisansı kaldır
     */
    public function deactivate(): bool
    {
        \App\Models\Setting::where('key', 'license_key')->delete();
        \App\Models\Setting::where('key', 'license_activated_at')->delete();
        \App\Models\Setting::where('key', 'license_type')->delete();
        Cache::forget($this->cacheKey);
        
        return true;
    }

    /**
     * Lisans key formatını kontrol et
     * Format: XXXX-XXXX-XXXX-XXXX
     */
    protected function isValidKeyFormat(string $key): bool
    {
        return (bool) preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', strtoupper($key));
    }

    /**
     * Lisansı doğrula
     */
    protected function validateLicense(string $licenseKey): array
    {
        // Önce local doğrulama yap (API olmadan çalışabilmesi için)
        $localResult = $this->validateLocally($licenseKey);
        if ($localResult['valid']) {
            return $localResult;
        }

        // API doğrulama (opsiyonel)
        $apiUrl = config('app.license_api_url');
        if ($apiUrl) {
            try {
                $response = Http::timeout(10)->post($apiUrl . '/validate', [
                    'license_key' => $licenseKey,
                    'domain' => request()->getHost(),
                    'product' => 'castbook',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['valid'] ?? false) {
                        return [
                            'valid' => true,
                            'type' => $data['type'] ?? 'basic',
                            'expires_at' => $data['expires_at'] ?? null,
                            'max_firms' => $data['max_firms'] ?? 50,
                            'features' => $data['features'] ?? ['all'],
                            'message' => 'Lisans geçerli.',
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('License API error: ' . $e->getMessage());
            }
        }

        return $localResult;
    }

    /**
     * Local lisans doğrulama
     * Demo/geliştirme için basit doğrulama
     */
    protected function validateLocally(string $licenseKey): array
    {
        $key = strtoupper($licenseKey);
        
        // Enterprise key pattern: ENT-XXXX-XXXX-XXXX
        if (str_starts_with($key, 'ENT-')) {
            return [
                'valid' => true,
                'type' => 'enterprise',
                'expires_at' => null,
                'max_firms' => -1,
                'features' => ['all'],
                'message' => 'Enterprise lisans.',
            ];
        }

        // Pro key pattern: PRO-XXXX-XXXX-XXXX
        if (str_starts_with($key, 'PRO-')) {
            return [
                'valid' => true,
                'type' => 'pro',
                'expires_at' => now()->addYear()->toDateString(),
                'max_firms' => 500,
                'features' => ['all'],
                'message' => 'Pro lisans.',
            ];
        }

        // Basic key pattern: BSC-XXXX-XXXX-XXXX
        if (str_starts_with($key, 'BSC-')) {
            return [
                'valid' => true,
                'type' => 'basic',
                'expires_at' => now()->addYear()->toDateString(),
                'max_firms' => 50,
                'features' => ['firms', 'invoices', 'reports'],
                'message' => 'Basic lisans.',
            ];
        }

        // Geçersiz key
        return [
            'valid' => false,
            'type' => null,
            'message' => 'Geçersiz lisans key.',
        ];
    }

    /**
     * Trial lisans döndür
     */
    protected function getTrialLicense(): array
    {
        $installedAt = \App\Models\Setting::getValue('app_installed_at');
        $trialEndsAt = $installedAt 
            ? \Carbon\Carbon::parse($installedAt)->addDays(14)->toDateString()
            : now()->addDays(14)->toDateString();

        $isExpired = now()->isAfter($trialEndsAt);

        return [
            'valid' => !$isExpired,
            'type' => 'trial',
            'expires_at' => $trialEndsAt,
            'max_firms' => 10,
            'features' => ['all'],
            'is_trial' => true,
            'days_remaining' => $isExpired ? 0 : now()->diffInDays($trialEndsAt),
            'message' => $isExpired ? 'Deneme süresi doldu.' : 'Deneme sürümü.',
        ];
    }

    /**
     * Cached lisans geçerli mi?
     */
    protected function isValidCachedLicense(array $license): bool
    {
        if (!$license['valid']) {
            return false;
        }

        // Süre kontrolü
        if (isset($license['expires_at']) && $license['expires_at']) {
            if (now()->isAfter($license['expires_at'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Kayıtlı lisans key'i al
     */
    protected function getLicenseKey(): ?string
    {
        return \App\Models\Setting::getValue('license_key');
    }

    /**
     * Lisans key'i kaydet
     */
    protected function saveLicenseKey(string $key): void
    {
        \App\Models\Setting::updateOrCreate(
            ['key' => 'license_key'],
            ['value' => strtoupper($key)]
        );
        
        \App\Models\Setting::updateOrCreate(
            ['key' => 'license_activated_at'],
            ['value' => now()->toDateTimeString()]
        );
    }

    /**
     * Firma limiti kontrolü
     */
    public function canCreateFirm(): bool
    {
        $license = $this->check();
        
        if (!$license['valid']) {
            return false;
        }

        $maxFirms = $license['max_firms'] ?? 0;
        
        // -1 = sınırsız
        if ($maxFirms === -1) {
            return true;
        }

        $currentFirmCount = \App\Models\Firm::count();
        return $currentFirmCount < $maxFirms;
    }

    /**
     * Özellik erişimi kontrolü
     */
    public function hasFeature(string $feature): bool
    {
        $license = $this->check();
        
        if (!$license['valid']) {
            return false;
        }

        $features = $license['features'] ?? [];
        
        return in_array('all', $features) || in_array($feature, $features);
    }

    /**
     * Lisans bilgilerini formatla (görüntüleme için)
     */
    public function getDisplayInfo(): array
    {
        $license = $this->check();
        
        $typeInfo = self::LICENSE_TYPES[$license['type'] ?? 'trial'] ?? self::LICENSE_TYPES['trial'];

        return [
            'is_licensed' => $license['valid'] && !($license['is_trial'] ?? false),
            'is_trial' => $license['is_trial'] ?? false,
            'is_valid' => $license['valid'],
            'type' => $license['type'] ?? 'trial',
            'type_name' => $typeInfo['name'],
            'expires_at' => $license['expires_at'] ?? null,
            'days_remaining' => $license['days_remaining'] ?? null,
            'max_firms' => $license['max_firms'] ?? 0,
            'current_firms' => \App\Models\Firm::count(),
            'message' => $license['message'] ?? '',
        ];
    }
}
