<?php

namespace Tests\Feature;

use App\Mail\MonthlyInvoiceSummary;
use App\Models\Firm;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GenerateMonthlyInvoicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_summary_email_when_enabled(): void
    {
        Mail::fake();

        Setting::setValue('invoice_auto_notify', '1');
        Setting::setInvoiceNotificationRecipients(['finance@example.com']);

        $firm = Firm::create([
            'name' => 'Cron Test Firma',
            'monthly_fee' => 1500,
            'status' => 'active',
            'contract_start_at' => Carbon::parse('2023-01-01'),
        ]);

        $this->artisan('app:generate-monthly-invoices', ['--month' => '2024-05'])
            ->assertExitCode(0);

        // Verify invoice was created
        $this->assertDatabaseHas('invoices', [
            'firm_id' => $firm->id,
        ]);

        // Verify mail was sent
        Mail::assertSent(MonthlyInvoiceSummary::class, function (MonthlyInvoiceSummary $mail) use ($firm) {
            return $mail->createdCount === 1
                && $mail->period->format('Y-m') === '2024-05'
                && $mail->invoices[0]['firm_name'] === $firm->name
                && $mail->totalAmount === 1500.0;
        });
    }
}
