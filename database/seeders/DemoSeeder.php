<?php

namespace Database\Seeders;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\TaxDeclaration;
use App\Models\TaxForm;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Demo verileri oluştur
     */
    public function run(): void
    {
        $this->command->info('🚀 Demo verileri oluşturuluyor...');

        // Kullanıcılar
        $this->createUsers();

        // Ayarlar
        $this->createSettings();

        // Vergi formları
        $this->call(TaxFormSeeder::class);
        $taxForms = TaxForm::all();

        // Firmalar
        $firms = $this->createFirms($taxForms);

        // Faturalar ve ödemeler
        $this->createInvoicesAndPayments($firms);

        // Beyannameler
        $this->createTaxDeclarations($firms, $taxForms);

        $this->command->info('✅ Demo verileri başarıyla oluşturuldu!');
        $this->command->newLine();
        $this->command->table(['Hesap', 'E-posta', 'Şifre'], [
            ['Admin', 'demo@castbook.dev', 'demo123'],
            ['Personel', 'personel@castbook.dev', 'demo123'],
        ]);
    }

    protected function createUsers(): void
    {
        $this->command->info('👤 Kullanıcılar oluşturuluyor...');

        User::query()->updateOrCreate(
            ['email' => 'demo@castbook.dev'],
            [
                'name' => 'Demo Yönetici',
                'password' => Hash::make('demo123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'personel@castbook.dev'],
            [
                'name' => 'Demo Personel',
                'password' => Hash::make('demo123'),
                'role' => 'user',
                'is_active' => true,
            ]
        );
    }

    protected function createSettings(): void
    {
        $this->command->info('⚙️ Ayarlar yapılandırılıyor...');

        Setting::setValue('company_name', 'Demo Muhasebe Ofisi');
        Setting::setValue('company_address', 'Atatürk Cad. No:123, Kadıköy/İstanbul');
        Setting::setValue('company_phone', '+90 216 123 45 67');
        Setting::setValue('company_email', 'info@castbook.dev');
        Setting::setValue('invoice_day', '1');
        Setting::setValue('invoice_due_days', '15');
        Setting::setValue('invoice_default_description', 'Aylık muhasebe danışmanlık hizmeti');
        Setting::setPaymentMethods(['Nakit', 'Banka Havale', 'EFT', 'Kredi Kartı', 'Çek']);
        Setting::setValue('enable_email_notifications', '1');
        Setting::setValue('notification_recipients', 'demo@castbook.dev');
    }

    protected function createFirms($taxForms): array
    {
        $this->command->info('🏢 Firmalar oluşturuluyor...');

        $firmData = [
            [
                'name' => 'ABC Teknoloji A.Ş.',
                'company_type' => 'Anonim Şirket',
                'tax_no' => '1234567890',
                'contact_person' => 'Ahmet Yılmaz',
                'contact_email' => 'ahmet@abcteknoloji.com',
                'contact_phone' => '+90 532 111 22 33',
                'monthly_fee' => 3500.00,
                'status' => 'active',
                'notes' => 'Yazılım geliştirme firması. Aylık KDV ve Muhtasar beyanları.',
            ],
            [
                'name' => 'XYZ İnşaat Ltd. Şti.',
                'company_type' => 'Limited Şirket',
                'tax_no' => '9876543210',
                'contact_person' => 'Mehmet Demir',
                'contact_email' => 'mehmet@xyzinsaat.com',
                'contact_phone' => '+90 533 444 55 66',
                'monthly_fee' => 4500.00,
                'status' => 'active',
                'notes' => 'İnşaat ve taahhüt işleri. SGK işlemleri dahil.',
            ],
            [
                'name' => 'Güneş Tekstil',
                'company_type' => 'Şahıs Şirketi',
                'tax_no' => '5678901234',
                'contact_person' => 'Ayşe Güneş',
                'contact_email' => 'ayse@gunestekstil.com',
                'contact_phone' => '+90 534 777 88 99',
                'monthly_fee' => 2000.00,
                'status' => 'active',
                'notes' => 'Tekstil üretim ve satış.',
            ],
            [
                'name' => 'Yıldız Gıda San. ve Tic.',
                'company_type' => 'Limited Şirket',
                'tax_no' => '1357924680',
                'contact_person' => 'Ali Yıldız',
                'contact_email' => 'ali@yildizgida.com',
                'contact_phone' => '+90 535 222 33 44',
                'monthly_fee' => 5000.00,
                'status' => 'active',
                'notes' => 'Gıda üretimi ve dağıtımı. E-Fatura kullanıyor.',
            ],
            [
                'name' => 'Kuzey Danışmanlık',
                'company_type' => 'Şahıs Şirketi',
                'tax_no' => '2468013579',
                'contact_person' => 'Elif Kuzey',
                'contact_email' => 'elif@kuzeydanismanlik.com',
                'contact_phone' => '+90 536 555 66 77',
                'monthly_fee' => 1500.00,
                'status' => 'active',
                'notes' => 'Yönetim danışmanlığı.',
            ],
            [
                'name' => 'Eskici Oto Galeri',
                'company_type' => 'Şahıs Şirketi',
                'tax_no' => '9517538426',
                'contact_person' => 'Osman Eskici',
                'contact_email' => 'osman@eskicioto.com',
                'contact_phone' => '+90 537 888 99 00',
                'monthly_fee' => 2500.00,
                'status' => 'passive',
                'notes' => 'Pasif müşteri - 2024 sonu itibarıyla sözleşme sonlandırıldı.',
            ],
        ];

        $firms = [];
        foreach ($firmData as $data) {
            $firm = Firm::query()->updateOrCreate(
                ['tax_no' => $data['tax_no']],
                array_merge($data, [
                    'contract_start_at' => Carbon::now()->subMonths(rand(6, 24)),
                ])
            );

            // Firma-TaxForm ilişkisi
            if ($firm->status === 'active') {
                $firm->taxForms()->sync($taxForms->random(rand(2, 4))->pluck('id'));
            }

            $firms[] = $firm;
        }

        return $firms;
    }

    protected function createInvoicesAndPayments(array $firms): void
    {
        $this->command->info('📄 Faturalar ve ödemeler oluşturuluyor...');

        foreach ($firms as $firm) {
            if ($firm->status !== 'active') {
                continue;
            }

            // Son 6 ay için faturalar oluştur
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i)->startOfMonth();
                $dueDate = $date->copy()->addDays(15);

                $invoice = Invoice::create([
                    'firm_id' => $firm->id,
                    'date' => $date,
                    'due_date' => $dueDate,
                    'amount' => $firm->monthly_fee,
                    'description' => 'Aylık muhasebe hizmeti - ' . $date->locale('tr')->isoFormat('MMMM YYYY'),
                    'official_number' => 'F' . $date->format('Ym') . '-' . str_pad($firm->id, 3, '0', STR_PAD_LEFT),
                    'status' => 'unpaid',
                ]);

                // Line item
                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Aylık muhasebe danışmanlık hizmeti',
                    'quantity' => 1,
                    'unit_price' => $firm->monthly_fee,
                    'amount' => $firm->monthly_fee,
                    'sort_order' => 0,
                    'item_type' => 'service',
                ]);

                // Transaction (borç)
                Transaction::create([
                    'firm_id' => $firm->id,
                    'invoice_id' => $invoice->id,
                    'type' => 'debit',
                    'amount' => $firm->monthly_fee,
                    'date' => $date,
                    'description' => $invoice->description,
                ]);

                // Eski aylar için ödeme oluştur (rastgele)
                if ($i >= 2 || rand(0, 1)) {
                    $paymentDate = $dueDate->copy()->subDays(rand(0, 5));
                    
                    $payment = Payment::create([
                        'firm_id' => $firm->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $firm->monthly_fee,
                        'date' => $paymentDate,
                        'method' => collect(['Banka Havale', 'EFT', 'Nakit', 'Kredi Kartı'])->random(),
                        'notes' => 'Tahsilat - ' . $paymentDate->format('d.m.Y'),
                    ]);

                    // Transaction (alacak)
                    Transaction::create([
                        'firm_id' => $firm->id,
                        'payment_id' => $payment->id,
                        'type' => 'credit',
                        'amount' => $firm->monthly_fee,
                        'date' => $paymentDate,
                        'description' => 'Tahsilat - ' . $invoice->description,
                    ]);

                    $invoice->update(['status' => 'paid', 'paid_at' => $paymentDate]);
                } elseif ($i === 1 && rand(0, 1)) {
                    // Kısmi ödeme
                    $partialAmount = round($firm->monthly_fee * 0.5, 2);
                    $paymentDate = $dueDate->copy()->subDays(rand(0, 3));
                    
                    $payment = Payment::create([
                        'firm_id' => $firm->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $partialAmount,
                        'date' => $paymentDate,
                        'method' => 'Nakit',
                        'notes' => 'Kısmi ödeme',
                    ]);

                    Transaction::create([
                        'firm_id' => $firm->id,
                        'payment_id' => $payment->id,
                        'type' => 'credit',
                        'amount' => $partialAmount,
                        'date' => $paymentDate,
                        'description' => 'Kısmi tahsilat - ' . $invoice->description,
                    ]);

                    $invoice->update(['status' => 'partial']);
                }
            }
        }
    }

    protected function createTaxDeclarations(array $firms, $taxForms): void
    {
        $this->command->info('📋 Beyannameler oluşturuluyor...');

        $activeFirms = collect($firms)->where('status', 'active');

        foreach ($activeFirms as $firm) {
            $firmTaxForms = $firm->taxForms;

            if ($firmTaxForms->isEmpty()) {
                continue;
            }

            // Son 3 ay için beyannameler
            for ($i = 2; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $periodLabel = $month->locale('tr')->isoFormat('MMMM YYYY');

                foreach ($firmTaxForms->take(2) as $taxForm) {
                    $dueDay = $taxForm->default_due_day ?? 26;
                    $dueDate = $month->copy()->endOfMonth()->day(min($dueDay, $month->daysInMonth));

                    if ($dueDate->isPast()) {
                        $status = rand(0, 10) > 2 ? 'submitted' : 'pending'; // %80 tamamlanmış
                    } else {
                        $status = rand(0, 10) > 5 ? 'filed' : 'pending'; // %50 hazırlanmış
                    }

                    TaxDeclaration::query()->updateOrCreate(
                        [
                            'firm_id' => $firm->id,
                            'tax_form_id' => $taxForm->id,
                            'period_year' => $month->year,
                            'period_month' => $month->month,
                        ],
                        [
                            'due_date' => $dueDate,
                            'status' => $status,
                            'filed_at' => $status === 'submitted' ? $dueDate->copy()->subDays(rand(1, 5)) : null,
                            'submitted_at' => $status === 'submitted' ? $dueDate->copy()->subDays(rand(0, 3)) : null,
                            'notes' => $status === 'pending' ? 'Hazırlanıyor' : null,
                        ]
                    );
                }
            }
        }
    }
}
