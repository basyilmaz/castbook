<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // MySQL eski sürümler için string length sınırı
        Schema::defaultStringLength(191);
        // Rate Limiting konfigürasyonu
        $this->configureRateLimiting();
        
        // Register observers
        \App\Models\Firm::observe(\App\Observers\FirmObserver::class);
        
        $settings = [];

        try {
            if (Schema::hasTable('settings')) {
                $settings = Setting::query()->pluck('value', 'key')->toArray();

                if (! app()->runningInConsole()) {
                    $mailSettings = collect($settings)->only([
                        'mail_host',
                        'mail_port',
                        'mail_username',
                        'mail_password',
                        'mail_encryption',
                        'mail_from_address',
                        'mail_from_name',
                    ]);

                    config([
                        'mail.mailers.smtp.host' => $mailSettings->get('mail_host', config('mail.mailers.smtp.host')),
                        'mail.mailers.smtp.port' => $mailSettings->get('mail_port', config('mail.mailers.smtp.port')),
                        'mail.mailers.smtp.username' => $mailSettings->get('mail_username', config('mail.mailers.smtp.username')),
                        'mail.mailers.smtp.password' => $mailSettings->get('mail_password', config('mail.mailers.smtp.password')),
                        'mail.mailers.smtp.encryption' => $mailSettings->get('mail_encryption', config('mail.mailers.smtp.encryption')),
                        'mail.from.address' => $mailSettings->get('mail_from_address', config('mail.from.address')),
                        'mail.from.name' => $mailSettings->get('mail_from_name', config('mail.from.name')),
                    ]);
                }
            }
        } catch (\Throwable $exception) {
            $settings = [];
        }

        View::composer('layouts.app', function ($view) use ($settings) {
            $appFallbackName = config('app.name', 'CastBook');
            $companyName = $settings['company_name'] ?? $appFallbackName;
            $companyName = $companyName ?: $appFallbackName;
            $menuTitle = $settings['company_menu_title'] ?? $companyName;
            $menuTitle = $menuTitle ?: $companyName;
            $menuSubtitle = $settings['company_menu_subtitle'] ?? 'Kontrol Paneli';
            $menuSubtitle = $menuSubtitle ?: null;

            $logoUrl = null;
            $logoPath = $settings['company_logo_path'] ?? null;
            $logoVersion = $settings['company_logo_version'] ?? null;

            if (! empty($logoPath)) {
                $logoParams = ['path' => $logoPath];

                if (! empty($logoVersion)) {
                    $logoParams['v'] = $logoVersion;
                }

                $logoUrl = route('settings.logo', $logoParams);
            }

            $view->with([
                'layoutThemeMode' => $settings['theme_mode'] ?? 'auto',
                'layoutCompanyName' => $companyName,
                'layoutCompanyInitial' => Str::upper(Str::substr($companyName, 0, 1)),
                'layoutMenuTitle' => $menuTitle,
                'layoutMenuSubtitle' => $menuSubtitle,
                'layoutLogoUrl' => $logoUrl,
                'layoutIsAdmin' => optional(auth()->user())->role === 'admin',
            ]);
        });
    }

    /**
     * Rate limiting konfigürasyonu
     */
    protected function configureRateLimiting(): void
    {
        $limiter = app(\Illuminate\Cache\RateLimiter::class);

        // Login rate limit - 5 deneme / dakika
        $limiter->for('login', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return redirect()->back()
                        ->withErrors(['email' => 'Çok fazla giriş denemesi. Lütfen 1 dakika bekleyin.'])
                        ->withInput();
                });
        });

        // Şifre sıfırlama - 3 istek / dakika
        $limiter->for('password-reset', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(3)
                ->by($request->ip());
        });

        // API rate limit - 60 istek / dakika
        $limiter->for('api', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        // E-posta gönderimi - 10 / saat
        $limiter->for('email', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perHour(10)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Genel form submission - 30 / dakika
        $limiter->for('form-submit', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
