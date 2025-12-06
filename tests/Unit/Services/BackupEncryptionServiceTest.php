<?php

namespace Tests\Unit\Services;

use App\Services\BackupEncryptionService;
use RuntimeException;
use Tests\TestCase;

class BackupEncryptionServiceTest extends TestCase
{
    private BackupEncryptionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BackupEncryptionService();
    }

    public function test_encrypt_and_decrypt_round_trip(): void
    {
        $structure = [
            'meta' => [
                'version' => '1.1',
                'generated_at' => '2024-05-01T12:00:00+00:00',
                'encrypted' => false,
            ],
            'data' => [
                'firms' => [['id' => 1, 'name' => 'Acme']],
            ],
        ];

        $encrypted = $this->service->encrypt($structure, 'S3cret!');

        $this->assertTrue($encrypted['meta']['encrypted']);
        $this->assertSame('AES-256-GCM', $encrypted['meta']['algorithm']);
        $this->assertNotEmpty($encrypted['payload']);

        $decrypted = $this->service->decrypt($encrypted, 'S3cret!');

        $this->assertSame($structure['data'], $decrypted['data']);
        $this->assertSame($structure['meta']['version'], $decrypted['meta']['version']);
        $this->assertFalse($decrypted['meta']['encrypted']);
    }

    public function test_decrypt_with_wrong_password_fails(): void
    {
        $structure = [
            'meta' => [
                'version' => '1.1',
                'generated_at' => '2024-05-01T12:00:00+00:00',
                'encrypted' => false,
            ],
            'data' => ['settings' => []],
        ];

        $encrypted = $this->service->encrypt($structure, 'correct-password');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Åifre Ã§Ã¶zme iÅŸlemi baÅŸarÄ±sÄ±z');

        $this->service->decrypt($encrypted, 'wrong-password');
    }

    public function test_tampered_payload_is_detected(): void
    {
        $structure = [
            'meta' => [
                'version' => '1.1',
                'generated_at' => '2024-05-01T12:00:00+00:00',
                'encrypted' => false,
            ],
            'data' => ['transactions' => []],
        ];

        $encrypted = $this->service->encrypt($structure, 'AnotherSecret');
        $encrypted['payload'] = base64_encode(strrev(base64_decode($encrypted['payload'])));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Åifre Ã§Ã¶zme iÅŸlemi baÅŸarÄ±sÄ±z');

        $this->service->decrypt($encrypted, 'AnotherSecret');
    }
}
