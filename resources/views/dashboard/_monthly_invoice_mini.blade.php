{{-- Aylık Fatura Üret - Mini Widget --}}
@php
    use App\Support\Format;
    
    $activeFirmCount = \App\Models\Firm::where('status', 'active')->count();
    $totalMonthlyFee = \App\Models\Firm::where('status', 'active')->sum('monthly_fee');
@endphp

<div class="card border-0 shadow-sm h-100 monthly-invoice-mini">
    <div class="card-body monthly-invoice-mini-gradient p-3">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="mini-icon">
                <i class="bi bi-magic"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="text-white mb-0 fw-semibold">Aylık Fatura Üret</h6>
                <small class="text-white-50">{{ $activeFirmCount }} firma • {{ Format::money($totalMonthlyFee) }}</small>
            </div>
        </div>
        <form action="{{ route('invoices.sync-monthly') }}" method="POST">
            @csrf
            <div class="d-flex gap-2">
                <input type="month" 
                       name="month" 
                       class="form-control form-control-sm bg-white border-0" 
                       value="{{ now()->format('Y-m') }}">
                <button type="submit" class="btn btn-light btn-sm fw-semibold text-nowrap">
                    <i class="bi bi-lightning-charge-fill text-warning"></i> Üret
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.monthly-invoice-mini-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 0.5rem;
}

.monthly-invoice-mini .mini-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
}
</style>
