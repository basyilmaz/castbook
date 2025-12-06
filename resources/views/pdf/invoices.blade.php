@extends('pdf.layout')

@section('content')
@php
    $statusLabels = [
        'paid' => ['label' => 'Ödendi', 'class' => 'success'],
        'partial' => ['label' => 'Kısmi', 'class' => 'warning'],
        'unpaid' => ['label' => 'Bekliyor', 'class' => 'danger'],
        'cancelled' => ['label' => 'İptal', 'class' => 'secondary'],
    ];
@endphp

<div class="summary-box">
    <h3>{{ $year }} Yılı Özet</h3>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Fatura Sayısı</div>
            <div class="value">{{ number_format($summary['count'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Toplam Tutar</div>
            <div class="value">{{ number_format($summary['total_amount'], 2, ',', '.') }} ₺</div>
        </div>
        <div class="summary-item">
            <div class="label">Ödenen</div>
            <div class="value success">{{ number_format($summary['paid_amount'], 2, ',', '.') }} ₺</div>
        </div>
        <div class="summary-item">
            <div class="label">Bekleyen</div>
            <div class="value danger">{{ number_format($summary['unpaid_amount'], 2, ',', '.') }} ₺</div>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Fatura No</th>
            <th>Firma</th>
            <th>Tarih</th>
            <th>Vade</th>
            <th class="text-right">Tutar</th>
            <th class="text-center">Durum</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $invoice)
        @php
            $status = $statusLabels[$invoice->status] ?? $statusLabels['unpaid'];
        @endphp
        <tr>
            <td>{{ $invoice->official_number ?? '#' . $invoice->id }}</td>
            <td>{{ $invoice->firm->name ?? '-' }}</td>
            <td>{{ $invoice->date?->format('d.m.Y') }}</td>
            <td>{{ $invoice->due_date?->format('d.m.Y') ?? '-' }}</td>
            <td class="text-right">{{ number_format($invoice->amount, 2, ',', '.') }} ₺</td>
            <td class="text-center">
                <span class="badge badge-{{ $status['class'] }}">{{ $status['label'] }}</span>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4"><strong>TOPLAM ({{ $summary['count'] }} fatura)</strong></td>
            <td class="text-right">{{ number_format($summary['total_amount'], 2, ',', '.') }} ₺</td>
            <td></td>
        </tr>
    </tfoot>
</table>
@endsection
