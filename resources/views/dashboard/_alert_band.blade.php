{{-- Acil Uyarılar Bandı - Dashboard Üstte --}}
@php
    use App\Models\TaxDeclaration;
    use App\Models\Invoice;
    use Illuminate\Support\Carbon;
    
    $now = Carbon::now();
    
    // Bugün son gün beyanname sayısı
    $todayDueDeclarations = TaxDeclaration::query()
        ->whereIn('status', ['pending', 'filed'])
        ->whereDate('due_date', $now->toDateString())
        ->count();
    
    // Bugün vadesi gelen fatura sayısı
    $todayDueInvoices = Invoice::query()
        ->whereIn('status', ['unpaid', 'partial'])
        ->whereDate('due_date', $now->toDateString())
        ->count();
    
    // Gecikmiş toplam (beyanname + fatura)
    $overdueDeclarationCount = TaxDeclaration::query()
        ->whereIn('status', ['pending', 'filed'])
        ->where('due_date', '<', $now->toDateString())
        ->count();
    
    $overdueInvoiceCount = Invoice::query()
        ->whereIn('status', ['unpaid', 'partial'])
        ->where('due_date', '<', $now->toDateString())
        ->count();
    
    $totalOverdue = $overdueDeclarationCount + $overdueInvoiceCount;
    
    // Genel durum
    $hasUrgent = $todayDueDeclarations > 0 || $todayDueInvoices > 0;
    $hasOverdue = $totalOverdue > 0;
    $allClear = !$hasUrgent && !$hasOverdue;
@endphp

@if(!$allClear)
<div class="alert-band mb-4">
    <div class="row g-2">
        {{-- Bugün Son Gün Beyannameler --}}
        @if($todayDueDeclarations > 0)
        <div class="col-md-4">
            <div class="alert-band-item alert-band-danger">
                <div class="d-flex align-items-center gap-3">
                    <div class="alert-band-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <div class="alert-band-value">{{ $todayDueDeclarations }}</div>
                        <div class="alert-band-label">Bugün Son Gün Beyanname</div>
                    </div>
                    <a href="{{ route('tax-declarations.index', ['due_today' => 1]) }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
        @endif
        
        {{-- Bugün Vadesi Gelen Faturalar --}}
        @if($todayDueInvoices > 0)
        <div class="col-md-4">
            <div class="alert-band-item alert-band-warning">
                <div class="d-flex align-items-center gap-3">
                    <div class="alert-band-icon">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div>
                        <div class="alert-band-value">{{ $todayDueInvoices }}</div>
                        <div class="alert-band-label">Bugün Vadesi Gelen Fatura</div>
                    </div>
                    <a href="{{ route('invoices.index', ['due_today' => 1]) }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
        @endif
        
        {{-- Gecikmiş Toplam --}}
        @if($totalOverdue > 0)
        <div class="col-md-4">
            <div class="alert-band-item alert-band-danger-dark">
                <div class="d-flex align-items-center gap-3">
                    <div class="alert-band-icon">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div>
                        <div class="alert-band-value">{{ $totalOverdue }}</div>
                        <div class="alert-band-label">
                            Gecikmiş 
                            <small class="opacity-75">({{ $overdueDeclarationCount }} beyanname, {{ $overdueInvoiceCount }} fatura)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@else
<div class="alert-band mb-4">
    <div class="alert-band-item alert-band-success">
        <div class="d-flex align-items-center justify-content-center gap-3 py-2">
            <i class="bi bi-check-circle-fill fs-4"></i>
            <div>
                <strong>Tüm işler yolunda!</strong>
                <span class="ms-2 opacity-75">Bugün için acil bekleyen iş bulunmuyor.</span>
            </div>
        </div>
    </div>
</div>
@endif

<style>
.alert-band-item {
    position: relative;
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    color: white;
    transition: transform 0.2s, box-shadow 0.2s;
}

.alert-band-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.alert-band-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.alert-band-danger-dark {
    background: linear-gradient(135deg, #721c24 0%, #491217 100%);
}

.alert-band-warning {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
}

.alert-band-success {
    background: linear-gradient(135deg, #198754 0%, #146c43 100%);
}

.alert-band-icon {
    font-size: 1.75rem;
    opacity: 0.9;
}

.alert-band-value {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1;
}

.alert-band-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .alert-band-item {
        padding: 0.75rem 1rem;
    }
    .alert-band-value {
        font-size: 1.5rem;
    }
    .alert-band-icon {
        font-size: 1.5rem;
    }
}
</style>
