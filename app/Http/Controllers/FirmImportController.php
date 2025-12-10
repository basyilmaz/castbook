<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class FirmImportController extends Controller
{
    /**
     * Import formunu göster
     */
    public function showImportForm(): View
    {
        return view('firms.import');
    }

    /**
     * CSV/Excel dosyasından firma import et
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'], // 5MB max
        ], [
            'file.required' => 'Lütfen bir dosya seçin.',
            'file.mimes' => 'Dosya formatı CSV veya Excel olmalıdır.',
            'file.max' => 'Dosya boyutu 5MB\'dan küçük olmalıdır.',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            if ($extension === 'csv' || $extension === 'txt') {
                $data = $this->parseCsv($file->getPathname());
            } else {
                // Excel için basit CSV fallback
                return back()->withErrors(['file' => 'Excel dosyaları henüz desteklenmiyor. Lütfen CSV formatı kullanın.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Dosya okunamadı: ' . $e->getMessage()]);
        }

        if (empty($data)) {
            return back()->withErrors(['file' => 'Dosyada veri bulunamadı.']);
        }

        $result = $this->processImport($data);

        $message = "{$result['created']} yeni firma oluşturuldu.";
        if ($result['updated'] > 0) {
            $message .= " {$result['updated']} firma güncellendi.";
        }
        if ($result['errors'] > 0) {
            $message .= " {$result['errors']} satır hatalı.";
        }

        $redirect = redirect()->route('firms.index')->with('status', $message);

        if (!empty($result['errorDetails'])) {
            $redirect->with('warning', 'Bazı satırlar işlenemedi: ' . implode(', ', array_slice($result['errorDetails'], 0, 5)));
        }

        return $redirect;
    }

    /**
     * CSV dosyasını parse et
     */
    protected function parseCsv(string $path): array
    {
        $data = [];
        $handle = fopen($path, 'r');

        if (!$handle) {
            throw new \Exception('Dosya açılamadı.');
        }

        // BOM karakterini temizle
        $bom = fread($handle, 3);
        if ($bom !== "\xef\xbb\xbf") {
            rewind($handle);
        }

        // Başlık satırı
        $headers = fgetcsv($handle, 0, ';');
        if (!$headers) {
            $headers = fgetcsv($handle, 0, ',');
            rewind($handle);
            fgetcsv($handle); // Başlığı atla
        }

        // Başlıkları normalize et
        $headers = array_map(function ($h) {
            return strtolower(trim(str_replace([' ', '-'], '_', $h)));
        }, $headers);

        // Satırları oku
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) === 1 && strpos($row[0], ',') !== false) {
                $row = str_getcsv($row[0], ',');
            }

            if (count($row) < 2) continue; // Boş satırları atla

            $rowData = [];
            foreach ($headers as $i => $header) {
                $rowData[$header] = isset($row[$i]) ? trim($row[$i]) : '';
            }
            $data[] = $rowData;
        }

        fclose($handle);
        return $data;
    }

    /**
     * Import verilerini işle
     */
    protected function processImport(array $data): array
    {
        $result = [
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'errorDetails' => [],
        ];

        $fieldMapping = [
            'firma_adi' => 'name',
            'firma_adı' => 'name',
            'name' => 'name',
            'ad' => 'name',
            'vergi_no' => 'tax_no',
            'vergi_numarasi' => 'tax_no',
            'vergi_numarası' => 'tax_no',
            'tax_no' => 'tax_no',
            'vkn' => 'tax_no',
            'aylik_ucret' => 'monthly_fee',
            'aylık_ücret' => 'monthly_fee',
            'monthly_fee' => 'monthly_fee',
            'ucret' => 'monthly_fee',
            'ücret' => 'monthly_fee',
            'yetkili' => 'contact_person',
            'yetkili_kisi' => 'contact_person',
            'contact_person' => 'contact_person',
            'telefon' => 'contact_phone',
            'contact_phone' => 'contact_phone',
            'phone' => 'contact_phone',
            'eposta' => 'contact_email',
            'email' => 'contact_email',
            'contact_email' => 'contact_email',
            'adres' => 'address',
            'address' => 'address',
            'sirket_turu' => 'company_type',
            'şirket_türü' => 'company_type',
            'company_type' => 'company_type',
            'not' => 'notes',
            'notes' => 'notes',
            'notlar' => 'notes',
            // Yeni alanlar
            'sozlesme_baslangic' => 'contract_start_at',
            'sözleşme_başlangıç' => 'contract_start_at',
            'contract_start_at' => 'contract_start_at',
            'otomatik_fatura' => 'auto_invoice_enabled',
            'auto_invoice_enabled' => 'auto_invoice_enabled',
            'beyanname_takibi' => 'tax_tracking_enabled',
            'tax_tracking_enabled' => 'tax_tracking_enabled',
            'kdv_orani' => 'default_vat_rate',
            'default_vat_rate' => 'default_vat_rate',
            'kdv_dahil' => 'default_vat_included',
            'default_vat_included' => 'default_vat_included',
        ];

        DB::beginTransaction();

        try {
            foreach ($data as $index => $row) {
                $mapped = [];

                foreach ($row as $key => $value) {
                    $normalizedKey = strtolower(str_replace([' ', '-'], '_', $key));
                    if (isset($fieldMapping[$normalizedKey])) {
                        $mapped[$fieldMapping[$normalizedKey]] = $value;
                    }
                }

                // Zorunlu alan kontrolü
                if (empty($mapped['name'])) {
                    $result['errors']++;
                    $result['errorDetails'][] = "Satır " . ($index + 2) . ": Firma adı boş";
                    continue;
                }

                // Aylik ücret düzenleme
                if (isset($mapped['monthly_fee'])) {
                    $mapped['monthly_fee'] = (float) str_replace(['.', ',', '₺', ' '], ['', '.', '', ''], $mapped['monthly_fee']);
                }

                // Şirket türü normalize et
                if (!empty($mapped['company_type'])) {
                    $mapped['company_type'] = $this->normalizeCompanyType($mapped['company_type']);
                }

                // Sözleşme başlangıç tarihi
                if (!empty($mapped['contract_start_at'])) {
                    try {
                        $mapped['contract_start_at'] = \Carbon\Carbon::parse($mapped['contract_start_at'])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $mapped['contract_start_at'] = now()->format('Y-m-d');
                    }
                } else {
                    $mapped['contract_start_at'] = now()->format('Y-m-d');
                }

                // Boolean alanlar
                if (isset($mapped['auto_invoice_enabled'])) {
                    $mapped['auto_invoice_enabled'] = $this->parseBooleanValue($mapped['auto_invoice_enabled']);
                }
                if (isset($mapped['tax_tracking_enabled'])) {
                    $mapped['tax_tracking_enabled'] = $this->parseBooleanValue($mapped['tax_tracking_enabled']);
                }
                if (isset($mapped['default_vat_included'])) {
                    $mapped['default_vat_included'] = $this->parseBooleanValue($mapped['default_vat_included']);
                }

                // KDV oranı
                if (isset($mapped['default_vat_rate'])) {
                    $mapped['default_vat_rate'] = (float) str_replace(['%', ','], ['', '.'], $mapped['default_vat_rate']);
                }

                // Validation
                $validator = Validator::make($mapped, [
                    'name' => 'required|string|max:255',
                    'tax_no' => 'nullable|string|max:20',
                    'monthly_fee' => 'nullable|numeric|min:0',
                    'contact_person' => 'nullable|string|max:255',
                    'contact_phone' => 'nullable|string|max:50',
                    'contact_email' => 'nullable|email|max:255',
                    'address' => 'nullable|string|max:500',
                    'company_type' => 'nullable|in:individual,limited,joint_stock',
                    'notes' => 'nullable|string|max:1000',
                    'contract_start_at' => 'nullable|date',
                    'auto_invoice_enabled' => 'nullable|boolean',
                    'tax_tracking_enabled' => 'nullable|boolean',
                    'default_vat_rate' => 'nullable|numeric|min:0|max:100',
                    'default_vat_included' => 'nullable|boolean',
                ]);

                if ($validator->fails()) {
                    $result['errors']++;
                    $result['errorDetails'][] = "Satır " . ($index + 2) . ": " . $validator->errors()->first();
                    continue;
                }

                // Var olan firmayı kontrol et (vergi no veya isim ile)
                $existingFirm = null;
                if (!empty($mapped['tax_no'])) {
                    $existingFirm = Firm::where('tax_no', $mapped['tax_no'])->first();
                }
                if (!$existingFirm) {
                    $existingFirm = Firm::where('name', $mapped['name'])->first();
                }

                if ($existingFirm) {
                    $existingFirm->update(array_filter($mapped));
                    $result['updated']++;
                } else {
                    $mapped['status'] = 'active';
                    Firm::create($mapped);
                    $result['created']++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Örnek CSV şablonu indir
     */
    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="firma_sablonu.csv"',
        ];

        $columns = ['Firma Adı', 'Vergi No', 'Şirket Türü', 'Aylık Ücret', 'Sözleşme Başlangıç', 'Yetkili', 'Telefon', 'E-posta', 'Otomatik Fatura', 'Beyanname Takibi', 'KDV Oranı', 'KDV Dahil', 'Notlar'];
        
        $exampleData = [
            ['ABC Teknoloji A.Ş.', '1234567890', 'Anonim Şirket', '3500', '2024-01-01', 'Ali Yılmaz', '0532 111 22 33', 'ali@abc.com', 'Evet', 'Evet', '20', 'Evet', 'Yazılım firması'],
            ['XYZ Ltd. Şti.', '9876543210', 'Limited Şirket', '2500', '2024-06-15', 'Ayşe Demir', '0533 444 55 66', 'ayse@xyz.com', 'Evet', 'Evet', '20', 'Evet', ''],
            ['Mehmet Danışmanlık', '5555555555', 'Şahıs', '1500', '2024-03-01', 'Mehmet Öz', '0544 777 88 99', 'mehmet@dan.com', 'Hayır', 'Evet', '10', 'Hayır', 'Serbest meslek'],
        ];

        return response()->streamDownload(function () use ($columns, $exampleData) {
            $output = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($output, $columns, ';');
            foreach ($exampleData as $row) {
                fputcsv($output, $row, ';');
            }
            
            fclose($output);
        }, 'firma_sablonu.csv', $headers);
    }

    /**
     * Şirket türünü enum değerine normalize et
     */
    protected function normalizeCompanyType(string $value): string
    {
        $value = mb_strtolower(trim($value));
        
        $mapping = [
            'şahıs' => 'individual',
            'sahis' => 'individual',
            'şahıs firması' => 'individual',
            'bireysel' => 'individual',
            'individual' => 'individual',
            'gerçek kişi' => 'individual',
            
            'limited' => 'limited',
            'ltd' => 'limited',
            'limited şirket' => 'limited',
            'ltd. şti.' => 'limited',
            'ltd şti' => 'limited',
            
            'anonim' => 'joint_stock',
            'a.ş.' => 'joint_stock',
            'aş' => 'joint_stock',
            'anonim şirket' => 'joint_stock',
            'joint_stock' => 'joint_stock',
        ];

        return $mapping[$value] ?? 'limited'; // Varsayılan: limited
    }

    /**
     * Boolean değeri parse et (Evet/Hayır, 1/0, true/false)
     */
    protected function parseBooleanValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = mb_strtolower(trim((string) $value));
        
        $trueValues = ['1', 'true', 'yes', 'evet', 'e', 'açık', 'aktif'];
        
        return in_array($value, $trueValues);
    }
}
