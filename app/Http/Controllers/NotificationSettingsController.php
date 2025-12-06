<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReminder;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class NotificationSettingsController extends Controller
{
    /**
     * Bildirim ayarları formunu göster
     */
    public function edit(): View
    {
        $settings = [
            'enable_email_notifications' => (bool) Setting::getValue('enable_email_notifications', '0'),
            'notification_recipients' => Setting::getValue('notification_recipients', ''),
            'enable_payment_reminders' => (bool) Setting::getValue('enable_payment_reminders', '1'),
            'payment_reminder_days' => (int) Setting::getValue('payment_reminder_days', 3),
            'overdue_reminder_frequency' => Setting::getValue('overdue_reminder_frequency', 'daily'),
            'enable_declaration_reminders' => (bool) Setting::getValue('enable_declaration_reminders', '1'),
            'declaration_reminder_days' => (int) Setting::getValue('declaration_reminder_days', 3),
            'notification_time' => Setting::getValue('notification_time', '09:00'),
            'enable_weekly_summary' => (bool) Setting::getValue('enable_weekly_summary', '0'),
            'weekly_summary_day' => Setting::getValue('weekly_summary_day', 'monday'),
        ];

        return view('settings.notifications', compact('settings'));
    }

    /**
     * Bildirim ayarlarını güncelle
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enable_email_notifications' => ['nullable'],
            'notification_recipients' => ['nullable', 'string'],
            'enable_payment_reminders' => ['nullable'],
            'payment_reminder_days' => ['required', 'integer', 'min:1', 'max:30'],
            'overdue_reminder_frequency' => ['required', 'in:daily,weekly,once'],
            'enable_declaration_reminders' => ['nullable'],
            'declaration_reminder_days' => ['required', 'integer', 'min:1', 'max:30'],
            'notification_time' => ['required', 'string'],
        ], [
            'payment_reminder_days.required' => 'Ödeme hatırlatma günü zorunludur.',
            'declaration_reminder_days.required' => 'Beyanname hatırlatma günü zorunludur.',
        ]);

        // E-posta bildirimleri durumu
        Setting::setValue('enable_email_notifications', $request->boolean('enable_email_notifications') ? '1' : '0');

        // Alıcıları temizle ve kaydet
        $recipients = collect(preg_split('/[\r\n,]+/', $data['notification_recipients'] ?? '', -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($email) => trim($email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->implode("\n");
        Setting::setValue('notification_recipients', $recipients);

        // Ödeme hatırlatma ayarları
        Setting::setValue('enable_payment_reminders', $request->boolean('enable_payment_reminders') ? '1' : '0');
        Setting::setValue('payment_reminder_days', (string) $data['payment_reminder_days']);
        Setting::setValue('overdue_reminder_frequency', $data['overdue_reminder_frequency']);

        // Beyanname hatırlatma ayarları
        Setting::setValue('enable_declaration_reminders', $request->boolean('enable_declaration_reminders') ? '1' : '0');
        Setting::setValue('declaration_reminder_days', (string) $data['declaration_reminder_days']);

        // Gönderim zamanı
        Setting::setValue('notification_time', $data['notification_time']);

        // Haftalık özet ayarları
        Setting::setValue('enable_weekly_summary', $request->boolean('enable_weekly_summary') ? '1' : '0');
        Setting::setValue('weekly_summary_day', $request->input('weekly_summary_day', 'monday'));

        return redirect()
            ->route('settings.notifications')
            ->with('status', 'Bildirim ayarları güncellendi.');
    }

    /**
     * Test e-postası gönder
     */
    public function sendTest(Request $request): RedirectResponse
    {
        $recipients = Setting::getNotificationRecipients();

        if (empty($recipients)) {
            return back()->withErrors(['test' => 'Bildirim alıcısı tanımlı değil.']);
        }

        try {
            // Örnek test verisi
            $testInvoices = Invoice::with('firm')
                ->whereIn('status', ['unpaid', 'partial'])
                ->limit(3)
                ->get();

            if ($testInvoices->isEmpty()) {
                // Boş koleksiyon ile test gönder
                $testInvoices = collect([
                    (object) [
                        'firm' => (object) ['name' => 'Örnek Firma A.Ş.'],
                        'official_number' => 'TEST-001',
                        'date' => now(),
                        'due_date' => now()->addDays(5),
                        'amount' => 3500.00,
                    ]
                ]);
            }

            foreach ($recipients as $email) {
                Mail::to($email)->send(new PaymentReminder(
                    invoices: $testInvoices,
                    firmName: 'Test Bildirimi',
                    totalAmount: $testInvoices->sum('amount'),
                    type: 'upcoming'
                ));
            }

            return back()->with('status', 'Test e-postası başarıyla gönderildi: ' . implode(', ', $recipients));
        } catch (\Exception $e) {
            return back()->withErrors(['test' => 'E-posta gönderilirken hata oluştu: ' . $e->getMessage()]);
        }
    }
}
