<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\TaxDeclaration;
use App\Models\TaxForm;
use App\Models\FirmTaxForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicatePreventionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Firm $firm;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'is_active' => true,
            'role' => 'admin', // Admin olmalı
        ]);
        $this->firm = Firm::factory()->create([
            'status' => 'active',
            'company_type' => 'limited',
            'monthly_fee' => 1000,
        ]);
    }

    // ========== FATURA DUPLICATE KONTROLLARI ==========

    public function test_invoice_official_number_must_be_unique(): void
    {
        $this->actingAs($this->user);

        // İlk faturayı oluştur
        $invoice1 = Invoice::create([
            'firm_id' => $this->firm->id,
            'date' => now(),
            'due_date' => now()->addDays(30),
            'amount' => 1000,
            'description' => 'Test fatura 1',
            'official_number' => 'INV-001',
            'status' => 'unpaid',
        ]);

        // Aynı official_number ile ikinci fatura dene - validation hatası bekle
        $response = $this->post(route('invoices.store'), [
            'firm_id' => $this->firm->id,
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'amount' => 2000,
            'description' => 'Test fatura 2',
            'official_number' => 'INV-001', // Aynı numara
        ]);

        $response->assertSessionHasErrors('official_number');
        
        // Veritabanında sadece 1 fatura olmalı
        $this->assertEquals(1, Invoice::where('official_number', 'INV-001')->count());
    }

    public function test_invoice_update_allows_same_official_number(): void
    {
        $this->actingAs($this->user);

        // Fatura oluştur
        $invoice = Invoice::create([
            'firm_id' => $this->firm->id,
            'date' => now(),
            'due_date' => now()->addDays(30),
            'amount' => 1000,
            'description' => 'Test fatura',
            'official_number' => 'INV-001',
            'status' => 'unpaid',
        ]);

        // Aynı official_number ile güncelleme yapabilmeli
        $response = $this->put(route('invoices.update', $invoice), [
            'firm_id' => $this->firm->id,
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'amount' => 1500, // Tutum değişti
            'description' => 'Güncellenmiş fatura',
            'official_number' => 'INV-001', // Aynı numara kalabilir
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'amount' => 1500,
            'official_number' => 'INV-001',
        ]);
    }

    public function test_monthly_invoice_sync_skips_existing_invoices(): void
    {
        $this->actingAs($this->user);

        $month = now()->startOfMonth();

        // Bu ay için manuel fatura oluştur
        Invoice::create([
            'firm_id' => $this->firm->id,
            'date' => $month,
            'due_date' => $month->copy()->addDays(30),
            'amount' => 1000,
            'description' => 'Manuel fatura',
            'status' => 'unpaid',
        ]);

        // Aylık senkron çağır
        $response = $this->post(route('invoices.sync-monthly'), [
            'month' => $month->format('Y-m'),
        ]);

        $response->assertRedirect();

        // Aynı ay için sadece 1 fatura olmalı (yeni oluşturulmamalı)
        $invoiceCount = Invoice::where('firm_id', $this->firm->id)
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->count();
            
        $this->assertEquals(1, $invoiceCount);
    }

    // ========== BEYANNAME DUPLICATE KONTROLLARI ==========

    public function test_tax_declaration_duplicate_is_prevented(): void
    {
        // Vergi formu oluştur
        $taxForm = TaxForm::create([
            'code' => 'KDV',
            'name' => 'Katma Değer Vergisi',
            'frequency' => 'monthly',
            'default_due_day' => 26,
            'is_active' => true,
        ]);

        // Firmaya ata
        FirmTaxForm::create([
            'firm_id' => $this->firm->id,
            'tax_form_id' => $taxForm->id,
            'is_active' => true,
        ]);

        $periodStart = now()->startOfMonth()->subMonth();
        $periodEnd = now()->startOfMonth()->subMonth()->endOfMonth();

        // İlk beyanname oluştur
        TaxDeclaration::create([
            'firm_id' => $this->firm->id,
            'tax_form_id' => $taxForm->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'period_label' => $periodStart->format('m/Y'),
            'due_date' => now(),
            'status' => 'pending',
        ]);

        // Command çalıştır
        $this->artisan('app:generate-tax-declarations', [
            '--month' => now()->format('Y-m'),
        ])->assertSuccessful();

        // Aynı dönem için sadece 1 beyanname olmalı
        $declarationCount = TaxDeclaration::where('firm_id', $this->firm->id)
            ->where('tax_form_id', $taxForm->id)
            ->whereDate('period_start', $periodStart)
            ->count();
            
        $this->assertEquals(1, $declarationCount);
    }

    public function test_generate_declarations_command_counts_duplicates(): void
    {
        // Vergi formu oluştur
        $taxForm = TaxForm::create([
            'code' => 'MUHTASAR',
            'name' => 'Muhtasar ve Prim Hizmet',
            'frequency' => 'monthly',
            'default_due_day' => 17,
            'is_active' => true,
        ]);

        // Firmaya ata
        FirmTaxForm::create([
            'firm_id' => $this->firm->id,
            'tax_form_id' => $taxForm->id,
            'is_active' => true,
        ]);

        // İlk çalıştırma
        $this->artisan('app:generate-tax-declarations', [
            '--month' => now()->format('Y-m'),
        ])->assertSuccessful();

        $firstCount = TaxDeclaration::count();

        // İkinci çalıştırma - aynı sayıda kalmalı
        $this->artisan('app:generate-tax-declarations', [
            '--month' => now()->format('Y-m'),
        ])->assertSuccessful();

        $secondCount = TaxDeclaration::count();

        $this->assertEquals($firstCount, $secondCount);
    }

    // ========== VERGİ FORMU DUPLICATE KONTROLLARI ==========

    public function test_tax_form_code_must_be_unique(): void
    {
        $this->actingAs($this->user);

        // İlk vergi formu
        TaxForm::create([
            'code' => 'TEST001',
            'name' => 'Test Formu 1',
            'frequency' => 'monthly',
            'default_due_day' => 15,
            'is_active' => true,
        ]);

        // Aynı kod ile ikinci form deneme
        $response = $this->post(route('settings.tax-forms.store'), [
            'code' => 'TEST001', // Aynı kod
            'name' => 'Test Formu 2',
            'frequency' => 'monthly',
            'default_due_day' => 20,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('code');
        
        // Sadece 1 form olmalı
        $this->assertEquals(1, TaxForm::where('code', 'TEST001')->count());
    }

    // ========== KULLANICI DUPLICATE KONTROLLARI ==========

    public function test_user_email_must_be_unique(): void
    {
        $this->actingAs($this->user);

        // İkinci kullanıcı oluşturma denemesi - aynı email
        $response = $this->post(route('users.store'), [
            'name' => 'Yeni Kullanıcı',
            'email' => $this->user->email, // Aynı email
            'role' => 'user',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('email');
    }
}
