<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirmManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_firm(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Test Firma',
            'company_type' => 'individual',
            'tax_no' => '1234567890',
            'contact_person' => 'Ahmet Yilmaz',
            'contact_email' => 'ahmet@example.com',
            'contact_phone' => '5551234567',
            'monthly_fee' => 1500,
            'status' => 'active',
            'contract_start_at' => now()->subMonths(6)->format('Y-m-d'),
            'notes' => 'Test notu',
        ];

        $token = 'test-token';

        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->post(route('firms.store'), array_merge($payload, ['_token' => $token]));

        $response->assertRedirect(route('firms.index'));
        $this->assertDatabaseHas('firms', [
            'name' => 'Test Firma',
            'tax_no' => '1234567890',
        ]);
    }

    public function test_authenticated_user_can_update_firm(): void
    {
        $user = User::factory()->create();
        $firm = Firm::create([
            'name' => 'Eski Firma',
            'company_type' => 'limited',
            'tax_no' => '1111111111',
            'monthly_fee' => 1000,
            'status' => 'active',
            'contract_start_at' => now()->subYear(),
        ]);

        $payload = [
            'name' => 'Guncellenmis Firma',
            'company_type' => 'joint_stock',
            'tax_no' => '2222222222',
            'contact_person' => 'Mehmet Kaya',
            'contact_email' => 'mehmet@example.com',
            'contact_phone' => '5559998877',
            'monthly_fee' => 1750,
            'status' => 'inactive',
            'contract_start_at' => now()->subMonths(3)->format('Y-m-d'),
            'notes' => 'Guncelleme sonrasi not',
        ];

        $token = 'test-token';

        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->put(route('firms.update', $firm), array_merge($payload, ['_token' => $token]));

        $response->assertRedirect(route('firms.show', $firm));
        $this->assertDatabaseHas('firms', [
            'id' => $firm->id,
            'name' => 'Guncellenmis Firma',
            'status' => 'inactive',
        ]);
    }
}
