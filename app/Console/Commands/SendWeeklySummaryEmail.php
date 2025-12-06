<?php

namespace App\Console\Commands;

use App\Mail\WeeklySummary;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklySummaryEmail extends Command
{
    protected $signature = 'email:weekly-summary {--force : E-posta ayarları kapalı olsa bile gönder}';

    protected $description = 'Haftalık özet e-postasını gönderir';

    public function handle(): int
    {
        // Haftalık özet aktif mi kontrol et
        $enabled = Setting::getValue('enable_weekly_summary', '0') === '1';
        
        if (!$enabled && !$this->option('force')) {
            $this->info('Haftalık özet bildirimi devre dışı.');
            return self::SUCCESS;
        }

        // Alıcıları al
        $recipients = Setting::getNotificationRecipients();
        
        if (empty($recipients)) {
            $this->warn('Bildirim alıcısı tanımlı değil.');
            return self::SUCCESS;
        }

        try {
            foreach ($recipients as $email) {
                Mail::to($email)->send(new WeeklySummary());
                $this->info("E-posta gönderildi: {$email}");
            }

            Log::info('Haftalık özet e-postası gönderildi', [
                'recipients' => $recipients,
            ]);

            $this->info('Haftalık özet e-postası başarıyla gönderildi.');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('E-posta gönderilirken hata: ' . $e->getMessage());
            Log::error('Haftalık özet e-postası gönderilemedi', [
                'error' => $e->getMessage(),
            ]);
            return self::FAILURE;
        }
    }
}
