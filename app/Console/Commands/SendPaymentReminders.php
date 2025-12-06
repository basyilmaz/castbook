<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminder;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminders extends Command
{
    protected $signature = 'email:payment-reminders {--force : E-posta ayarları kapalı olsa bile gönder}';

    protected $description = 'Vadesi yaklaşan ve gecikmiş faturalar için hatırlatma e-postaları gönderir';

    public function handle(): int
    {
        // Ödeme hatırlatmaları aktif mi kontrol et
        $enabled = Setting::getValue('enable_payment_reminders', '1') === '1';
        
        if (!$enabled && !$this->option('force')) {
            $this->info('Ödeme hatırlatma bildirimi devre dışı.');
            return self::SUCCESS;
        }

        // Alıcıları al
        $recipients = Setting::getNotificationRecipients();
        
        if (empty($recipients)) {
            $this->warn('Bildirim alıcısı tanımlı değil.');
            return self::SUCCESS;
        }

        $reminderDays = (int) Setting::getValue('payment_reminder_days', 3);

        // Vadesi yaklaşan faturalar
        $upcomingInvoices = Invoice::with('firm')
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereBetween('due_date', [
                now()->toDateString(),
                now()->addDays($reminderDays)->toDateString()
            ])
            ->get();

        // Gecikmiş faturalar
        $overdueInvoices = Invoice::with('firm')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', now()->toDateString())
            ->get();

        if ($upcomingInvoices->isEmpty() && $overdueInvoices->isEmpty()) {
            $this->info('Hatırlatılacak fatura bulunamadı.');
            return self::SUCCESS;
        }

        try {
            $sentCount = 0;

            // Vadesi yaklaşan faturalar için e-posta
            if ($upcomingInvoices->isNotEmpty()) {
                foreach ($recipients as $email) {
                    Mail::to($email)->send(new PaymentReminder(
                        invoices: $upcomingInvoices,
                        firmName: 'Genel',
                        totalAmount: $upcomingInvoices->sum('amount'),
                        type: 'upcoming'
                    ));
                    $sentCount++;
                }
                $this->info("Vadesi yaklaşan {$upcomingInvoices->count()} fatura için hatırlatma gönderildi.");
            }

            // Gecikmiş faturalar için e-posta
            if ($overdueInvoices->isNotEmpty()) {
                foreach ($recipients as $email) {
                    Mail::to($email)->send(new PaymentReminder(
                        invoices: $overdueInvoices,
                        firmName: 'Genel',
                        totalAmount: $overdueInvoices->sum('amount'),
                        type: 'overdue'
                    ));
                    $sentCount++;
                }
                $this->info("Gecikmiş {$overdueInvoices->count()} fatura için hatırlatma gönderildi.");
            }

            Log::info('Ödeme hatırlatma e-postaları gönderildi', [
                'upcoming_count' => $upcomingInvoices->count(),
                'overdue_count' => $overdueInvoices->count(),
                'recipients' => $recipients,
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('E-posta gönderilirken hata: ' . $e->getMessage());
            Log::error('Ödeme hatırlatma e-postası gönderilemedi', [
                'error' => $e->getMessage(),
            ]);
            return self::FAILURE;
        }
    }
}
