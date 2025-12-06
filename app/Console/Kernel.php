<?php

namespace App\Console;

use App\Console\Commands\BackupPreviewCommand;
use App\Console\Commands\EnsureLocalAdmin;
use App\Console\Commands\GenerateMonthlyInvoices;
use App\Console\Commands\GenerateTaxDeclarations;
use App\Console\Commands\SyncHistoricalInvoices;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:generate-monthly-invoices', ['--month' => now()->format('Y-m')])
            ->monthlyOn(1, '01:00');

        $schedule->command('app:generate-tax-declarations', ['--month' => now()->format('Y-m')])
            ->monthlyOn(1, '02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        $this->commands([
            BackupPreviewCommand::class,
            GenerateMonthlyInvoices::class,
            GenerateTaxDeclarations::class,
            EnsureLocalAdmin::class,
            SyncHistoricalInvoices::class,
        ]);
    }
}
