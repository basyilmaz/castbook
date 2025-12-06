<?php

namespace Tests\Feature\Settings;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_restore_disabled_returns_error(): void
    {
        config(['backup.restore_enabled' => false]);

        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent(
            'backup.json',
            json_encode($this->plainBackupStructure(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $response = $this->actingAs($user)
            ->withSession(['_token' => 'restore-disabled'])
            ->post(route('settings.backup.restore'), [
                'mode' => 'restore',
                'backup_file' => $file,
                '_token' => 'restore-disabled',
                'confirm_restore' => '1',
            ]);

        $response->assertSessionHasErrors('backup_file');
    }

    public function test_restore_rejects_file_exceeding_max_limit(): void
    {
        config([
            'backup.restore_enabled' => true,
            'backup.max_upload_mb' => 0.0001, // yaklaÅŸÄ±k 100 bayt
        ]);

        $user = User::factory()->create();
        $content = json_encode($this->plainBackupStructure(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            . str_repeat(' ', 2048);

        $file = UploadedFile::fake()->createWithContent('oversized.json', $content);

        $response = $this->actingAs($user)
            ->withSession(['_token' => 'restore-size'])
            ->post(route('settings.backup.restore'), [
                'mode' => 'restore',
                'backup_file' => $file,
                '_token' => 'restore-size',
                'confirm_restore' => '1',
            ]);

        $response->assertSessionHasErrors('backup_file');
    }

    /**
     * @return array<string, mixed>
     */
    private function plainBackupStructure(): array
    {
        $data = [
            'firms' => [[
                'id' => 1,
                'name' => 'Test Firma',
                'status' => 'active',
            ]],
            'invoices' => [[
                'id' => 1,
                'firm_id' => 1,
                'status' => 'paid',
                'date' => '2024-05-01',
            ]],
            'payments' => [[
                'id' => 1,
                'firm_id' => 1,
                'amount' => 1000,
            ]],
            'transactions' => [[
                'id' => 1,
                'firm_id' => 1,
                'type' => 'credit',
            ]],
            'settings' => [[
                'key' => 'company_name',
                'value' => 'Test Firma',
            ]],
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
}
