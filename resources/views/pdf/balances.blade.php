@extends('pdf.layout')

@section('content')
<div class="summary-box">
    <h3>Özet Bilgiler</h3>
    <div class="summary-grid">
        <div class="summary-item">
            <div class="label">Toplam Borç</div>
            <div class="value danger">{{ number_format($totals['debit'], 2, ',', '.') }} ₺</div>
        </div>
        <div class="summary-item">
            <div class="label">Toplam Tahsilat</div>
            <div class="value success">{{ number_format($totals['credit'], 2, ',', '.') }} ₺</div>
        </div>
        <div class="summary-item">
            <div class="label">Kalan Bakiye</div>
            <div class="value {{ $totals['balance'] > 0 ? 'danger' : 'success' }}">
                {{ number_format($totals['balance'], 2, ',', '.') }} ₺
            </div>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Firma Adı</th>
            <th>Vergi No</th>
            <th class="text-right">Toplam Borç</th>
            <th class="text-right">Toplam Tahsilat</th>
            <th class="text-right">Kalan Bakiye</th>
            <th class="text-center">Durum</th>
        </tr>
    </thead>
    <tbody>
        @foreach($firms as $firm)
        @php
            $balance = (float)$firm->debit_total - (float)$firm->credit_total;
        @endphp
        <tr>
            <td>{{ $firm->name }}</td>
            <td>{{ $firm->tax_no ?? '-' }}</td>
            <td class="text-right text-danger">{{ number_format($firm->debit_total, 2, ',', '.') }} ₺</td>
            <td class="text-right text-success">{{ number_format($firm->credit_total, 2, ',', '.') }} ₺</td>
            <td class="text-right {{ $balance > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                {{ number_format($balance, 2, ',', '.') }} ₺
            </td>
            <td class="text-center">
                <span class="badge badge-{{ $firm->status === 'active' ? 'success' : 'secondary' }}">
                    {{ $firm->status === 'active' ? 'Aktif' : 'Pasif' }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"><strong>TOPLAM</strong></td>
            <td class="text-right">{{ number_format($totals['debit'], 2, ',', '.') }} ₺</td>
            <td class="text-right">{{ number_format($totals['credit'], 2, ',', '.') }} ₺</td>
            <td class="text-right">{{ number_format($totals['balance'], 2, ',', '.') }} ₺</td>
            <td></td>
        </tr>
    </tfoot>
</table>
@endsection
