<?php

namespace Tests\Unit;

use App\Services\LicenseService;
use Tests\TestCase;

class LicenseServiceTest extends TestCase
{
    protected LicenseService $licenseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->licenseService = new LicenseService();
    }

    // ==========================================
    // KEY GENERATION VE CHECKSUM TESTLERİ
    // ==========================================

    public function test_valid_key_generation(): void
    {
        $key = $this->licenseService->generateValidKey('pro', 'TEST', 'ABCD');
        
        $this->assertMatchesRegularExpression('/^PRO-TEST-ABCD-\d{4}$/', $key);
    }

    public function test_basic_key_prefix(): void
    {
        $key = $this->licenseService->generateValidKey('basic', 'TEST', 'BSC1');
        $this->assertStringStartsWith('BSC-', $key);
    }

    public function test_enterprise_key_prefix(): void
    {
        $key = $this->licenseService->generateValidKey('enterprise', 'CORP', 'ENTX');
        $this->assertStringStartsWith('ENT-', $key);
    }

    public function test_checksum_is_four_digits(): void
    {
        $key = $this->licenseService->generateValidKey('pro', 'XXXX', 'YYYY');
        $parts = explode('-', $key);
        
        $this->assertCount(4, $parts);
        $this->assertEquals(4, strlen($parts[3]));
        $this->assertTrue(ctype_digit($parts[3]) || ctype_alnum($parts[3]));
    }

    public function test_same_input_generates_same_checksum(): void
    {
        $key1 = $this->licenseService->generateValidKey('pro', 'AAAA', 'BBBB');
        $key2 = $this->licenseService->generateValidKey('pro', 'AAAA', 'BBBB');
        
        $this->assertEquals($key1, $key2);
    }

    public function test_different_input_generates_different_checksum(): void
    {
        $key1 = $this->licenseService->generateValidKey('pro', 'AAAA', 'BBBB');
        $key2 = $this->licenseService->generateValidKey('pro', 'CCCC', 'DDDD');
        
        $this->assertNotEquals($key1, $key2);
    }

    // ==========================================
    // FİNGERPRİNT TESTLERİ
    // ==========================================

    public function test_fingerprint_is_consistent(): void
    {
        $fingerprint1 = $this->licenseService->generateFingerprint();
        $fingerprint2 = $this->licenseService->generateFingerprint();

        $this->assertEquals($fingerprint1, $fingerprint2);
    }

    public function test_fingerprint_is_sha256(): void
    {
        $fingerprint = $this->licenseService->generateFingerprint();
        
        // SHA256 = 64 hex karakteri
        $this->assertEquals(64, strlen($fingerprint));
        $this->assertTrue(ctype_xdigit($fingerprint));
    }

    // ==========================================
    // LİSANS TİPLERİ TESTLERİ
    // ==========================================

    public function test_license_types_are_defined(): void
    {
        $types = LicenseService::LICENSE_TYPES;
        
        $this->assertArrayHasKey('trial', $types);
        $this->assertArrayHasKey('basic', $types);
        $this->assertArrayHasKey('pro', $types);
        $this->assertArrayHasKey('enterprise', $types);
    }

    public function test_trial_has_correct_limits(): void
    {
        $trial = LicenseService::LICENSE_TYPES['trial'];
        
        $this->assertEquals(10, $trial['max_firms']);
        $this->assertEquals(14, $trial['duration_days']);
    }

    public function test_enterprise_has_unlimited(): void
    {
        $enterprise = LicenseService::LICENSE_TYPES['enterprise'];
        
        $this->assertEquals(-1, $enterprise['max_firms']);
        $this->assertEquals(-1, $enterprise['duration_days']);
    }

    public function test_all_types_have_required_keys(): void
    {
        $requiredKeys = ['name', 'max_firms', 'max_domains', 'duration_days', 'features'];
        
        foreach (LicenseService::LICENSE_TYPES as $type => $config) {
            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $config, "Type '{$type}' missing key '{$key}'");
            }
        }
    }

    // ==========================================
    // KEY FORMAT DOĞRULAMA
    // ==========================================

    public function test_generated_key_format_is_valid(): void
    {
        $key = $this->licenseService->generateValidKey('pro', 'ABCD', '1234');
        
        // Format: XXX-XXXX-XXXX-XXXX
        $this->assertMatchesRegularExpression('/^[A-Z]{3}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key);
    }
}
