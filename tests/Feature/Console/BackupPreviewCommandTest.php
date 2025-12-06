<?php

namespace Tests\Feature\Console;

use App\Services\BackupEncryptionService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackupPreviewCommandTest extends TestCase
{
    public function test_json_output_for_plain_backup(): void
    {
        $path = $this->writeTempBackup($this->plainStructure());

        try {
            Artisan::call('backup:preview', [
                'file' => $path,
                '--json' => true,
            ]);

            $output = Artisan::output();
            $decoded = json_decode($output, true);

            $this->assertIsArray($decoded);
            $this->assertSame(1, $decoded['counts']['firms']);
            $this->assertSame(1, $decoded['counts']['invoices']);
            $this->assertFalse($decoded['meta']['encrypted'] ?? true);
        } finally {
            @unlink($path);
        }
    }

    public function test_json_output_for_encrypted_backup_with_password(): void
    {
        $service = app(BackupEncryptionService::class);
        $encrypted = $service->encrypt($this->plainStructure(), 'CLI-Secret');
        $path = $this->writeTempBackup($encrypted);

        try {
            Artisan::call('backup:preview', [
                'file' => $path,
                '--password' => 'CLI-Secret',
                '--json' => true,
            ]);

            $output = Artisan::output();
            $decoded = json_decode($output, true);

            $this->assertIsArray($decoded);
            $this->assertSame(1, $decoded['counts']['payments']);
            $this->assertSame('1.1', $decoded['meta']['version']);
            $this->assertFalse($decoded['meta']['encrypted']);
        } finally {
            @unlink($path);
        }
    }

    public function test_table_output_can_be_logged(): void
    {
        $path = $this->writeTempBackup($this->plainStructure());
        $logPath = tempnam(sys_get_temp_dir(), 'backup-log-');

        try {
            Artisan::call('backup:preview', [
                'file' => $path,
                '--log' => $logPath,
            ]);

            $this->assertStringContainsString('Ã–n izleme tamamlandÄ±.', Artisan::output());
            $logged = file_get_contents($logPath);
            $this->assertStringContainsString('Firms: 1', $logged);
            $this->assertStringContainsString('Invoices: 1', $logged);
        } finally {
            @unlink($path);
            @unlink($logPath);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function plainStructure(): array
    {
        $data = [
            'firms' => [
                ['id' => 1, 'name' => 'CLI Firm', 'status' => 'active'],
            ],
            'invoices' => [
                ['id' => 1, 'firm_id' => 1, 'status' => 'paid', 'date' => '2025-05-01'],
            ],
            'payments' => [
                ['id' => 1, 'firm_id' => 1, 'amount' => 500],
            ],
            'transactions' => [
                ['id' => 1, 'firm_id' => 1, 'type' => 'credit'],
            ],
            'settings' => [
                ['key' => 'company_name', 'value' => 'CLI Firm'],
            ],
        ];

        return [
            'meta' => [
                'version' => '1.1',
                'generated_at' => now()->toIso8601String(),
                'encrypted' => false,
                'checksum' => hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE)),
            ],
            'data' => $data,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function writeTempBackup(array $payload): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'backup-preview-');
        file_put_contents($temp, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $temp;
    }
}
