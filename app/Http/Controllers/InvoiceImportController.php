<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InvoiceImportController extends Controller
{
    /**
     * Import form sayfası
     */
    public function showForm(): View
    {
        $firms = Firm::active()->orderBy('name')->get(['id', 'name']);
        
        return view('invoices.import', compact('firms'));
    }

    /**
     * CSV dosyasını işle
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], [
            'csv_file.required' => 'Lütfen bir CSV dosyası seçin.',
            'csv_file.mimes' => 'Dosya CSV formatında olmalıdır.',
            'csv_file.max' => 'Dosya boyutu maksimum 5MB olabilir.',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        // CSV'yi oku
        $rows = [];
        $errors = [];
        $lineNumber = 0;

        if (($handle = fopen($path, 'r')) !== false) {
            // İlk satır başlık
            $header = fgetcsv($handle, 0, ';');
            
            if (!$header) {
                return back()->withErrors(['csv_file' => 'CSV dosyası okunamadı veya boş.']);
            }

            // Başlıkları normalize et
            $header = array_map(fn($h) => mb_strtolower(trim($h)), $header);
            
            // Gerekli sütunları kontrol et
            $requiredColumns = ['firma', 'tutar', 'tarih'];
            $missingColumns = array_diff($requiredColumns, $header);
            
            if (!empty($missingColumns)) {
                return back()->withErrors([
                    'csv_file' => 'Eksik sütunlar: ' . implode(', ', $missingColumns) . '. Gerekli sütunlar: firma, tutar, tarih'
                ]);
            }

            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                $lineNumber++;
                
                if (count($data) < count($header)) {
                    $errors[] = "Satır {$lineNumber}: Eksik sütun sayısı";
                    continue;
                }

                $row = array_combine($header, $data);
                
                // Satırı doğrula
                $validationResult = $this->validateRow($row, $lineNumber);
                
                if ($validationResult['valid']) {
                    $rows[] = $validationResult['data'];
                } else {
                    $errors = array_merge($errors, $validationResult['errors']);
                }
            }
            
            fclose($handle);
        }

        if (empty($rows)) {
            return back()->withErrors([
                'csv_file' => 'İçe aktarılacak geçerli kayıt bulunamadı.',
                'import_errors' => $errors,
            ])->withInput();
        }

        // Faturaları oluştur
        $created = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Aynı fatura var mı kontrol et
                $exists = Invoice::where('firm_id', $row['firm_id'])
                    ->where('official_number', $row['official_number'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                Invoice::create([
                    'firm_id' => $row['firm_id'],
                    'official_number' => $row['official_number'],
                    'date' => $row['date'],
                    'due_date' => $row['due_date'],
                    'amount' => $row['amount'],
                    'description' => $row['description'],
                    'status' => $row['status'],
                ]);
                
                $created++;
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'csv_file' => 'İçe aktarma sırasında hata: ' . $e->getMessage()
            ])->withInput();
        }

        $message = "{$created} fatura başarıyla içe aktarıldı.";
        if ($skipped > 0) {
            $message .= " {$skipped} fatura zaten mevcut olduğu için atlandı.";
        }
        if (!empty($errors)) {
            $message .= " " . count($errors) . " satırda hata var.";
        }

        return redirect()
            ->route('invoices.index')
            ->with('status', $message);
    }

    /**
     * Satırı doğrula ve dönüştür
     */
    protected function validateRow(array $row, int $lineNumber): array
    {
        $errors = [];
        $data = [];

        // Firma bul
        $firmName = trim($row['firma'] ?? '');
        $firm = Firm::where('name', 'like', "%{$firmName}%")->first();
        
        if (!$firm) {
            $errors[] = "Satır {$lineNumber}: Firma bulunamadı: {$firmName}";
            return ['valid' => false, 'errors' => $errors];
        }
        $data['firm_id'] = $firm->id;

        // Tutar
        $amount = str_replace(['.', ','], ['', '.'], trim($row['tutar'] ?? '0'));
        if (!is_numeric($amount) || $amount <= 0) {
            $errors[] = "Satır {$lineNumber}: Geçersiz tutar";
            return ['valid' => false, 'errors' => $errors];
        }
        $data['amount'] = (float) $amount;

        // Tarih
        try {
            $data['date'] = Carbon::createFromFormat('d.m.Y', trim($row['tarih']))->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                $data['date'] = Carbon::parse(trim($row['tarih']))->format('Y-m-d');
            } catch (\Exception $e2) {
                $errors[] = "Satır {$lineNumber}: Geçersiz tarih formatı (dd.mm.yyyy bekleniyor)";
                return ['valid' => false, 'errors' => $errors];
            }
        }

        // Vade tarihi (opsiyonel)
        if (!empty($row['vade_tarihi'] ?? $row['vade'] ?? null)) {
            try {
                $dueDate = trim($row['vade_tarihi'] ?? $row['vade']);
                $data['due_date'] = Carbon::createFromFormat('d.m.Y', $dueDate)->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    $data['due_date'] = Carbon::parse($dueDate)->format('Y-m-d');
                } catch (\Exception $e2) {
                    $data['due_date'] = Carbon::parse($data['date'])->addDays(30)->format('Y-m-d');
                }
            }
        } else {
            $data['due_date'] = Carbon::parse($data['date'])->addDays(30)->format('Y-m-d');
        }

        // Fatura numarası
        $data['official_number'] = trim($row['fatura_no'] ?? $row['numara'] ?? 'IMP-' . time() . '-' . $lineNumber);

        // Açıklama
        $data['description'] = trim($row['aciklama'] ?? $row['açıklama'] ?? 'CSV Import');

        // Durum
        $statusMap = [
            'ödenmedi' => 'unpaid',
            'odenmedi' => 'unpaid',
            'bekliyor' => 'unpaid',
            'kısmi' => 'partial',
            'kismi' => 'partial',
            'ödendi' => 'paid',
            'odendi' => 'paid',
            'iptal' => 'cancelled',
        ];
        $status = mb_strtolower(trim($row['durum'] ?? 'ödenmedi'));
        $data['status'] = $statusMap[$status] ?? 'unpaid';

        return ['valid' => true, 'data' => $data, 'errors' => []];
    }

    /**
     * Örnek CSV indir
     */
    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="fatura_import_sablonu.csv"',
        ];

        return response()->stream(function () {
            $output = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Başlıklar
            fputcsv($output, [
                'firma',
                'tutar',
                'tarih',
                'vade_tarihi',
                'fatura_no',
                'aciklama',
                'durum'
            ], ';');
            
            // Örnek satırlar
            fputcsv($output, [
                'Örnek Firma A.Ş.',
                '1500,00',
                '01.12.2024',
                '31.12.2024',
                'FA-2024-001',
                'Aralık ayı ücret faturası',
                'ödenmedi'
            ], ';');
            
            fputcsv($output, [
                'Demo Ltd.',
                '2500,50',
                '15.12.2024',
                '15.01.2025',
                'FA-2024-002',
                'Danışmanlık hizmeti',
                'bekliyor'
            ], ';');
            
            fclose($output);
        }, 200, $headers);
    }
}
