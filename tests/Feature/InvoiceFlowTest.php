<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_creation_creates_transaction_record(): void
    {
        $user = User::factory()->create();
        $firm = Firm::create([
            'name' => 'Test Firma',
            'monthly_fee' => 1200,
            'status' => 'active',
            'contract_start_at' => now()->subYear(),
        ]);

        $payload = [
            'firm_id' => $firm->id,
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'amount' => 1200,
            'description' => 'Test faturasi',
        ];

        $token = 'test-token';
        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->post(route('invoices.store'), array_merge($payload, ['_token' => $token]));

        $invoice = Invoice::first();

        $response->assertRedirect(route('invoices.show', $invoice));
        $this->assertNotNull($invoice);
        $this->assertDatabaseHas('transactions', [
            'firm_id' => $firm->id,
            'sourceable_id' => $invoice->id,
            'sourceable_type' => Invoice::class,
            'type' => 'debit',
            'amount' => 1200,
        ]);
    }

    public function test_paid_invoice_cannot_be_updated(): void
    {
        $user = User::factory()->create();
        $firm = Firm::create([
            'name' => 'Test Firma',
            'monthly_fee' => 1000,
            'status' => 'active',
            'contract_start_at' => now()->subYear(),
        ]);

        $invoice = Invoice::create([
            'firm_id' => $firm->id,
            'date' => now(),
            'due_date' => now()->addDays(5),
            'amount' => 1000,
            'status' => 'paid',
        ]);

        $token = 'test-token';
        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->put(route('invoices.update', $invoice), [
                'firm_id' => $firm->id,
                'date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'amount' => 1100,
                'description' => 'Deneme',
                'status' => 'paid',
                '_token' => $token,
            ]);

        $response->assertSessionHasErrors('invoice');
        $this->assertEquals(1000, $invoice->fresh()->amount);
    }
}
