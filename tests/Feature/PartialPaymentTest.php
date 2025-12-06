<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PartialPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Firm $firm;
    protected Invoice $invoice;
    protected string $csrfToken = 'test-token';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withSession(['_token' => $this->csrfToken]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->firm = Firm::create([
            'name' => 'Partial Test Firm',
            'monthly_fee' => 1000,
            'status' => 'active',
            'contract_start_at' => Carbon::parse('2024-01-01'),
        ]);

        $this->invoice = Invoice::create([
            'firm_id' => $this->firm->id,
            'date' => Carbon::parse('2024-02-01'),
            'due_date' => Carbon::parse('2024-02-15'),
            'amount' => 1000,
            'status' => 'unpaid',
            'description' => 'Åubat faturasÄ±',
        ]);
    }

    public function test_invoice_transitions_to_partial_after_partial_payment(): void
    {
        $this->actingAs($this->admin)
            ->post(route('payments.store'), [
                '_token' => $this->csrfToken,
                'firm_id' => $this->firm->id,
                'invoice_id' => $this->invoice->id,
                'amount' => 400,
                'method' => 'Banka',
                'date' => '2024-02-10',
                'note' => 'KÄ±smi tahsilat',
            ])
            ->assertRedirect(route('payments.index'));

        $invoice = $this->invoice->fresh();

        $this->assertSame('partial', $invoice->status);
        $this->assertNull($invoice->paid_at);
        $this->assertEquals(400, $invoice->payments()->sum('amount'));
    }

    public function test_invoice_becomes_paid_when_remaining_balance_cleared(): void
    {
        Payment::create([
            'firm_id' => $this->firm->id,
            'invoice_id' => $this->invoice->id,
            'amount' => 700,
            'method' => 'Banka',
            'date' => '2024-02-10',
        ]);

        $this->invoice->refreshPaymentStatus();

        $this->actingAs($this->admin)
            ->post(route('payments.store'), [
                '_token' => $this->csrfToken,
                'firm_id' => $this->firm->id,
                'invoice_id' => $this->invoice->id,
                'amount' => 300,
                'method' => 'Nakit',
                'date' => '2024-02-15',
            ])
            ->assertRedirect(route('payments.index'));

        $invoice = $this->invoice->fresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);
        $this->assertEquals(1000, $invoice->payments()->sum('amount'));
    }

    public function test_overpayment_is_blocked_with_validation_error(): void
    {
        Payment::create([
            'firm_id' => $this->firm->id,
            'invoice_id' => $this->invoice->id,
            'amount' => 800,
            'method' => 'Banka',
            'date' => '2024-02-08',
        ]);

        $this->invoice->refreshPaymentStatus();

        $response = $this->actingAs($this->admin)
            ->from(route('payments.create', ['invoice_id' => $this->invoice->id, 'firm_id' => $this->firm->id]))
            ->post(route('payments.store'), [
                '_token' => $this->csrfToken,
                'firm_id' => $this->firm->id,
                'invoice_id' => $this->invoice->id,
                'amount' => 500,
                'method' => 'Nakit',
                'date' => '2024-02-15',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('amount');

        $invoice = $this->invoice->fresh();
        $this->assertSame('partial', $invoice->status);
        $this->assertEquals(800, $invoice->payments()->sum('amount'));
    }

    public function test_payment_method_must_be_from_settings(): void
    {
        Setting::setPaymentMethods(['Havale']);

        $response = $this->actingAs($this->admin)
            ->from(route('payments.create', ['firm_id' => $this->firm->id]))
            ->post(route('payments.store'), [
                '_token' => $this->csrfToken,
                'firm_id' => $this->firm->id,
                'invoice_id' => $this->invoice->id,
                'amount' => 200,
                'method' => 'Nakit',
                'date' => '2024-02-12',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('method');
    }

    public function test_removing_payment_reverts_invoice_status(): void
    {
        $this->actingAs($this->admin)
            ->post(route('payments.store'), [
                '_token' => $this->csrfToken,
                'firm_id' => $this->firm->id,
                'invoice_id' => $this->invoice->id,
                'amount' => 500,
                'method' => 'Banka',
                'date' => '2024-02-10',
            ])
            ->assertRedirect(route('payments.index'));

        $payment = Payment::latest('id')->first();

        $this->invoice = $this->invoice->fresh();
        $this->assertSame('partial', $this->invoice->status);

        $this->actingAs($this->admin)
            ->delete(route('payments.destroy', $payment), [
                '_token' => $this->csrfToken,
            ])
            ->assertRedirect(route('payments.index'));

        $invoice = $this->invoice->fresh();
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertEquals(0, $invoice->payments()->sum('amount'));
        $this->assertSame('unpaid', $invoice->status);
    }
}
