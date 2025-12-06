@extends('layouts.app')

@php
    use App\Support\Format;
@endphp

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Tahsilat Kaydı</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('payments.store') }}" method="POST">
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <h6 class="fw-semibold mb-2">Lütfen formu kontrol edin.</h6>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="firm_id" class="form-label">Firma</label>
                                <select name="firm_id" id="firm_id" class="form-select @error('firm_id') is-invalid @enderror" required autofocus>
                                    <option value="">Firma seçin</option>
                                    @foreach ($firms as $firm)
                                        <option value="{{ $firm->id }}" @selected(old('firm_id', $selectedFirmId) == $firm->id)>
                                            {{ $firm->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('firm_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="invoice_id" class="form-label">Fatura</label>
                                <select name="invoice_id" id="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror">
                                    <option value="">Fatura seç (opsiyonel)</option>
                                    @foreach ($invoices as $invoice)
                                        <option value="{{ $invoice->id }}" @selected(old('invoice_id', $selectedInvoiceId) == $invoice->id)>
                                            #{{ $invoice->id }} · {{ $invoice->date->format('d.m.Y') }} · {{ Format::money($invoice->amount) }}
                                            (Kalan: {{ Format::money($invoice->remaining_amount) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('invoice_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Tutar</label>
                                <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount', $payment->amount) }}" required>
                                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="method" class="form-label">Tahsilat Yöntemi</label>
                                <select name="method" id="method" class="form-select @error('method') is-invalid @enderror" required>
                                    <option value="">Seçiniz</option>
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method }}" @selected(old('method') === $method)>{{ $method }}</option>
                                    @endforeach
                                </select>
                                @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="date" class="form-label">Tarih</label>
                                <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror"
                                       value="{{ old('date', optional($payment->date)->format('Y-m-d')) }}" required>
                                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="note" class="form-label">Açıklama</label>
                                <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror" rows="1"
                                          placeholder="İsteğe bağlı kısa not">{{ old('note') }}</textarea>
                                @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('payments.index') }}" class="btn btn-light">İptal</a>
                            <button type="submit" class="btn btn-primary">Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Firma Özeti</h6>
                </div>
                <div class="card-body">
                    @if ($firmSummary)
                        <dl class="row mb-0">
                            <dt class="col-5 text-muted">Firma Adı</dt>
                            <dd class="col-7">{{ $firmSummary['name'] }}</dd>

                            <dt class="col-5 text-muted">Güncel Bakiye</dt>
                            <dd class="col-7 {{ $firmSummary['balance'] > 0 ? 'text-danger' : ($firmSummary['balance'] < 0 ? 'text-success' : 'text-muted') }}">
                                {{ Format::money($firmSummary['balance']) }}
                            </dd>

                            <dt class="col-5 text-muted">Son Tahsilat</dt>
                            <dd class="col-7">{{ $firmSummary['last_payment_at'] ?? '-' }}</dd>
                        </dl>
                    @else
                        <p class="text-muted mb-0">Özet bilgisi için firma seçiniz.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('firm_id').addEventListener('change', function () {
        const firmId = this.value;
        const url = new URL(window.location.href);

        if (firmId) {
            url.searchParams.set('firm_id', firmId);
        } else {
            url.searchParams.delete('firm_id');
        }

        url.searchParams.delete('invoice_id');
        window.location.href = url.toString();
    });
</script>
@endsection
