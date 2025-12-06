<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\TaxDeclaration;
use App\Models\TaxForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TaxDeclarationsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lists_declarations_with_filters(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $firmA = Firm::factory()->create(['name' => 'Firma A']);
        $firmB = Firm::factory()->create(['name' => 'Firma B']);
        $form = TaxForm::factory()->create([
            'code' => 'KDV1',
            'name' => 'KDV Beyannamesi',
            'frequency' => 'monthly',
            'default_due_day' => 26,
        ]);

        TaxDeclaration::factory()->create([
            'firm_id' => $firmA->id,
            'tax_form_id' => $form->id,
            'period_label' => '2025-01',
        ]);
        TaxDeclaration::factory()->create([
            'firm_id' => $firmB->id,
            'tax_form_id' => $form->id,
            'period_label' => '2025-02',
        ]);

        $response = $this->get(route('tax-declarations.index', [
            'firm_id' => $firmA->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('2025-01');
        $response->assertDontSee('2025-02');
    }

    #[Test]
    public function it_updates_status_to_filed_and_sets_filed_at(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $firm = Firm::factory()->create();
        $form = TaxForm::factory()->create([
            'code' => 'MUH',
            'name' => 'Muhtasar',
            'frequency' => 'monthly',
            'default_due_day' => 26,
        ]);

        $declaration = TaxDeclaration::factory()->create([
            'firm_id' => $firm->id,
            'tax_form_id' => $form->id,
            'status' => 'pending',
            'filed_at' => null,
        ]);

        $response = $this->put(route('tax-declarations.update', $declaration), [
            'status' => 'filed',
            'filed_at' => null,
            'paid_at' => null,
            'notes' => 'Test filed',
        ]);

        $response->assertRedirect(route('tax-declarations.index'));

        $this->assertDatabaseHas('tax_declarations', [
            'id' => $declaration->id,
            'status' => 'filed',
        ]);

        $this->assertNotNull(TaxDeclaration::find($declaration->id)->filed_at);
    }

    #[Test]
    public function it_marks_declaration_as_not_required(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $firm = Firm::factory()->create();
        $form = TaxForm::factory()->create([
            'code' => 'GEKAP',
            'name' => 'GEKAP',
            'frequency' => 'quarterly',
            'default_due_day' => 20,
        ]);

        $declaration = TaxDeclaration::factory()->create([
            'firm_id' => $firm->id,
            'tax_form_id' => $form->id,
            'status' => 'pending',
        ]);

        $response = $this->put(route('tax-declarations.update', $declaration), [
            'status' => 'not_required',
            'filed_at' => null,
            'paid_at' => null,
            'notes' => 'Gerekli deÄŸil',
        ]);

        $response->assertRedirect(route('tax-declarations.index'));

        $this->assertDatabaseHas('tax_declarations', [
            'id' => $declaration->id,
            'status' => 'not_required',
        ]);
    }
}
