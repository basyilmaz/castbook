<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\TaxDeclaration;
use App\Models\TaxForm;
use App\Models\FirmTaxForm;
use App\Services\BackupEncryptionService;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    private const RESTORE_THROTTLE_MAX_ATTEMPTS = 5;
    private const RESTORE_THROTTLE_DECAY_SECONDS = 300;

    public function __construct(private readonly BackupEncryptionService $backupEncryption)
    {
    }

    public function edit(): View
    {
        $settings = Setting::query()->pluck('value', 'key');
        $paymentMethods = Setting::getPaymentMethods();

        return view('settings.edit', [
            'settings' => $settings,
            'paymentMethods' => $paymentMethods,
            'invoiceNotifyEnabled' => (bool) Setting::getValue('invoice_auto_notify', '0'),
            'invoiceNotifyRecipients' => Setting::getInvoiceNotificationRecipients(),
            'isAdmin' => auth()->user()?->isAdmin() ?? false,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_menu_title' => ['nullable', 'string', 'max:255'],
            'company_menu_subtitle' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'invoice_default_description' => ['nullable', 'string'],
            'invoice_default_due_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'invoice_prefix' => ['nullable', 'string', 'max:10'],
            'theme_mode' => ['required', 'in:light,dark,auto'],
            'company_logo' => ['nullable', 'image', 'max:2048'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'string', 'max:20'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'payment_methods' => ['nullable', 'string'],
            'invoice_auto_notify' => ['nullable', 'boolean'],
            'invoice_notify_recipients' => ['nullable', 'string'],
            'smtp_preset' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('company_logo')) {
            try {
                $file = $request->file('company_logo');
                
                // Dosya boyutu kontrolü (max 500KB)
                if ($file->getSize() > 512000) {
                    return back()->withErrors(['company_logo' => 'Logo dosyası çok büyük. Maksimum 500KB olmalı.'])->withInput();
                }
                
                $mimeType = $file->getMimeType();
                
                // Sadece resim dosyaları kabul et
                if (!str_starts_with($mimeType, 'image/')) {
                    return back()->withErrors(['company_logo' => 'Sadece resim dosyaları yüklenebilir.'])->withInput();
                }
                
                $contents = file_get_contents($file->getRealPath());
                $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                
                Setting::setValue('company_logo_base64', $base64);
                Setting::setValue('company_logo_version', (string) now()->timestamp);
                
                // Eski path bazlı logo'yu temizle
                $oldLogo = Setting::getValue('company_logo_path');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
                Setting::setValue('company_logo_path', '');
            } catch (\Exception $e) {
                Log::error('Logo yükleme hatası: ' . $e->getMessage());
                return back()->withErrors(['company_logo' => 'Logo yüklenirken bir hata oluştu: ' . $e->getMessage()])->withInput();
            }
        }

        $keys = [
            'company_name',
            'company_menu_title',
            'company_menu_subtitle',
            'company_address',
            'company_email',
            'company_phone',
            'invoice_default_description',
            'invoice_default_due_days',
            'invoice_prefix',
            'theme_mode',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
        ];

        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                Setting::setValue($key, $data[$key] ?? '');
            }
        }

        Setting::setPaymentMethods(
            collect(preg_split('/[\r\n]+/', (string) ($request->input('payment_methods') ?? '')))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->unique()
                ->values()
                ->toArray()
        );

        Setting::setValue('invoice_auto_notify', $request->boolean('invoice_auto_notify') ? '1' : '0');
        $notifyRecipients = preg_split('/[\r\n,]+/', (string) $request->input('invoice_notify_recipients', ''), -1, PREG_SPLIT_NO_EMPTY);
        Setting::setInvoiceNotificationRecipients($notifyRecipients ?? []);

        return redirect()
            ->route('settings.edit')
            ->with('status', 'Ayarlar güncellendi.');
    }

    public function downloadBackup(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'password' => ['nullable', 'string', 'min:4'],
        ]);

        $payload = $this->buildBackupPayload();
        $structure = $this->buildPlainBackupStructure($payload);

        $password = $validated['password'] ?? null;
        $filenameSuffix = '.json';

        if (! empty($password)) {
            $structure = $this->backupEncryption->encrypt($structure, $password);
            $filenameSuffix = '.enc.json';
        }

        $json = json_encode($structure, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            abort(500, 'Yedek oluşturulurken hata meydana geldi.');
        }

        $filename = 'muhasebe-backup-' . now()->format('Ymd_His') . $filenameSuffix;

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    public function restoreBackup(Request $request): RedirectResponse|JsonResponse
    {
        $mode = $request->input('mode', 'restore');

        $rules = [
            'backup_file' => ['required', 'file', 'mimetypes:application/json,text/plain'],
            'restore_password' => ['nullable', 'string', 'min:4'],
        ];

        if ($mode !== 'preview') {
            $rules['confirm_restore'] = ['accepted'];
        }

        $validated = $request->validate($rules, [
            'confirm_restore.accepted' => 'Lütfen geri yükleme işlemini onaylayın.',
        ]);

        if ($mode !== 'preview' && ! config('backup.restore_enabled')) {
            return $this->respondWithError(
                $request,
                'Yedek geri yükleme özelliği bu ortamda devre dışı. Lütfen sistem yöneticinizle iletişime geçin.',
                403,
                $request->except('backup_file')
            );
        }

        $uploadedFile = $request->file('backup_file');
        $maxBytes = (int) config('backup.max_upload_mb', 20) * 1024 * 1024;
        $fileSize = $uploadedFile?->getSize() ?? 0;

        if ($fileSize <= 0 && $uploadedFile) {
            $fileSize = filesize($uploadedFile->getRealPath()) ?: 0;
        }

        if ($fileSize > $maxBytes) {
            return $this->respondWithError(
                $request,
                'Yedek dosyası izin verilen maksimum boyutu aşıyor. Lütfen dosyayı küçültün veya segmentlere ayırın.',
                422,
                $request->except('backup_file')
            );
        }

        $password = $validated['restore_password'] ?? null;
        $throttleKey = $this->restoreThrottleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::RESTORE_THROTTLE_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $message = 'Çok fazla başarısız yedek çözme denemesi. Lütfen ' . $seconds . ' saniye sonra yeniden deneyin.';

            return $this->respondWithError($request, $message, 429, $request->except('backup_file'));
        }

        try {
            $parsed = $this->parseBackupFile(
                $uploadedFile->getRealPath(),
                $password
            );

            $data = $this->validateBackupData($parsed['data']);
        } catch (ValidationException $exception) {
            return $this->respondWithError(
                $request,
                $exception->errors(),
                422,
                $request->except('backup_file')
            );
        } catch (\Throwable $exception) {
            RateLimiter::hit($throttleKey, self::RESTORE_THROTTLE_DECAY_SECONDS);
            Log::warning('backup.preview.failed', [
                'mode' => $mode,
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return $this->respondWithError(
                $request,
                $exception->getMessage(),
                400,
                $request->except('backup_file')
            );
        }

        RateLimiter::clear($throttleKey);

        $counts = $this->summarizeBackupData($data);

        if ($mode === 'preview') {
            $payload = [
                'meta' => $parsed['meta'],
                'counts' => $counts,
            ];

            Log::debug('backup.preview.success', [
                'user_id' => $request->user()?->id,
                'generated_at' => $payload['meta']['generated_at'] ?? null,
                'counts' => $payload['counts'],
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'preview' => $payload,
                    'html' => view('settings.partials.backup-preview', ['preview' => $payload])->render(),
                ]);
            }

            return back()
                ->with('backup_preview', $payload)
                ->withInput($request->except('backup_file'));
        }

        $tables = [
            'transactions' => Transaction::class,
            'payments' => Payment::class,
            'invoice_line_items' => InvoiceLineItem::class,
            'invoices' => Invoice::class,
            'tax_declarations' => TaxDeclaration::class,
            'firm_tax_forms' => FirmTaxForm::class,
            'tax_forms' => TaxForm::class,
            'firms' => Firm::class,
            'settings' => Setting::class,
        ];

        DB::beginTransaction();

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tables as $key => $modelClass) {
                $records = $data[$key] ?? [];

                /** @var \Illuminate\Database\Eloquent\Model $model */
                $model = new $modelClass();
                $table = $model->getTable();

                DB::table($table)->truncate();

                if (! empty($records)) {
                    DB::table($table)->insert($records);
                }
            }
        } catch (\Throwable $exception) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            report($exception);
            RateLimiter::hit($throttleKey, self::RESTORE_THROTTLE_DECAY_SECONDS);

            return $this->respondWithError(
                $request,
                'Yedek yüklenirken hata oluştu: ' . $exception->getMessage(),
                500,
                $request->except('backup_file')
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::commit();

        $statusMessage = sprintf(
            'Yedek başarıyla geri yüklendi. Firmalar: %d, Faturalar: %d, Tahsilatlar: %d, İşlemler: %d, Beyannameler: %d, Ayarlar: %d.',
            $counts['firms'],
            $counts['invoices'],
            $counts['payments'],
            $counts['transactions'],
            $counts['tax_declarations'],
            $counts['settings'],
        );

        Log::notice('backup.restore.success', [
            'user_id' => $request->user()?->id,
            'counts' => $counts,
        ]);

        return back()->with('status', $statusMessage);
    }

    /**
     * Provides a unified error response for both HTML and JSON consumers.
     *
     * @param  array<string,mixed>  $input
     */
    private function respondWithError(Request $request, string|array $errors, int $status, array $input = []): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            $payload = is_array($errors)
                ? ['message' => 'Doğrulama hatası oluştu.', 'errors' => $errors]
                : ['message' => $errors];

            return response()->json($payload, $status);
        }

        $redirect = back();

        if (is_array($errors)) {
            $redirect = $redirect->withErrors($errors);
        } else {
            $redirect = $redirect->withErrors(['backup_file' => $errors]);
        }

        if (! empty($input)) {
            $redirect = $redirect->withInput($input);
        }

        return $redirect;
    }

    private function restoreThrottleKey(Request $request): string
    {
        $userPart = $request->user()?->id ?? 'guest';

        return sprintf(
            'backup-restore:%s:%s',
            $userPart,
            sha1((string) $request->ip())
        );
    }

    public function exportCsv(string $type): StreamedResponse
    {
        $filename = $type . '-export-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($type) {
            $handle = fopen('php://output', 'w');

            switch ($type) {
                case 'firms':
                    $headers = ['id', 'name', 'tax_no', 'contact_person', 'contact_email', 'contact_phone', 'monthly_fee', 'status', 'contract_start_at', 'notes'];
                    fputcsv($handle, $headers);
                    Firm::orderBy('name')->chunk(200, function ($firms) use ($handle, $headers) {
                        foreach ($firms as $firm) {
                            $row = [];
                            foreach ($headers as $column) {
                                $row[] = $firm->{$column};
                            }
                            fputcsv($handle, $row);
                        }
                    });
                    break;

                case 'invoices':
                    $headers = ['id', 'firm_name', 'date', 'due_date', 'amount', 'status', 'description'];
                    fputcsv($handle, $headers);
                    Invoice::with('firm')->orderBy('date')->chunk(200, function ($invoices) use ($handle) {
                        foreach ($invoices as $invoice) {
                            fputcsv($handle, [
                                $invoice->id,
                                $invoice->firm?->name,
                                optional($invoice->date)->format('Y-m-d'),
                                optional($invoice->due_date)->format('Y-m-d'),
                                $invoice->amount,
                                $invoice->status,
                                $invoice->description,
                            ]);
                        }
                    });
                    break;

                case 'payments':
                    $headers = ['id', 'firm_name', 'invoice_id', 'date', 'amount', 'method', 'note'];
                    fputcsv($handle, $headers);
                    Payment::with(['firm', 'invoice'])->orderBy('date')->chunk(200, function ($payments) use ($handle) {
                        foreach ($payments as $payment) {
                            fputcsv($handle, [
                                $payment->id,
                                $payment->firm?->name,
                                $payment->invoice?->id,
                                optional($payment->date)->format('Y-m-d'),
                                $payment->amount,
                                $payment->method,
                                $payment->note,
                            ]);
                        }
                    });
                    break;

                default:
                    fputcsv($handle, ['Desteklenmeyen veri türü.']);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function logo(string $path): BinaryFileResponse
    {
        $cleanPath = ltrim($path, '/');

        if (! str_starts_with($cleanPath, 'logos/')) {
            abort(403);
        }

        if (! Storage::disk('public')->exists($cleanPath)) {
            abort(404);
        }

        $fullPath = storage_path('app/public/' . $cleanPath);

        return response()->file($fullPath, [
            'Cache-Control' => 'public, max-age=86400',
            'Last-Modified' => gmdate('D, d M Y H:i:s', filemtime($fullPath)) . ' GMT',
        ]);
    }

    private function buildBackupPayload(): array
    {
        return [
            'firms' => Firm::withTrashed()->get()->toArray(),
            'invoices' => Invoice::withTrashed()->get()->toArray(),
            'invoice_line_items' => InvoiceLineItem::all()->toArray(),
            'payments' => Payment::withTrashed()->get()->toArray(),
            'transactions' => Transaction::withTrashed()->get()->toArray(),
            'tax_forms' => TaxForm::all()->toArray(),
            'firm_tax_forms' => FirmTaxForm::all()->toArray(),
            'tax_declarations' => TaxDeclaration::all()->toArray(),
            'settings' => Setting::all()->toArray(),
        ];
    }

    private function buildPlainBackupStructure(array $payload): array
    {
        $checksum = hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE));

        return [
            'meta' => [
                'version' => '1.1',
                'generated_at' => now()->toIso8601String(),
                'encrypted' => false,
                'checksum' => $checksum,
            ],
            'data' => $payload,
        ];
    }

    private function parseBackupFile(string $path, ?string $password): array
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException('Yedek dosyası okunamadı.');
        }

        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            throw new \RuntimeException('Geçersiz yedek dosyası. JSON formatı okunamadı.');
        }

        if (($decoded['meta']['encrypted'] ?? false) === true) {
            if (empty($password)) {
                throw new \RuntimeException('Bu yedek şifrelenmiş. Lütfen şifre girin.');
            }

            $plainStructure = $this->backupEncryption->decrypt($decoded, $password);

            if (! is_array($plainStructure) || ! isset($plainStructure['data'])) {
                throw new \RuntimeException('Çözülen yedek beklenen yapıda değil.');
            }

            return [
                'meta' => array_merge($plainStructure['meta'] ?? [], ['encrypted' => false]),
                'data' => $plainStructure['data'] ?? [],
            ];
        }

        if (isset($decoded['data'])) {
            return [
                'meta' => array_merge([
                    'version' => $decoded['meta']['version'] ?? '1.0',
                    'generated_at' => $decoded['meta']['generated_at'] ?? null,
                    'encrypted' => false,
                    'checksum' => $decoded['meta']['checksum'] ?? hash('sha256', json_encode($decoded['data'], JSON_UNESCAPED_UNICODE)),
                ], $decoded['meta'] ?? []),
                'data' => $decoded['data'],
            ];
        }

        return [
            'meta' => [
                'version' => 'legacy',
                'generated_at' => null,
                'encrypted' => false,
                'checksum' => hash('sha256', json_encode($decoded, JSON_UNESCAPED_UNICODE)),
            ],
            'data' => $decoded,
        ];
    }

    private function validateBackupData(array $data): array
    {
        $rules = [
            'firms' => ['required', 'array'],
            'firms.*.id' => ['required', 'integer'],
            'firms.*.name' => ['required', 'string'],
            'firms.*.status' => ['required', 'string', 'in:active,inactive'],
            'invoices' => ['required', 'array'],
            'invoices.*.id' => ['required', 'integer'],
            'invoices.*.firm_id' => ['required', 'integer'],
            'invoices.*.status' => ['required', 'string', 'in:unpaid,partial,paid,cancelled'],
            'invoices.*.date' => ['required'],
            'payments' => ['required', 'array'],
            'payments.*.id' => ['required', 'integer'],
            'payments.*.firm_id' => ['required', 'integer'],
            'payments.*.amount' => ['required'],
            'transactions' => ['required', 'array'],
            'transactions.*.id' => ['required', 'integer'],
            'transactions.*.firm_id' => ['required', 'integer'],
            'transactions.*.type' => ['required', 'in:debit,credit'],
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string'],
        ];

        $validator = Validator::make($data, $rules);
        $validator->validate();

        foreach (['firms', 'invoices', 'invoice_line_items', 'payments', 'transactions', 'tax_forms', 'firm_tax_forms', 'tax_declarations', 'settings'] as $table) {
            $data[$table] = array_values($data[$table] ?? []);
        }

        return $data;
    }

    private function summarizeBackupData(array $data): array
    {
        return [
            'firms' => count($data['firms'] ?? []),
            'invoices' => count($data['invoices'] ?? []),
            'invoice_line_items' => count($data['invoice_line_items'] ?? []),
            'payments' => count($data['payments'] ?? []),
            'transactions' => count($data['transactions'] ?? []),
            'tax_forms' => count($data['tax_forms'] ?? []),
            'firm_tax_forms' => count($data['firm_tax_forms'] ?? []),
            'tax_declarations' => count($data['tax_declarations'] ?? []),
            'settings' => count($data['settings'] ?? []),
        ];
    }
}

