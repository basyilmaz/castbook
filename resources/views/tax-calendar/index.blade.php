@extends('layouts.app')

@php
    $monthNames = [
        1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
        5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
        9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
    ];
@endphp

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h4 class="fw-semibold mb-0">
                <i class="bi bi-calendar-event text-primary me-2"></i>
                GİB Resmi Vergi Takvimi
            </h4>
            <small class="text-muted">Gelir İdaresi Başkanlığı resmi beyanname son tarihleri</small>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <a href="https://gib.gov.tr/vergi-takvimi" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i>GİB Resmi Site
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="bi bi-plus-circle me-1"></i>Yeni Yıl Oluştur
            </button>
        </div>
    </div>

    {{-- Eksik Yıl Uyarısı --}}
    @if(!empty($missingYears))
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div class="flex-grow-1">
                <strong>Eksik Yıllar Tespit Edildi!</strong>
                <p class="mb-2">Aşağıdaki yıllar için vergi takvimi verisi bulunmuyor:</p>
                @foreach($missingYears as $missingYear)
                    <form action="{{ route('tax-calendar.generate') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="year" value="{{ $missingYear }}">
                        <button type="submit" class="btn btn-warning btn-sm me-2 mb-1">
                            <i class="bi bi-plus-circle me-1"></i>{{ $missingYear }} Yılını Oluştur
                        </button>
                    </form>
                @endforeach
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif

    {{-- İstatistikler --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase">{{ $year }} Toplam</div>
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-calendar3 fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-uppercase opacity-75">Önümüzdeki 7 Gün</div>
                            <h3 class="mb-0 fw-bold">{{ $stats['upcoming_7'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-clock-history fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 {{ ($stats['today'] ?? 0) > 0 ? 'bg-danger' : 'bg-success' }} text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase">Bugün Son Gün</div>
                            <h3 class="mb-0 fw-bold">{{ $stats['today'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-{{ ($stats['today'] ?? 0) > 0 ? 'exclamation-triangle' : 'check-circle' }} fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('tax-calendar.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-muted text-uppercase small">Yıl</label>
                    <select name="year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted text-uppercase small">Ay</label>
                    <select name="month" class="form-select">
                        <option value="">Tümü</option>
                        @foreach($monthNames as $m => $name)
                            <option value="{{ $m }}" @selected($m == $month)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-funnel-fill me-1"></i>Filtrele
                    </button>
                    <a href="{{ route('tax-calendar.index') }}" class="btn btn-outline-secondary">Temizle</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Aya Göre Gruplu Liste --}}
    @forelse($groupedByMonth as $monthNum => $monthItems)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-gradient text-white py-3" style="background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-calendar-month me-2"></i>
                    {{ $monthNames[$monthNum] }} {{ $year }}
                    <span class="badge bg-white text-primary ms-2">{{ $monthItems->count() }} beyanname</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="100">Tarih</th>
                                <th width="120">Kod</th>
                                <th>Beyanname Adı</th>
                                <th width="150">Dönem</th>
                                <th width="120" class="text-center">Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthItems as $item)
                                @php
                                    $days = $item->daysUntilDue();
                                    $isToday = $days === 0;
                                    $isOverdue = $days < 0;
                                    $isFuture = $days > 0;
                                @endphp
                                <tr class="{{ $isToday ? 'table-danger' : '' }}">
                                    <td>
                                        <span class="fw-semibold {{ $isToday ? 'text-danger' : ($isOverdue ? 'text-muted' : '') }}">
                                            {{ $item->due_date->format('d.m.Y') }}
                                        </span>
                                        @if($isToday)
                                            <span class="badge bg-danger pulse-badge d-block mt-1">BUGÜN!</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <i class="{{ $item->icon }} me-1"></i>
                                            {{ $item->code }}
                                        </span>
                                    </td>
                                    <td>{{ $item->name }}</td>
                                    <td>
                                        <small class="text-muted">{{ $item->period_label }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($isOverdue)
                                            <span class="badge bg-secondary">Geçti</span>
                                        @elseif($isToday)
                                            <span class="badge bg-danger">Bugün</span>
                                        @elseif($days <= 3)
                                            <span class="badge bg-warning text-dark">{{ $days }} gün</span>
                                        @elseif($days <= 7)
                                            <span class="badge bg-info">{{ $days }} gün</span>
                                        @else
                                            <span class="badge bg-light text-muted">{{ $days }} gün</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x fs-1 text-muted mb-3 d-block"></i>
                <p class="text-muted mb-0">Seçilen dönem için vergi takvimi verisi bulunamadı.</p>
            </div>
        </div>
    @endforelse

    {{-- Renk Açıklaması --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap gap-4 justify-content-center align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger">●</span>
                    <small class="text-muted">Bugün son gün</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning">●</span>
                    <small class="text-muted">3 gün içinde</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-info">●</span>
                    <small class="text-muted">7 gün içinde</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary">●</span>
                    <small class="text-muted">Tarihi geçti</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Yeni Yıl Oluşturma Modal --}}
<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="generateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="generateModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Yeni Yıl için Vergi Takvimi Oluştur
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tax-calendar.generate') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-4">
                        Seçtiğiniz yıl için tüm vergi beyanname tarihlerini otomatik olarak oluşturur.
                        Sistem, KDV, Muhtasar, Geçici Vergi, Ba-Bs ve diğer beyanname tarihlerini 
                        algoritmik olarak hesaplar.
                    </p>
                    
                    <div class="mb-3">
                        <label for="yearSelect" class="form-label fw-semibold">Yıl Seçin</label>
                        <select name="year" id="yearSelect" class="form-select form-select-lg">
                            @for($y = now()->year; $y <= now()->year + 3; $y++)
                                <option value="{{ $y }}" @selected($y == now()->year + 1)>
                                    {{ $y }}
                                    @if(!in_array($y, $years->toArray()))
                                        (Yeni)
                                    @elseif(in_array($y, $missingYears ?? []))
                                        (Eksik)
                                    @endif
                                </option>
                            @endfor
                        </select>
                        <small class="form-text text-muted">
                            Mevcut veriler korunur, sadece eksik girişler eklenir.
                        </small>
                    </div>

                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>İpucu:</strong> Hafta sonuna denk gelen tarihler otomatik olarak Cuma'ya çekilir.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Oluştur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .pulse-badge {
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endpush
@endsection
