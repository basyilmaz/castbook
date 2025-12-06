{{-- Gelir Tahmin Raporu Widget - Kompakt --}}
@php
    use App\Support\Format;
@endphp

<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-gradient text-white py-3" style="background: linear-gradient(135deg, #00695c 0%, #26a69a 100%);">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-graph-up-arrow me-1"></i>
                Gelir Tahmini
            </h6>
            <span class="badge bg-white text-success">3 Ay</span>
        </div>
    </div>
    <div class="card-body py-3">
        {{-- Özet Bilgiler --}}
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="text-center">
                    <div class="fw-bold fs-4">{{ $forecast['firm_count'] }}</div>
                    <small class="text-muted">Aktif Firma</small>
                </div>
            </div>
            <div class="col-6">
                <div class="text-center">
                    <div class="fw-bold fs-4 {{ $forecast['collection_rate'] >= 80 ? 'text-success' : 'text-warning' }}">
                        %{{ $forecast['collection_rate'] }}
                    </div>
                    <small class="text-muted">Tahsilat Oranı</small>
                </div>
            </div>
        </div>

        <hr class="my-2">

        {{-- Toplam Tahmin --}}
        <div class="text-center">
            <small class="text-muted d-block">3 Aylık Tahmin</small>
            <div class="fw-bold fs-3 text-success">
                {{ Format::money($forecast['total_forecast']) }}
            </div>
            <small class="text-muted">
                (Aylık: {{ Format::money($forecast['monthly_total']) }})
            </small>
        </div>
    </div>
    <div class="card-footer bg-white border-top py-2 text-center">
        <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Son 3 ay tahsilat oranı baz alınır
        </small>
    </div>
</div>
