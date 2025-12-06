<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\InvoiceExtraField;
use App\Models\InvoiceExtraValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class InvoiceExtraFieldsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_invoice_with_optional_extra_field_value()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $firm = Firm::factory()->create();
        $field = InvoiceExtraField::create([
            'firm_id' => $firm->id,
            'name' => 'donem',
            'label' => 'DÃ¶nem',
            'type' => 'text',
            'options' => null,
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $payload = [
            'firm_id' => $firm->id,
            'date' => '2025-01-01',
            'amount' => 1000,
            'description' => 'Test fatura',
            'extra_fields' => [
                $field->id => 'Ocak 2025',
            ],
        ];

        $response = $this->post(route('invoices.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'firm_id' => $firm->id,
            'description' => 'Test fatura',
        ]);

        $invoice = Invoice::latest('id')->first();

        $this->assertDatabaseHas('invoice_extra_values', [
            'invoice_id' => $invoice->id,
            'extra_field_id' => $field->id,
            'value' => 'Ocak 2025',
        ]);
    }

    #[Test]
    public function it_requires_required_extra_field_on_invoice_creation()
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $firm = Firm::factory()->create();

        $field = InvoiceExtraField::create([
            'firm_id' => $firm->id,
            'name' => 'donem',
            'label' => 'DÃ¶nem',
            'type' => 'text',
            'options' => null,
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $payload = [
            'firm_id' => $firm->id,
            'date' => '2025-01-01',
            'amount' => 1000,
            'description' => 'Test fatura',
            // extra_fields intentionally missing
        ];

        $response = $this->post(route('invoices.store'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('extra_fields.' . $field->id);

        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('invoice_extra_values', 0);
    }

    #[Test]
    public function it_updates_extra_field_values_on_invoice_update()
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $firm = Firm::factory()->create();

        $field = InvoiceExtraField::create([
            'firm_id' => $firm->id,
            'name' => 'donem',
            'label' => 'DÃ¶nem',
            'type' => 'text',
            'options' => null,
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $invoice = Invoice::factory()->create([
            'firm_id' => $firm->id,
            'amount' => 1000,
            'date' => Carbon::parse('2025-01-01'),
        ]);

        InvoiceExtraValue::create([
            'invoice_id' => $invoice->id,
            'extra_field_id' => $field->id,
            'value' => 'Eski DeÄŸer',
        ]);

        $payload = [
            'firm_id' => $firm->id,
            'date' => '2025-01-02',
            'amount' => 1500,
            'description' => 'GÃ¼ncellenmiÅŸ fatura',
            'extra_fields' => [
                $field->id => 'Yeni DeÄŸer',
            ],
        ];

        $response = $this->put(route('invoices.update', $invoice), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('invoice_extra_values', [
            'invoice_id' => $invoice->id,
            'extra_field_id' => $field->id,
            'value' => 'Yeni DeÄŸer',
        ]);
    }

    #[Test]
    public function paid_invoices_cannot_be_edited_or_deleted()
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $firm = Firm::factory()->create();

        $invoice = Invoice::factory()->create([
            'firm_id' => $firm->id,
            'status' => 'paid',
            'amount' => 1000,
            'date' => Carbon::parse('2025-01-01'),
        ]);

        $editResponse = $this->get(route('invoices.edit', $invoice));
        $editResponse->assertRedirect(route('invoices.show', $invoice));
        $editResponse->assertSessionHasErrors('invoice');

        $deleteResponse = $this->delete(route('invoices.destroy', $invoice));
        $deleteResponse->assertRedirect(route('invoices.show', $invoice));
        $deleteResponse->assertSessionHasErrors('invoice');

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }
}
