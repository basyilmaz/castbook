<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    // ==========================================
    // GÜVENLİK SABİTLERİ (Obfuscated)
    // ==========================================
    private const SALT = 'C@stB00k!L1c3ns3#2025';
    private const CHECKSUM_MULTIPLIER = 7;
    private const FINGERPRINT_ALGO = 'sha256';
    
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
            'max_firms' => -1,
            'max_domains' => -1,
            'duration_days' => -1,
            'features' => ['all'],
        ],
    ];

    protected string $cacheKey = 'license_data';
    protected int $cacheDuration = 3600; // 1 saat (daha kısa)

    // ==========================================
    // HARDWARE FINGERPRINT
    // ==========================================
    
    /**
     * Sunucu fingerprint'ini oluştur
     */
    public function generateFingerprint(): string
    {
        $components = [
            request()->getHost(),
            $_SERVER['SERVER_NAME'] ?? 'unknown',
            $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
            php_uname('n'), // hostname
            base_path(), // uygulama yolu
        ];
        
        $data = implode('|', $components) . self::SALT;
        return hash(self::FINGERPRINT_ALGO, $data);
    }

    /**
     * Fingerprint'i doğrula
     */
    protected function verifyFingerprint(string $storedFingerprint): bool
    {
        return hash_equals($storedFingerprint, $this->generateFingerprint());
    }

    // ==========================================
    // KEY ŞİFRELEME
    // ==========================================
    
    /**
     * Lisans key'i şifrele
     */
    protected function encryptKey(string $key): string
    {
        return Crypt::encryptString($key);
    }

    /**
     * Lisans key'i çöz
     */
    protected function decryptKey(string $encryptedKey): ?string
    {
        try {
            return Crypt::decryptString($encryptedKey);
        } catch (\Exception $e) {
            Log::warning('License key decryption failed');
            return null;
        }
    }

    // ==========================================
    // CHECKSUM DOĞRULAMA
    // ==========================================
    
    /**
     * Lisans key checksum'ını doğrula
     * Geçerli format: TYPE-XXXX-XXXX-CHCK
     * CHCK = ilk 3 bölümün karakterlerinin ASCII toplamı mod 10000
     */
    protected function validateChecksum(string $key): bool
    {
        $parts = explode('-', strtoupper($key));
        if (count($parts) !== 4) {
            return false;
        }

        // Son bölüm checksum olmalı
        $providedChecksum = $parts[3];
        
        // İlk 3 bölümün checksum'ını hesapla
        $data = $parts[0] . $parts[1] . $parts[2];
        $sum = 0;
        for ($i = 0; $i < strlen($data); $i++) {
            $sum += ord($data[$i]) * self::CHECKSUM_MULTIPLIER;
        }
        $calculatedChecksum = str_pad($sum % 10000, 4, '0', STR_PAD_LEFT);

        return $providedChecksum === $calculatedChecksum;
    }

    /**
     * Geçerli checksum ile key oluştur (generator için)
     */
    public function generateValidKey(string $type, string $random1, string $random2): string
    {
        $prefix = match($type) {
            'basic' => 'BSC',
            'pro' => 'PRO',
            'enterprise' => 'ENT',
            default => 'TRL',
        };

        $data = $prefix . $random1 . $random2;
        $sum = 0;
        for ($i = 0; $i < strlen($data); $i++) {
            $sum += ord($data[$i]) * self::CHECKSUM_MULTIPLIER;
        }
        $checksum = str_pad($sum % 10000, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$random1}-{$random2}-{$checksum}";
    }

    // ==========================================
    // LİSANS DOĞRULAMA
    // ==========================================

    /**
     * Lisans durumunu kontrol et
     */
    public function check(): array
    {
        // Cache kontrolü
        $cachedLicense = Cache::get($this->cacheKey);
        if ($cachedLicense && $this->isValidCachedLicense($cachedLicense)) {
            // Periyodik API doğrulaması (her 24 saatte bir)
            $lastApiCheck = Cache::get('license_last_api_check', 0);
            if (time() - $lastApiCheck > 86400) {
                $this->periodicApiValidation();
            }
            return $cachedLicense;
        }

        // Veritabanından lisans bilgilerini al
        $encryptedKey = $this->getStoredLicenseKey();
        if (!$encryptedKey) {
            return $this->getTrialLicense();
        }

        // Key'i çöz
        $licenseKey = $this->decryptKey($encryptedKey);
        if (!$licenseKey) {
            return $this->getTrialLicense();
        }

        // Fingerprint kontrolü
        $storedFingerprint = \App\Models\Setting::getValue('license_fingerprint');
        if ($storedFingerprint && !$this->verifyFingerprint($storedFingerprint)) {
            Log::warning('License fingerprint mismatch - possible unauthorized transfer');
            return [
                'valid' => false,
                'type' => null,
                'message' => 'Lisans bu sunucu için geçerli değil. Lütfen yeniden aktive edin.',
            ];
        }

        // Lisansı doğrula
        return $this->validateLicense($licenseKey);
    }

    /**
     * Lisans aktive et
     */
    public function activate(string $licenseKey): array
    {
        $key = strtoupper(trim($licenseKey));

        // Format kontrolü
        if (!$this->isValidKeyFormat($key)) {
            return [
                'success' => false,
                'message' => 'Geçersiz lisans key formatı.',
            ];
        }

        // Checksum kontrolü
        if (!$this->validateChecksum($key)) {
            return [
                'success' => false,
                'message' => 'Lisans key doğrulaması başarısız.',
            ];
        }

        // API doğrulama (varsa)
        $apiResult = $this->validateWithApi($key);
        if ($apiResult !== null && !$apiResult['valid']) {
            return [
                'success' => false,
                'message' => $apiResult['message'] ?? 'API doğrulaması başarısız.',
            ];
        }

        // Local doğrulama
        $result = $this->validateLicense($key);

        if ($result['valid']) {
            // Şifreli key'i kaydet
            $this->saveLicenseData($key);
            
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
        \App\Models\Setting::where('key', 'LIKE', 'license_%')->delete();
        Cache::forget($this->cacheKey);
        Cache::forget('license_last_api_check');
        
        return true;
    }

    // ==========================================
    // API DOĞRULAMA
    // ==========================================

    /**
     * API ile lisans doğrula
     */
    protected function validateWithApi(string $licenseKey): ?array
    {
        $apiUrl = config('app.license_api_url');
        if (!$apiUrl) {
            return null; // API yoksa local doğrulama kullan
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-License-Fingerprint' => $this->generateFingerprint(),
                    'X-Product-Version' => config('app.version'),
                ])
                ->post($apiUrl . '/validate', [
                    'license_key' => $licenseKey,
                    'domain' => request()->getHost(),
                    'fingerprint' => $this->generateFingerprint(),
                    'product' => 'castbook',
                ]);

            if ($response->successful()) {
                Cache::put('license_last_api_check', time(), 86400);
                return $response->json();
            }

            return [
                'valid' => false,
                'message' => 'API yanıt hatası: ' . $response->status(),
            ];
        } catch (\Exception $e) {
            Log::warning('License API error: ' . $e->getMessage());
            return null; // API erişilemezse local doğrulama kullan
        }
    }

    /**
     * Periyodik API doğrulama (arka planda)
     */
    protected function periodicApiValidation(): void
    {
        $encryptedKey = $this->getStoredLicenseKey();
        if (!$encryptedKey) {
            return;
        }

        $key = $this->decryptKey($encryptedKey);
        if (!$key) {
            return;
        }

        $apiResult = $this->validateWithApi($key);
        if ($apiResult !== null && !$apiResult['valid']) {
            // Lisans geçersiz - cache'i temizle
            Cache::forget($this->cacheKey);
            Log::warning('License revoked by API: ' . ($apiResult['message'] ?? 'Unknown'));
        }
    }

    // ==========================================
    // YARDIMCI METODLAR
    // ==========================================

    /**
     * Key format kontrolü
     */
    protected function isValidKeyFormat(string $key): bool
    {
        return (bool) preg_match('/^[A-Z]{3}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key);
    }

    /**
     * Lisansı doğrula (local)
     */
    protected function validateLicense(string $licenseKey): array
    {
        $key = strtoupper($licenseKey);
        $parts = explode('-', $key);
        
        if (count($parts) !== 4) {
            return ['valid' => false, 'message' => 'Geçersiz key formatı.'];
        }

        // Checksum kontrolü
        if (!$this->validateChecksum($key)) {
            return ['valid' => false, 'message' => 'Key checksum doğrulaması başarısız.'];
        }

        $prefix = $parts[0];
        
        // Enterprise
        if ($prefix === 'ENT') {
            return [
                'valid' => true,
                'type' => 'enterprise',
                'expires_at' => null,
                'max_firms' => -1,
                'features' => ['all'],
                'message' => 'Enterprise lisans.',
            ];
        }

        // Pro
        if ($prefix === 'PRO') {
            return [
                'valid' => true,
                'type' => 'pro',
                'expires_at' => now()->addYear()->toDateString(),
                'max_firms' => 500,
                'features' => ['all'],
                'message' => 'Pro lisans.',
            ];
        }

        // Basic
        if ($prefix === 'BSC') {
            return [
                'valid' => true,
                'type' => 'basic',
                'expires_at' => now()->addYear()->toDateString(),
                'max_firms' => 50,
                'features' => ['firms', 'invoices', 'reports'],
                'message' => 'Basic lisans.',
            ];
        }

        return ['valid' => false, 'message' => 'Bilinmeyen lisans tipi.'];
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
     * Cache'deki lisans geçerli mi?
     */
    protected function isValidCachedLicense(array $license): bool
    {
        if (!$license['valid']) {
            return false;
        }

        if (isset($license['expires_at']) && $license['expires_at']) {
            if (now()->isAfter($license['expires_at'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Kayıtlı şifreli key'i al
     */
    protected function getStoredLicenseKey(): ?string
    {
        return \App\Models\Setting::getValue('license_key_encrypted');
    }

    /**
     * Lisans verilerini kaydet
     */
    protected function saveLicenseData(string $key): void
    {
        // Şifreli key
        \App\Models\Setting::updateOrCreate(
            ['key' => 'license_key_encrypted'],
            ['value' => $this->encryptKey($key)]
        );
        
        // Fingerprint
        \App\Models\Setting::updateOrCreate(
            ['key' => 'license_fingerprint'],
            ['value' => $this->generateFingerprint()]
        );
        
        // Aktivasyon zamanı
        \App\Models\Setting::updateOrCreate(
            ['key' => 'license_activated_at'],
            ['value' => now()->toDateTimeString()]
        );

        // Eski düz metin key'i sil (varsa)
        \App\Models\Setting::where('key', 'license_key')->delete();
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
     * Lisans bilgilerini formatla
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
            'fingerprint' => substr($this->generateFingerprint(), 0, 16) . '...',
        ];
    }
}
