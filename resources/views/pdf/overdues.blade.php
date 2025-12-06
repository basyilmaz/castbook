@extends('pdf.layout')

@section('content')
<div class="summary-box">
    <h3>Gecikmiş Ödemeler Özeti</h3>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Toplam Gecikmiş</div>
            <div class="value danger">{{ $invoices->count() }} adet</div>
        </div>
        <div class="summary-item">
            <div class="label">Toplam Tutar</div>
            <div class="value danger">{{ number_format($invoices->sum('amount'), 2, ',', '.') }} ₺</div>
        </div>
        <div class="summary-item">
            <div class="label">Bugünün Tarihi</div>
            <div class="value">{{ $today->format('d.m.Y') }}</div>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Firma</th>
            <th>Fatura No</th>
            <th>Fatura Tarihi</th>
            <th>Vade Tarihi</th>
            <th class="text-center">Geciken Gün</th>
            <th class="text-right">Tutar</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $invoice)
        @php
            $reference = $invoice->due_date ?? $invoice->date;
            $daysOverdue = (int) ($reference?->diffInDays($today) ?? 0);
        @endphp
        <tr>
            <td>{{ $invoice->firm->name ?? '-' }}</td>
            <td>{{ $invoice->official_number ?? '#' . $invoice->id }}</td>
            <td>{{ $invoice->date?->format('d.m.Y') }}</td>
            <td>{{ $invoice->due_date?->format('d.m.Y') ?? '-' }}</td>
            <td class="text-center text-danger fw-bold">{{ $daysOverdue }} gün</td>
            <td class="text-right text-danger fw-bold">{{ number_format($invoice->amount, 2, ',', '.') }} ₺</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5"><strong>TOPLAM</strong></td>
            <td class="text-right">{{ number_format($invoices->sum('amount'), 2, ',', '.') }} ₺</td>
        </tr>
    </tfoot>
</table>
@endsection
