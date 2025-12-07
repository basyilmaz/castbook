<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FirmController;
use App\Http\Controllers\FirmPriceHistoryController;
use App\Http\Controllers\FirmStatementController;
use App\Http\Controllers\FirmTaxFormController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceExtraFieldController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaxDeclarationController;
use App\Http\Controllers\TaxFormController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.attempt');
    
    // Demo Hesap Sayfası
    Route::get('/demo', fn () => view('auth.demo'))->name('demo');
    
    // 2FA Challenge (giriş sırasında)
    Route::get('/two-factor/challenge', [\App\Http\Controllers\TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('two-factor.verify');
    
    // Şifre Sıfırlama (rate limited)
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:password-reset')
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->middleware('throttle:password-reset')
        ->name('password.update');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now()->toIso8601String(),
    ]);
});

// Debug route - session ve auth durumunu göster
Route::get('/debug-session', function () {
    $user = \App\Models\User::where('email', 'muhasebe@example.com')->first();
    $passwordValid = $user ? \Illuminate\Support\Facades\Hash::check('Parola123!', $user->password) : null;
    
    return response()->json([
        'authenticated' => \Illuminate\Support\Facades\Auth::check(),
        'user' => \Illuminate\Support\Facades\Auth::user()?->email,
        'session_id' => session()->getId(),
        'session_driver' => config('session.driver'),
        'test_user_exists' => $user ? true : false,
        'test_password_valid' => $passwordValid,
        'test_user_active' => $user?->is_active,
    ]);
});

// Test login - doğrudan login yap ve dashboard'a yönlendir
Route::get('/test-login', function () {
    $user = \App\Models\User::where('email', 'muhasebe@example.com')->first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user, true);
        return redirect('/debug-session');
    }
    return 'User not found';
});

// Sitemap for SEO
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Global Arama
    Route::get('/search', [SearchController::class, 'search'])->name('search');
    
    // Bildirimler
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    
    // Yardım & Kılavuz
    Route::get('/help', fn () => view('help.guide'))->name('help');
    Route::get('/faq', fn () => view('help.faq'))->name('faq');
    Route::get('/admin-guide', fn () => view('help.admin-guide'))
        ->middleware('role:admin')
        ->name('admin-guide');
    
    // Onboarding
    Route::get('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding/complete', [\App\Http\Controllers\OnboardingController::class, 'complete'])->name('onboarding.complete');
    
    // Audit Log (Admin only)
    Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])
        ->middleware('role:admin')
        ->name('audit-logs.index');

    // 2FA (Two-Factor Authentication)
    Route::get('/two-factor', [\App\Http\Controllers\TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::get('/two-factor/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirmShow'])->name('two-factor.confirm');
    Route::post('/two-factor/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm']);
    Route::delete('/two-factor/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::post('/two-factor/recovery-codes', [\App\Http\Controllers\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes');

    Route::post('firms/{firm}/sync-invoices', [FirmController::class, 'syncInvoices'])->name('firms.sync-invoices');
    Route::post('firms/{firm}/statement', [FirmStatementController::class, 'generate'])->name('firms.statement');
    Route::post('firms/{firm}/price-histories', [FirmPriceHistoryController::class, 'store'])->name('firms.price-histories.store');
    Route::delete('firms/{firm}/price-histories/{priceHistory}', [FirmPriceHistoryController::class, 'destroy'])->name('firms.price-histories.destroy');
    Route::get('firms/{firm}/tax-forms', [FirmTaxFormController::class, 'index'])->name('firms.tax-forms.index');
    Route::post('firms/{firm}/tax-forms', [FirmTaxFormController::class, 'store'])->name('firms.tax-forms.store');
    Route::post('invoices/sync-monthly', [InvoiceController::class, 'syncMonthly'])->name('invoices.sync-monthly');
    
    // Firma Import
    Route::get('firms/import', [\App\Http\Controllers\FirmImportController::class, 'showImportForm'])->name('firms.import');
    Route::post('firms/import', [\App\Http\Controllers\FirmImportController::class, 'import'])->name('firms.import.process');
    Route::get('firms/import/template', [\App\Http\Controllers\FirmImportController::class, 'downloadTemplate'])->name('firms.import.template');
    
    // Firma Beyanname Özeti
    Route::get('firms/{firm}/declarations', [FirmController::class, 'declarations'])->name('firms.declarations');
    
    Route::resource('firms', FirmController::class);
    
    // Fatura Import (resource'dan önce tanımlanmalı)
    Route::get('invoices/import', [\App\Http\Controllers\InvoiceImportController::class, 'showForm'])->name('invoices.import.form');
    Route::post('invoices/import', [\App\Http\Controllers\InvoiceImportController::class, 'import'])->name('invoices.import');
    Route::get('invoices/import/template', [\App\Http\Controllers\InvoiceImportController::class, 'downloadTemplate'])->name('invoices.import.template');
    
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
    Route::delete('invoices/bulk-destroy', [InvoiceController::class, 'bulkDestroy'])->name('invoices.bulk-destroy');
    Route::patch('invoices/bulk-status', [InvoiceController::class, 'bulkUpdateStatus'])->name('invoices.bulk-status');
    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/backup/download', [SettingsController::class, 'downloadBackup'])->name('settings.backup.download');
    Route::post('settings/backup/restore', [SettingsController::class, 'restoreBackup'])->name('settings.backup.restore');
    Route::get('settings/logo/{path}', [SettingsController::class, 'logo'])
        ->where('path', '.*')
        ->name('settings.logo');
    Route::get('settings/export/{type}', [SettingsController::class, 'exportCsv'])->name('settings.export.csv');
    
    // Bildirim Ayarları
    Route::get('settings/notifications', [\App\Http\Controllers\NotificationSettingsController::class, 'edit'])->name('settings.notifications');
    Route::put('settings/notifications', [\App\Http\Controllers\NotificationSettingsController::class, 'update'])->name('settings.notifications.update');
    Route::post('settings/notifications/test', [\App\Http\Controllers\NotificationSettingsController::class, 'sendTest'])->name('settings.notifications.test');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('settings/invoice-extra-fields', InvoiceExtraFieldController::class)
            ->names('settings.invoice-extra-fields')
            ->except(['show']);
        Route::resource('settings/tax-forms', TaxFormController::class)
            ->names('settings.tax-forms')
            ->except(['show']);
    });

    Route::resource('tax-declarations', TaxDeclarationController::class)
        ->only(['index', 'edit', 'update']);

    // Beyanname API endpoints
    Route::get('tax-declarations/api/calendar', [TaxDeclarationController::class, 'calendar'])
        ->name('tax-declarations.calendar');
    Route::get('tax-declarations/api/today-due', [TaxDeclarationController::class, 'todayDue'])
        ->name('tax-declarations.today-due');
    Route::patch('tax-declarations/bulk-status', [TaxDeclarationController::class, 'bulkUpdateStatus'])
        ->name('tax-declarations.bulk-status');

    // AJAX endpoint - Hızlı durum değiştirme
    Route::patch('tax-declarations/{taxDeclaration}/status', [TaxDeclarationController::class, 'updateStatus'])
        ->name('tax-declarations.update-status');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('balances', [ReportController::class, 'balances'])->name('balance');
        Route::get('collections', [ReportController::class, 'collections'])->name('collections');
        Route::get('overdues', [ReportController::class, 'overdues'])->name('overdues');
        Route::get('invoices', [ReportController::class, 'invoices'])->name('invoices');
        
        // CSV Export
        Route::get('balances/export', [ReportController::class, 'exportBalances'])->name('balance.export');
        Route::get('collections/export', [ReportController::class, 'exportCollections'])->name('collections.export');
        Route::get('overdues/export', [ReportController::class, 'exportOverdues'])->name('overdues.export');
        Route::get('invoices/export', [ReportController::class, 'exportInvoices'])->name('invoices.export');
        
        // PDF Export
        Route::get('balances/pdf', [ReportController::class, 'pdfBalances'])->name('balance.pdf');
        Route::get('overdues/pdf', [ReportController::class, 'pdfOverdues'])->name('overdues.pdf');
        Route::get('invoices/pdf', [ReportController::class, 'pdfInvoices'])->name('invoices.pdf');
    });

    // GİB Resmi Vergi Takvimi
    Route::prefix('tax-calendar')->name('tax-calendar.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TaxCalendarController::class, 'index'])->name('index');
        Route::post('/generate', [\App\Http\Controllers\TaxCalendarController::class, 'generate'])->name('generate');
        Route::delete('/delete-year', [\App\Http\Controllers\TaxCalendarController::class, 'deleteYear'])->name('delete-year');
        Route::get('/api/month', [\App\Http\Controllers\TaxCalendarController::class, 'apiMonth'])->name('api.month');
        Route::get('/api/upcoming', [\App\Http\Controllers\TaxCalendarController::class, 'apiUpcoming'])->name('api.upcoming');
        Route::post('/api/generate', [\App\Http\Controllers\TaxCalendarController::class, 'apiGenerate'])->name('api.generate');
    });
});
