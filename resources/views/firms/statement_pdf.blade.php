@php
    use App\Support\Format;
@endphp

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hesap Ekstresi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2933; }
        h1, h2, h3, h4 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #cbd2d9; padding: 8px; text-align: left; }
        th { background: #f5f7fa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-table td { border: none; padding: 4px 8px; }
    </style>
</head>
<body>
    <table class="summary-table" style="margin-bottom: 12px;">
        <tr>
            <td>
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
            <td class="text-right">
                <h3>Hesap Ekstresi</h3>
                <div>{{ $firm->name }}</div>
                <div>Dönem: {{ $summary['start']->format('d.m.Y') }} &mdash; {{ $summary['end']->format('d.m.Y') }}</div>
                <div>Oluşturma: {{ now()->format('d.m.Y H:i') }}</div>
            </td>
        </tr>
    </table>

    <table class="summary-table" style="margin-bottom: 24px;">
        <tr>
            <td><strong>Başlangıç Bakiyesi:</strong></td>
            <td>{{ Format::money($summary['initial_balance']) }}</td>
            <td><strong>Toplam Borç:</strong></td>
            <td>{{ Format::money($summary['total_debit']) }}</td>
        </tr>
        <tr>
            <td><strong>Toplam Tahsilat:</strong></td>
            <td>{{ Format::money($summary['total_credit']) }}</td>
            <td><strong>Kapanış Bakiyesi:</strong></td>
            <td>{{ Format::money($summary['closing_balance']) }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="15%">Tarih</th>
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
                    <td>{{ $transaction->type === 'debit' ? 'Borç' : 'Alacak' }}</td>
                    <td>{{ $transaction->description }}</td>
                    <td class="text-right">
                        {{ $transaction->type === 'debit' ? '+' : '-' }} {{ Format::money($transaction->amount) }}
                    </td>
                    <td class="text-right">{{ Format::money($transaction->running_balance) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Bu dönemde hareket bulunamadı.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
