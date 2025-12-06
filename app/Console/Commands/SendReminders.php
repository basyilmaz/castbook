<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminder;
use App\Mail\TaxDeclarationReminder;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\TaxDeclaration;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendReminders extends Command
{
    protected $signature = 'app:send-reminders 
                            {--type=all : Bildirim tipi (all, payments, declarations)}
                            {--days=3 : Kaç gün önceden hatırlat}';

    protected $description = 'Ödeme ve beyanname hatırlatma e-postaları gönderir';

    public function handle(): int
    {
        $type = $this->option('type');
        $days = (int) $this->option('days');

        // Bildirim ayarı kontrolü
        $notificationsEnabled = Setting::getValue('enable_email_notifications', '0') === '1';
        if (!$notificationsEnabled) {
            $this->warn('E-posta bildirimleri devre dışı. Ayarlardan aktifleştirebilirsiniz.');
            return self::SUCCESS;
        }

        $recipients = $this->getRecipients();
        if (empty($recipients)) {
            $this->error('Bildirim alıcısı bulunamadı.');
            return self::FAILURE;
        }

        $this->info("Bildirimler gönderiliyor: " . implode(', ', $recipients));

        if ($type === 'all' || $type === 'payments') {
            $this->sendPaymentReminders($recipients, $days);
        }

        if ($type === 'all' || $type === 'declarations') {
            $this->sendDeclarationReminders($recipients, $days);
        }

        $this->info('Bildirimler başarıyla gönderildi.');
        return self::SUCCESS;
    }

    protected function getRecipients(): array
    {
        $recipients = Setting::getValue('notification_recipients', '');
        
        if (empty($recipients)) {
            $defaultEmail = config('mail.from.address');
            return $defaultEmail ? [$defaultEmail] : [];
        }

        return array_filter(array_map('trim', explode(',', $recipients)));
    }

    protected function sendPaymentReminders(array $recipients, int $days): void
    {
        $today = Carbon::today();
        $futureDate = $today->copy()->addDays($days);

        // Gecikmiş faturalar
        $overdueInvoices = Invoice::with('firm')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where(function ($query) use ($today) {
                $query->whereNotNull('due_date')
                    ->where('due_date', '<', $today);
            })
            ->orderBy('due_date')
            ->get();

        if ($overdueInvoices->isNotEmpty()) {
            // Firma bazında grupla
            $grouped = $overdueInvoices->groupBy('firm_id');
            
            foreach ($grouped as $firmId => $invoices) {
                $firmName = $invoices->first()->firm->name ?? 'Bilinmeyen Firma';
                $totalAmount = $invoices->sum('amount');

                Mail::to($recipients)->send(new PaymentReminder(
                    $invoices,
                    $firmName,
                    $totalAmount,
                    'overdue'
                ));
            }

            $this->info("Gecikmiş ödeme hatırlatması: {$overdueInvoices->count()} fatura");
        }

        // Yaklaşan vadeler
        $upcomingInvoices = Invoice::with('firm')
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$today, $futureDate])
            ->orderBy('due_date')
            ->get();

        if ($upcomingInvoices->isNotEmpty()) {
            $grouped = $upcomingInvoices->groupBy('firm_id');
            
            foreach ($grouped as $firmId => $invoices) {
                $firmName = $invoices->first()->firm->name ?? 'Bilinmeyen Firma';
                $totalAmount = $invoices->sum('amount');

                Mail::to($recipients)->send(new PaymentReminder(
                    $invoices,
                    $firmName,
                    $totalAmount,
                    'upcoming'
                ));
            }

            $this->info("Yaklaşan ödeme hatırlatması: {$upcomingInvoices->count()} fatura");
        }
    }

    protected function sendDeclarationReminders(array $recipients, int $days): void
    {
        $today = Carbon::today();
        $futureDate = $today->copy()->addDays($days);

        // Gecikmiş beyannameler
        $overdueDeclarations = TaxDeclaration::with(['firm', 'taxForm'])
            ->whereIn('status', ['pending', 'filed'])
            ->where('due_date', '<', $today)
            ->orderBy('due_date')
            ->get();

        if ($overdueDeclarations->isNotEmpty()) {
            Mail::to($recipients)->send(new TaxDeclarationReminder(
                $overdueDeclarations,
                0,
                'overdue'
            ));
            $this->info("Gecikmiş beyanname hatırlatması: {$overdueDeclarations->count()} beyanname");
        }

        // Yaklaşan beyannameler
        $upcomingDeclarations = TaxDeclaration::with(['firm', 'taxForm'])
            ->whereIn('status', ['pending', 'filed'])
            ->whereBetween('due_date', [$today, $futureDate])
            ->orderBy('due_date')
            ->get();

        if ($upcomingDeclarations->isNotEmpty()) {
            Mail::to($recipients)->send(new TaxDeclarationReminder(
                $upcomingDeclarations,
                $days,
                'upcoming'
            ));
            $this->info("Yaklaşan beyanname hatırlatması: {$upcomingDeclarations->count()} beyanname");
        }
    }
}
