<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$invoiceDay = 1;
$weeklySummaryDay = 'monday';

try {
    if (Schema::hasTable('settings')) {
        $invoiceDay = (int) Setting::getValue('invoice_day', '1');
        $weeklySummaryDay = Setting::getValue('weekly_summary_day', 'monday');
    }
} catch (\Throwable $exception) {
    $invoiceDay = 1;
    $weeklySummaryDay = 'monday';
}

// Aylık fatura oluşturma
Schedule::command('app:generate-monthly-invoices')
    ->monthlyOn($invoiceDay, '08:00')
    ->description('Aktif firmalar icin aylik fatura olusturur');

// Günlük ödeme hatırlatma e-postaları (sabah 09:00'da)
Schedule::command('email:payment-reminders')
    ->dailyAt('09:00')
    ->description('Vadesi yaklaşan ve gecikmiş faturalar için hatırlatma gönderir');

// Beyanname otomatik oluşturma (her ayın 1'inde)
Schedule::command('app:generate-tax-declarations')
    ->monthlyOn(1, '08:30')
    ->description('Aylık beyannameleri otomatik oluşturur');

// Haftalık özet e-postası (seçilen günde sabah 09:00'da)
// 0 = Sunday, 1 = Monday, ... 6 = Saturday
$weekDayMap = [
    'sunday' => 0,
    'monday' => 1,
    'tuesday' => 2,
    'wednesday' => 3,
    'thursday' => 4,
    'friday' => 5,
    'saturday' => 6,
];

Schedule::command('email:weekly-summary')
    ->weeklyOn($weekDayMap[$weeklySummaryDay] ?? 1, '09:00')
    ->description('Haftalık özet e-postası gönderir');
