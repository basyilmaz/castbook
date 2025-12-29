<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\InstallService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InstallController extends Controller
{
    protected InstallService $installService;

    public function __construct(InstallService $installService)
    {
        $this->installService = $installService;
    }

    /**
     * Kurulum ana sayfası - ilk adıma yönlendir
     */
    public function index()
    {
        if ($this->installService->isInstalled()) {
            return redirect('/');
        }

        return redirect()->route('install.requirements');
    }

    /**
     * Adım 1: Sistem Gereksinimleri
     */
    public function requirements(): View
    {
        if ($this->installService->isInstalled()) {
            return redirect('/');
        }

        $requirements = $this->installService->checkRequirements();
        $permissions = $this->installService->checkPermissions();
        $allMet = $this->installService->allRequirementsMet();

        return view('install.requirements', compact('requirements', 'permissions', 'allMet'));
    }

    /**
     * Adım 2: Veritabanı Ayarları (GET)
     */
    public function database(): View
    {
        if ($this->installService->isInstalled()) {
            return redirect('/');
        }

        if (!$this->installService->allRequirementsMet()) {
            return redirect()->route('install.requirements')
                ->with('error', 'Lütfen önce sistem gereksinimlerini karşılayın.');
        }

        return view('install.database');
    }

    /**
     * Adım 2: Veritabanı Test ve Kaydet (POST)
     */
    public function testDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|string',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
            'app_url' => 'required|url',
        ]);

        $result = $this->installService->testDatabaseConnection(
            $request->db_host,
            $request->db_port,
            $request->db_database,
            $request->db_username,
            $request->db_password ?? ''
        );

        if (!$result['success']) {
            return back()->withInput()->with('error', $result['message']);
        }

        // .env dosyasını oluştur
        $envCreated = $this->installService->createEnvFile([
            'app_name' => 'CastBook',
            'app_url' => $request->app_url,
            'db_host' => $request->db_host,
            'db_port' => $request->db_port,
            'db_database' => $request->db_database,
            'db_username' => $request->db_username,
            'db_password' => $request->db_password ?? '',
        ]);

        if (!$envCreated) {
            return back()->withInput()->with('error', '.env dosyası oluşturulamadı.');
        }

        // APP_KEY oluştur
        $this->installService->generateAppKey();

        // Session'a kaydet
        session(['install_db_configured' => true]);

        return redirect()->route('install.migration');
    }

    /**
     * Adım 3: Migration (GET)
     */
    public function migration(): View
    {
        if ($this->installService->isInstalled()) {
            return redirect('/');
        }

        return view('install.migration');
    }

    /**
     * Adım 3: Migration Çalıştır (POST)
     */
    public function runMigration()
    {
        // Config'i yenile
        $this->installService->clearCache();
        
        // Konfigürasyonu yeniden yükle
        app()->bootstrapWith([
            \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        ]);

        $migrationResult = $this->installService->runMigrations();

        if (!$migrationResult['success']) {
            return back()->with('error', $migrationResult['message']);
        }

        $seederResult = $this->installService->runSeeders();

        session(['install_migration_done' => true]);

        return redirect()->route('install.admin');
    }

    /**
     * Adım 4: Admin Oluşturma (GET)
     */
    public function admin(): View
    {
        if ($this->installService->isInstalled()) {
            return redirect('/');
        }

        return view('install.admin');
    }

    /**
     * Adım 4: Admin Kaydet (POST)
     */
    public function createAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'is_active' => true,
            ]);

            session(['install_admin_created' => true]);

            return redirect()->route('install.settings');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Kullanıcı oluşturulamadı: ' . $e->getMessage());
        }
    }

    /**
     * Adım 5: Temel Ayarlar (GET)
     */
    public function settings(): View
    {
        if ($this->installService->isInstalled()) {
            return redirect('/');
        }

        return view('install.settings');
    }

    /**
     * Adım 5: Ayarları Kaydet (POST)
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:500',
        ]);

        $settings = [
            'company_name' => $request->company_name,
            'company_email' => $request->company_email,
            'company_phone' => $request->company_phone,
            'company_address' => $request->company_address,
        ];

        foreach ($settings as $key => $value) {
            if ($value !== null) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        // APP_INSTALLED=true yap
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $content = file_get_contents($envPath);
            if (preg_match("/^APP_INSTALLED=/m", $content)) {
                $content = preg_replace("/^APP_INSTALLED=.*/m", "APP_INSTALLED=true", $content);
            } else {
                $content .= "\nAPP_INSTALLED=true";
            }
            file_put_contents($envPath, $content);
        }

        // Cache temizle
        $this->installService->clearCache();

        return redirect()->route('install.complete');
    }

    /**
     * Adım 6: Kurulum Tamamlandı
     */
    public function complete(): View
    {
        return view('install.complete');
    }
}
