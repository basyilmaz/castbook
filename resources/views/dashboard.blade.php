@extends('layouts.app')

@php
    use App\Support\Format;
    $pageTitle = 'Ana Sayfa';
    $pageDescription = 'CastBook kontrol paneli - Günlük işler, firma bakiyeleri ve beyanname takibi.';
@endphp

@section('content')
<div class="container py-4">
    {{-- Sayfa Başlığı --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-semibold mb-1">Genel Bakış</h3>
            <p class="text-muted mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge bg-light text-dark border">
                <i class="bi bi-clock me-1"></i>{{ now()->format('H:i') }}
            </span>
        </div>
    </div>

    {{-- BÖLÜM 1: Acil Uyarılar Bandı --}}
    @include('dashboard._alert_band')

    {{-- BÖLÜM 2: Hızlı İşlemler + Aylık Fatura Üret --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            @include('dashboard._quick_actions')
        </div>
        <div class="col-lg-4">
            @include('dashboard._monthly_invoice_mini')
        </div>
    </div>

    {{-- BÖLÜM 3: Ana İçerik - 2 Kolon Layout --}}
    <div class="row g-4">
        {{-- Sol Kolon: Metrikler + Grafikler --}}
        <div class="col-lg-8">
            {{-- Özet Metrik Kartları --}}
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 metric-card metric-card-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-icon">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                                <div>
                                    <div class="metric-label">Toplam Alacak</div>
                                    <div class="metric-value">{{ Format::money($metrics['total_receivable']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 metric-card metric-card-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-icon">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                                <div>
                                    <div class="metric-label">Bu Ayki Tahsilat</div>
                                    <div class="metric-value">{{ Format::money($metrics['monthly_collection']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 metric-card metric-card-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div>
                                    <div class="metric-label">Geciken Müşteri</div>
                                    <div class="metric-value">{{ $metrics['overdue_firm_count'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 metric-card metric-card-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-icon">
                                    <i class="bi bi-file-earmark-medical"></i>
                                </div>
                                <div>
                                    <div class="metric-label">Bekleyen Beyanname</div>
                                    <div class="metric-value">{{ $metrics['tax_summary']['pending'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Haftalık Takvim --}}
            <div class="mb-4">
                @include('dashboard._weekly_calendar')
            </div>

            {{-- Grafikler --}}
            <div class="row g-3 mb-4">
                @include('dashboard._charts')
            </div>

            {{-- Son İşlemler --}}
            <div class="row g-3">
                @include('dashboard._recent_activity')
            </div>
        </div>

        {{-- Sağ Kolon: Yan Bilgiler --}}
        <div class="col-lg-4">
            {{-- Dikkat Gerektiren Firmalar --}}
            <div class="mb-4">
                @include('dashboard._attention_firms')
            </div>

            {{-- GİB Takvimi --}}
            <div class="mb-4">
                @include('dashboard._gib_calendar')
            </div>

            {{-- Gelir Tahmini --}}
            <div class="mb-4">
                @include('dashboard._forecast')
            </div>

            {{-- Beyanname Durumu (Kompakt) --}}
            @include('dashboard._upcoming_declarations')
        </div>
    </div>
</div>

<style>
/* Metrik Kartları */
.metric-card {
    transition: transform 0.2s, box-shadow 0.2s;
    overflow: hidden;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.metric-card .metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.metric-card .metric-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    font-weight: 500;
}

.metric-card .metric-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #212529;
}

/* Renk Varyasyonları */
.metric-card-primary .metric-icon {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.metric-card-success .metric-icon {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.metric-card-danger .metric-icon {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.metric-card-warning .metric-icon {
    background-color: rgba(255, 193, 7, 0.15);
    color: #cc9a06;
}

/* Responsive Düzenlemeler */
@media (max-width: 992px) {
    .metric-card .metric-value {
        font-size: 1.1rem;
    }
    
    .metric-card .metric-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .metric-card .card-body {
        padding: 0.75rem;
    }
    
    .metric-card .metric-value {
        font-size: 1rem;
    }
}
</style>
@endsection
