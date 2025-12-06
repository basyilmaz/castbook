{{-- Son İşlemler Widget --}}
@php
    use App\Support\Format;
    use App\Models\Invoice;
    use App\Models\Payment;
    
    // Son 5 fatura
    $recentInvoices = Invoice::with('firm:id,name')
        ->orderByDesc('created_at')
        ->limit(5)
        ->get();
    
    // Son 5 tahsilat
    $recentPayments = Payment::with('firm:id,name')
        ->orderByDesc('created_at')
        ->limit(5)
        ->get();
@endphp

<div class="col-xl-6 col-12">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-receipt me-2 text-primary"></i>Son Faturalar
            </h6>
            <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-link text-decoration-none">
                Tümünü Gör <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="card-body p-0">
            @if($recentInvoices->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    Henüz fatura bulunmuyor
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($recentInvoices as $invoice)
                        <a href="{{ route('invoices.show', $invoice) }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light rounded-circle p-2">
                                    <i class="bi bi-receipt text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $invoice->firm->name ?? 'Belirtilmemiş' }}</div>
                                    <small class="text-muted">
                                        {{ $invoice->date?->format('d.m.Y') }} 
                                        @if($invoice->official_number)
                                            · #{{ $invoice->official_number }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold">{{ Format::money($invoice->amount) }}</div>
                                @php
                                    $statusClass = match($invoice->status) {
                                        'paid' => 'success',
                                        'partial' => 'warning',
                                        default => 'danger'
                                    };
                                    $statusLabel = match($invoice->status) {
                                        'paid' => 'Ödendi',
                                        'partial' => 'Kısmi',
                                        default => 'Bekliyor'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }} badge-sm">{{ $statusLabel }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div class="col-xl-6 col-12">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-cash-coin me-2 text-success"></i>Son Tahsilatlar
            </h6>
            <a href="{{ route('payments.index') }}" class="btn btn-sm btn-link text-decoration-none">
                Tümünü Gör <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="card-body p-0">
            @if($recentPayments->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    Henüz tahsilat bulunmuyor
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($recentPayments as $payment)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                    <i class="bi bi-cash-coin text-success"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $payment->firm->name ?? 'Belirtilmemiş' }}</div>
                                    <small class="text-muted">
                                        {{ $payment->date?->format('d.m.Y') }}
                                        @if($payment->method)
                                            · {{ $payment->method }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold text-success">+{{ Format::money($payment->amount) }}</div>
                                <small class="text-muted">
                                    {{ $payment->created_at?->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
