<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_creation_updates_invoice_status(): void
    {
        $user = User::factory()->create();
        $firm = Firm::create([
            'name' => 'Tahsilat Firma',
            'monthly_fee' => 2000,
            'status' => 'active',
            'contract_start_at' => now()->subYear(),
        ]);

        $invoice = Invoice::create([
            'firm_id' => $firm->id,
            'date' => now(),
            'due_date' => now()->addDays(5),
            'amount' => 2000,
            'status' => 'unpaid',
        ]);

        $token = 'test-token';

        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->post(route('payments.store'), [
                'firm_id' => $firm->id,
                'invoice_id' => $invoice->id,
                'amount' => 2000,
                'method' => 'Banka',
                'date' => now()->format('Y-m-d'),
                'note' => 'Test tahsilat',
                '_token' => $token,
            ]);

        $response->assertRedirect(route('payments.index'));
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 2000,
        ]);

        $this->assertDatabaseHas('transactions', [
            'firm_id' => $firm->id,
            'type' => 'credit',
            'amount' => 2000,
        ]);

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_payment_deletion_reverts_invoice_status(): void
    {
        $user = User::factory()->create();
        $firm = Firm::create([
            'name' => 'Silme Firma',
            'monthly_fee' => 500,
            'status' => 'active',
            'contract_start_at' => now()->subYear(),
        ]);

        $invoice = Invoice::create([
            'firm_id' => $firm->id,
            'date' => now(),
            'due_date' => now()->addDays(3),
            'amount' => 500,
            'status' => 'unpaid',
        ]);

        $payment = Payment::create([
            'firm_id' => $firm->id,
            'invoice_id' => $invoice->id,
            'amount' => 500,
            'method' => 'Nakit',
            'date' => now(),
        ]);

        $payment->transactions()->create([
            'firm_id' => $firm->id,
            'type' => 'credit',
            'amount' => 500,
            'date' => now(),
        ]);

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $token = 'test-token';
        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->delete(route('payments.destroy', $payment), ['_token' => $token]);

        $response->assertRedirect(route('payments.index'));
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertEquals('unpaid', $invoice->fresh()->status);
    }
}
