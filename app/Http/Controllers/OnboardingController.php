<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Onboarding wizard sayfasını göster
     */
    public function show(): View|RedirectResponse
    {
        // Eğer onboarding tamamlanmışsa dashboard'a yönlendir
        if (Setting::getValue('onboarding_completed', '0') === '1') {
            return redirect()->route('dashboard');
        }

        $settings = [
            'company_name' => Setting::getValue('company_name', ''),
            'company_email' => Setting::getValue('company_email', ''),
            'company_phone' => Setting::getValue('company_phone', ''),
        ];

        return view('onboarding.wizard', compact('settings'));
    }

    /**
     * Onboarding verilerini kaydet
     */
    public function complete(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'first_firm_name' => ['nullable', 'string', 'max:255'],
            'first_firm_tax_no' => ['nullable', 'string', 'max:20'],
            'first_firm_monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'theme_mode' => ['nullable', 'in:light,dark,auto'],
            'invoice_due_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'enable_notifications' => ['nullable'],
        ]);

        // Şirket bilgilerini kaydet
        Setting::setValue('company_name', $data['company_name']);
        Setting::setValue('company_menu_title', $data['company_name']);
        
        if (!empty($data['company_email'])) {
            Setting::setValue('company_email', $data['company_email']);
        }
        
        if (!empty($data['company_phone'])) {
            Setting::setValue('company_phone', $data['company_phone']);
        }

        // Tema ve tercihler
        Setting::setValue('theme_mode', $data['theme_mode'] ?? 'light');
        Setting::setValue('invoice_default_due_days', (string) ($data['invoice_due_days'] ?? 30));
        Setting::setValue('enable_email_notifications', $request->boolean('enable_notifications') ? '1' : '0');

        // İlk firma ekleme (opsiyonel)
        if (!empty($data['first_firm_name'])) {
            Firm::create([
                'name' => $data['first_firm_name'],
                'tax_no' => $data['first_firm_tax_no'] ?? null,
                'monthly_fee' => $data['first_firm_monthly_fee'] ?? null,
                'status' => 'active',
            ]);
        }

        // Onboarding tamamlandı olarak işaretle
        Setting::setValue('onboarding_completed', '1');

        return redirect()
            ->route('dashboard')
            ->with('status', 'Hoş geldiniz! CastBook\'u kullanmaya başlayabilirsiniz.');
    }
}
