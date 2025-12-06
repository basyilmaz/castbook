<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InvoiceOfficialNumberTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Firm $firm;
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
            'name' => 'Fatura Test Firma',
            'status' => 'active',
            'monthly_fee' => 1500,
            'contract_start_at' => Carbon::parse('2024-01-01'),
        ]);
    }

    public function test_official_number_is_saved_during_creation(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('invoices.store'), [
                '_token' => $this->csrfToken,
                'firm_id' => $this->firm->id,
                'date' => '2024-03-01',
                'due_date' => '2024-03-15',
                'amount' => 1500,
                'official_number' => '2024-INV-001',
                'description' => 'Mart dÃ¶nemi',
            ]);

        $invoice = Invoice::latest('id')->first();

        $response->assertRedirect(route('invoices.show', $invoice));
        $this->assertNotNull($invoice);
        $this->assertSame('2024-INV-001', $invoice->official_number);
    }

    public function test_official_number_must_be_unique(): void
    {
        $existing = Invoice::create([
            'firm_id' => $this->firm->id,
            'date' => Carbon::parse('2024-01-01'),
            'due_date' => Carbon::parse('2024-01-15'),
            'amount' => 1500,
            'official_number' => '2024-INV-999',
            'status' => 'unpaid',
        ]);

        $invoice = Invoice::create([
            'firm_id' => $this->firm->id,
            'date' => Carbon::parse('2024-02-01'),
            'due_date' => Carbon::parse('2024-02-15'),
            'amount' => 1500,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('invoices.edit', $invoice))
            ->put(route('invoices.update', $invoice), [
                '_token' => $this->csrfToken,
                'firm_id' => $this->firm->id,
                'date' => '2024-02-01',
                'due_date' => '2024-02-15',
                'amount' => 1500,
                'official_number' => $existing->official_number,
                'description' => 'Åubat dÃ¶nemi',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('official_number');
        $this->assertNull($invoice->fresh()->official_number);
    }
}
