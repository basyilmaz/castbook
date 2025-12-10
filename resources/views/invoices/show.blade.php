@extends('layouts.app')

@php
    use App\Support\Format;

    $paidTotal = (float) ($invoice->payments_sum_amount ?? $invoice->payments->sum('amount'));
    $remaining = max(0, (float) $invoice->amount - $paidTotal);
    $statusMeta = match ($invoice->status) {
        'paid' => ['label' => 'Ödendi', 'class' => 'success'],
        'partial' => ['label' => 'Kısmi ödeme', 'class' => 'warning text-dark'],
        'cancelled' => ['label' => 'İptal', 'class' => 'secondary'],
        default => ['label' => 'Ödenmedi', 'class' => 'danger'],
    };
    $paymentMethods = \App\Models\Setting::getPaymentMethods();
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Fatura #{{ $invoice->id }}</h4>
        <small class="text-muted">{{ $invoice->firm->name }}</small>
    </div>
    <div class="d-flex gap-2">
        @if ($remaining > 0)
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#quickPaymentModal">
                <i class="bi bi-cash-coin me-1"></i>Hızlı Tahsilat
            </button>
        @endif
        <a href="{{ route('payments.create', ['invoice_id' => $invoice->id, 'firm_id' => $invoice->firm_id]) }}"
           class="btn btn-sm btn-outline-success">
            <i class="bi bi-plus-circle me-1"></i>Tahsilat Kaydet
        </a>
        @if (! in_array($invoice->status, ['paid', 'partial'], true))
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-secondary">Düzenle</a>
        @endif
        <a href="{{ route('invoices.duplicate', $invoice) }}" class="btn btn-sm btn-outline-primary" title="Bu faturayı kopyala">
            <i class="bi bi-copy me-1"></i>Kopyala
        </a>
        <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-light">Listeye Dön</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted text-uppercase small">Durum</span>
                    <span class="badge bg-{{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Fatura Tarihi</small>
                    <span class="fw-semibold">{{ $invoice->date?->format('d.m.Y') ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Vade Tarihi</small>
                    <span class="fw-semibold">{{ $invoice->due_date?->format('d.m.Y') ?? '-' }}</span>
                </div>
                <hr>
                <div class="mb-2">
                    <small class="text-muted d-block">Resmi Fatura No</small>
                    <span class="fw-semibold">{{ $invoice->official_number ?? '-' }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Fatura Tutarı</small>
                    <span class="h5 mb-0">{{ Format::money($invoice->amount) }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Ödenen</small>
                    <span class="fw-semibold text-success">{{ Format::money($paidTotal) }}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Kalan</small>
                    <span class="fw-semibold {{ $remaining > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ Format::money($remaining) }}
                    </span>
                </div>
                
                @if ($remaining > 0)
                    <div class="progress mt-3" style="height: 8px;">
                        @php $paidPercent = min(100, ($paidTotal / $invoice->amount) * 100); @endphp
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $paidPercent }}%" 
                             aria-valuenow="{{ $paidPercent }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted">%{{ number_format($paidPercent, 0) }} ödendi</small>
                @endif
                
                <hr>
                <div class="mb-2">
                    <small class="text-muted d-block">Açıklama</small>
                    <span>{{ $invoice->description ?? '-' }}</span>
                </div>
                <div class="mb-0">
                    <small class="text-muted d-block">Ödeme Tarihi</small>
                    <span>{{ $invoice->paid_at?->format('d.m.Y H:i') ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        {{-- Fatura Kalemleri --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Fatura Kalemleri</h6>
                <small class="text-muted">{{ $invoice->lineItems->count() }} kalem</small>
            </div>
            <div class="card-body p-0">
                @if ($invoice->lineItems->isEmpty())
                    <p class="text-center text-muted py-4 mb-0">Fatura kalemi bulunmuyor.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Açıklama</th>
                                    <th class="text-center">Miktar</th>
                                    <th class="text-end">Birim Fiyat</th>
                                    <th class="text-end">Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->lineItems as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ Format::money($item->unit_price) }}</td>
                                        <td class="text-end fw-semibold">{{ Format::money($item->amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                @if ($invoice->vat_rate > 0)
                                <tr>
                                    <td colspan="3" class="text-end">Ara Toplam:</td>
                                    <td class="text-end">{{ Format::money($invoice->subtotal ?? $invoice->amount) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">
                                        KDV ({{ $invoice->formatted_vat_rate }})
                                        <small class="text-muted">{{ $invoice->vat_included ? 'dahil' : 'hariç' }}</small>
                                    </td>
                                    <td class="text-end text-success">{{ Format::money($invoice->vat_amount ?? 0) }}</td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="3" class="text-end fw-semibold">Genel Toplam:</td>
                                    <td class="text-end fw-bold">{{ Format::money($invoice->amount) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tahsilatlar --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Tahsilatlar</h6>
                <small class="text-muted">{{ $invoice->payments->count() }} kayıt</small>
            </div>
            <div class="card-body p-0">
                @if ($invoice->payments->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-cash-coin fs-1 text-muted"></i>
                        <p class="text-muted mb-2">Bu faturaya bağlı tahsilat bulunmuyor.</p>
                        @if ($remaining > 0)
                            <button type="button" class="btn btn-sm btn-success" 
                                    data-bs-toggle="modal" data-bs-target="#quickPaymentModal">
                                <i class="bi bi-plus-circle me-1"></i>İlk Tahsilatı Ekle
                            </button>
                        @endif
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tarih</th>
                                    <th>Yöntem</th>
                                    <th class="text-end">Tutar</th>
                                    <th>Not</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->date?->format('d.m.Y') }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $payment->method ?? '-' }}</span>
                                        </td>
                                        <td class="text-end fw-semibold text-success">{{ Format::money($payment->amount) }}</td>
                                        <td>{{ $payment->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="text-end fw-semibold">Toplam Tahsilat:</td>
                                    <td class="text-end fw-bold text-success">{{ Format::money($paidTotal) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Hızlı Tahsilat Modal --}}
@if ($remaining > 0)
<div class="modal fade" id="quickPaymentModal" tabindex="-1" aria-labelledby="quickPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="firm_id" value="{{ $invoice->firm_id }}">
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="quickPaymentModalLabel">
                        <i class="bi bi-cash-coin me-2"></i>Hızlı Tahsilat
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Fatura Tutarı:</span>
                            <strong>{{ Format::money($invoice->amount) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Ödenen:</span>
                            <strong class="text-success">{{ Format::money($paidTotal) }}</strong>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span>Kalan Borç:</span>
                            <strong class="text-danger fs-5">{{ Format::money($remaining) }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Tahsilat Tutarı</label>
                        <div class="input-group">
                            <span class="input-group-text">₺</span>
                            <input type="number" step="0.01" min="0.01" max="{{ $remaining }}"
                                   class="form-control form-control-lg" id="amount" name="amount" 
                                   value="{{ $remaining }}" required>
                        </div>
                        <div class="form-text">
                            <button type="button" class="btn btn-sm btn-link p-0" 
                                    onclick="document.getElementById('amount').value = {{ $remaining }}">
                                Tamamını öde
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="date" class="form-label">Tarih</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="method" class="form-label">Ödeme Yöntemi</label>
                            <select class="form-select" id="method" name="method" required>
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method }}">{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="notes" class="form-label">Not (Opsiyonel)</label>
                        <input type="text" class="form-control" id="notes" name="notes" 
                               placeholder="Tahsilat notu...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i>Tahsilatı Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

