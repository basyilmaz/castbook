<?php

namespace App\Http\Controllers;

use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class UpdateController extends Controller
{
    protected UpdateService $updateService;

    public function __construct(UpdateService $updateService)
    {
        $this->updateService = $updateService;
    }

    /**
     * Güncelleme sayfası
     */
    public function index(): View
    {
        $versionInfo = $this->updateService->getVersionInfo();
        $backups = $this->updateService->getBackupList();
        $rollbacks = $this->updateService->getRollbackList();

        return view('settings.tabs.updates', compact('versionInfo', 'backups', 'rollbacks'));
    }

    /**
     * Güncelleme kontrolü (AJAX)
     */
    public function checkUpdate()
    {
        $result = $this->updateService->forceCheckForUpdates();
        
        return response()->json($result);
    }

    /**
     * Güncelleme indir ve uygula
     */
    public function applyUpdate(Request $request)
    {
        $downloadUrl = $request->input('download_url');
        
        if (!$downloadUrl) {
            return redirect()->route('settings.updates')
                ->with('error', 'İndirme URL\'i bulunamadı.');
        }

        // ZIP indir
        $downloadResult = $this->updateService->downloadUpdate($downloadUrl);
        if (!$downloadResult['success']) {
            return redirect()->route('settings.updates')
                ->with('error', $downloadResult['message']);
        }

        // Güncellemeyi uygula
        $applyResult = $this->updateService->applyUpdate($downloadResult['path']);
        
        if ($applyResult['success']) {
            return redirect()->route('settings.updates')
                ->with('success', 'Güncelleme başarıyla tamamlandı!');
        }

        return redirect()->route('settings.updates')
            ->with('error', $applyResult['message'])
            ->with('update_steps', $applyResult['steps'] ?? []);
    }

    /**
     * Rollback uygula
     */
    public function rollback()
    {
        $result = $this->updateService->rollback();

        if ($result['success']) {
            return redirect()->route('settings.updates')
                ->with('success', $result['message']);
        }

        return redirect()->route('settings.updates')
            ->with('error', $result['message']);
    }

    /**
     * Rollback noktası oluştur
     */
    public function createRollback()
    {
        $result = $this->updateService->createRollbackPoint();

        if ($result['success']) {
            return redirect()->route('settings.updates')
                ->with('success', "Rollback noktası oluşturuldu: {$result['filename']}");
        }

        return redirect()->route('settings.updates')
            ->with('error', $result['message']);
    }

    /**
     * Veritabanı yedekle
     */
    public function backupDatabase()
    {
        $result = $this->updateService->backupDatabase();

        if ($result['success']) {
            return redirect()->route('settings.updates')
                ->with('success', "Veritabanı yedeklendi: {$result['filename']} ({$result['size']})");
        }

        return redirect()->route('settings.updates')
            ->with('error', $result['message']);
    }

    /**
     * Dosyaları yedekle
     */
    public function backupFiles()
    {
        $result = $this->updateService->backupFiles();

        if ($result['success']) {
            return redirect()->route('settings.updates')
                ->with('success', "Dosyalar yedeklendi: {$result['filename']} ({$result['size']})");
        }

        return redirect()->route('settings.updates')
            ->with('error', $result['message']);
    }

    /**
     * Migration çalıştır
     */
    public function runMigration()
    {
        $result = $this->updateService->runMigrations();

        if ($result['success']) {
            return redirect()->route('settings.updates')
                ->with('success', 'Migration başarıyla çalıştırıldı.');
        }

        return redirect()->route('settings.updates')
            ->with('error', $result['message']);
    }

    /**
     * Cache temizle
     */
    public function clearCache()
    {
        $result = $this->updateService->clearCache();

        if ($result['success']) {
            return redirect()->route('settings.updates')
                ->with('success', 'Önbellek temizlendi.');
        }

        return redirect()->route('settings.updates')
            ->with('error', $result['message']);
    }

    /**
     * Yedekleme indir
     */
    public function downloadBackup(string $filename)
    {
        $path = $this->updateService->getBackupPath($filename);

        if ($path) {
            return Response::download($path, $filename);
        }

        return redirect()->route('settings.updates')
            ->with('error', 'Yedekleme dosyası bulunamadı.');
    }

    /**
     * Yedekleme sil
     */
    public function deleteBackup(string $filename)
    {
        if ($this->updateService->deleteBackup($filename)) {
            return redirect()->route('settings.updates')
                ->with('success', 'Yedekleme silindi.');
        }

        return redirect()->route('settings.updates')
            ->with('error', 'Yedekleme silinemedi.');
    }
}
