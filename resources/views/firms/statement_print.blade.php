@php
    use App\Support\Format;
@endphp

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hesap Ekstresi - {{ $firm->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            font-size: 12px; 
            color: #1f2933; 
            margin: 0;
            padding: 20px;
        }
        h1, h2, h3, h4 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #cbd2d9; padding: 8px; text-align: left; }
        th { background: #f5f7fa; font-weight: 600; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-table td { border: none; padding: 4px 8px; }
        
        /* Print Styles */
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { 
                size: A4; 
                margin: 15mm;
            }
        }
        
        /* Print Button */
        .print-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            margin: -20px -20px 20px -20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .print-bar h4 { color: white; margin: 0; }
        .print-btn {
            background: white;
            color: #667eea;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }
        .print-btn:hover { background: #f0f0f0; }
        
        /* Summary Cards */
        .summary-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
        }
        .summary-card label { 
            font-size: 11px; 
            color: #64748b; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .summary-card .value { font-size: 18px; font-weight: 700; color: #1e293b; }
        .summary-card.debit .value { color: #dc2626; }
        .summary-card.credit .value { color: #16a34a; }
    </style>
</head>
<body>
    {{-- Print Bar --}}
    <div class="print-bar no-print">
        <div>
            <h4>{{ $firm->name }} - Hesap Ekstresi</h4>
            <small>{{ $summary['start']->format('d.m.Y') }} - {{ $summary['end']->format('d.m.Y') }}</small>
        </div>
        <button class="print-btn" onclick="window.print()">
            <i class="bi bi-printer"></i> Yazdır
        </button>
    </div>

    {{-- Header --}}
    <table class="summary-table" style="margin-bottom: 12px;">
        <tr>
            <td style="width: 60%;">
                @if (!empty($settings['company_logo_path']))
                    <img src="{{ route('settings.logo', ['path' => $settings['company_logo_path']]) }}" 
                         alt="Logo" style="max-height: 50px; max-width: 150px; margin-bottom: 8px;">
                    <br>
                @endif
                <h3>{{ $settings['company_name'] ?? config('app.name') }}</h3>
                @if (!empty($settings['company_address']))
                    <div>{{ $settings['company_address'] }}</div>
                @endif
                @if (!empty($settings['company_email']))
                    <div>{{ $settings['company_email'] }}</div>
                @endif
                @if (!empty($settings['company_phone']))
                    <div>{{ $settings['company_phone'] }}</div>
                @endif
            </td>
            <td class="text-right" style="width: 40%;">
                <h3>Hesap Ekstresi</h3>
                <div><strong>{{ $firm->name }}</strong></div>
                @if($firm->tax_no)
                    <div>VN: {{ $firm->tax_no }}</div>
                @endif
                <div>Dönem: {{ $summary['start']->format('d.m.Y') }} &mdash; {{ $summary['end']->format('d.m.Y') }}</div>
                <div style="font-size: 10px; color: #64748b;">Oluşturma: {{ now()->format('d.m.Y H:i') }}</div>
            </td>
        </tr>
    </table>

    {{-- Summary Cards --}}
    <div class="summary-cards">
        <div class="summary-card">
            <label>Başlangıç Bakiyesi</label>
            <div class="value">{{ Format::money($summary['initial_balance']) }}</div>
        </div>
        <div class="summary-card debit">
            <label>Toplam Borç</label>
            <div class="value">{{ Format::money($summary['total_debit']) }}</div>
        </div>
        <div class="summary-card credit">
            <label>Toplam Tahsilat</label>
            <div class="value">{{ Format::money($summary['total_credit']) }}</div>
        </div>
        <div class="summary-card">
            <label>Kapanış Bakiyesi</label>
            <div class="value">{{ Format::money($summary['closing_balance']) }}</div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <table>
        <thead>
            <tr>
                <th width="12%">Tarih</th>
                <th width="10%">Tip</th>
                <th>Açıklama</th>
                <th width="15%" class="text-right">Tutar</th>
                <th width="15%" class="text-right">Bakiye</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->date?->format('d.m.Y') }}</td>
                    <td>
                        @if($transaction->type === 'debit')
                            <span style="color: #dc2626;">Borç</span>
                        @else
                            <span style="color: #16a34a;">Alacak</span>
                        @endif
                    </td>
                    <td>{{ $transaction->description }}</td>
                    <td class="text-right" style="color: {{ $transaction->type === 'debit' ? '#dc2626' : '#16a34a' }};">
                        {{ $transaction->type === 'debit' ? '+' : '-' }} {{ Format::money($transaction->amount) }}
                    </td>
                    <td class="text-right" style="font-weight: 600;">{{ Format::money($transaction->running_balance) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #64748b; padding: 30px;">
                        Bu dönemde hareket bulunamadı.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f1f5f9; font-weight: 600;">
                <td colspan="3" class="text-right">Toplam:</td>
                <td class="text-right">
                    <span style="color: #dc2626;">+{{ Format::money($summary['total_debit']) }}</span>
                    <br>
                    <span style="color: #16a34a;">-{{ Format::money($summary['total_credit']) }}</span>
                </td>
                <td class="text-right">{{ Format::money($summary['closing_balance']) }}</td>
            </tr>
        </tfoot>
    </table>

    <script>
        // Sayfa yüklendiğinde otomatik yazdırma dialogunu aç
        window.onload = function() {
            // Küçük gecikme ile yazdır
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
