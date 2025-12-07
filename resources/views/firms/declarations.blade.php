@extends('layouts.app')

@php
    $statusLabels = [
        'pending' => ['label' => 'Bekliyor', 'class' => 'warning text-dark'],
        'filed' => ['label' => 'Dosyalandı', 'class' => 'primary'],
        'paid' => ['label' => 'Ödendi', 'class' => 'success'],
        'not_required' => ['label' => 'Gerekli Değil', 'class' => 'secondary'],
    ];
@endphp

@section('content')
<div class="container py-4">
    {{-- Breadcrumb ve Başlık --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('firms.index') }}">Firmalar</a></li>
            <li class="breadcrumb-item"><a href="{{ route('firms.show', $firm) }}">{{ $firm->name }}</a></li>
            <li class="breadcrumb-item active">Beyannameler</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h4 class="fw-semibold mb-0">
                <i class="bi bi-file-earmark-text text-primary me-2"></i>
                {{ $firm->name }} - Beyanname Özeti
            </h4>
            <small class="text-muted">{{ $year }} yılı beyanname durumu</small>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            <form action="{{ route('firms.declarations', $firm) }}" method="GET" class="d-flex gap-2">
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('firms.show', $firm) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Firmaya Dön
            </a>
        </div>
    </div>

    {{-- İstatistik Kartları --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100 text-center bg-primary text-white">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold">{{ $stats['total'] }}</div>
                    <small class="text-white-50">Toplam</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100 text-center bg-warning text-dark">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold">{{ $stats['pending'] }}</div>
                    <small class="opacity-75">Bekliyor</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100 text-center bg-info text-white">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold">{{ $stats['filed'] }}</div>
                    <small class="text-white-50">Dosyalandı</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100 text-center bg-success text-white">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold">{{ $stats['paid'] }}</div>
                    <small class="text-white-50">Ödendi</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100 text-center {{ $stats['overdue'] > 0 ? 'bg-danger' : 'bg-secondary' }} text-white">
                <div class="card-body py-3">
                    <div class="fs-3 fw-bold">{{ $stats['overdue'] }}</div>
                    <small class="text-white-50">Gecikmiş</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Eksik Dönemler Uyarısı --}}
    @if(count($expectedPeriods) > 0)
    <div class="alert alert-warning mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>Eksik Dönemler!</strong>
                <p class="mb-1">{{ $year }} yılı için {{ count($expectedPeriods) }} eksik beyanname dönemi var:</p>
                <div class="d-flex flex-wrap gap-1">
                    @foreach(collect($expectedPeriods)->take(10) as $missing)
                        <span class="badge bg-warning text-dark">{{ $missing['form'] }} - {{ $missing['period'] }}</span>
                    @endforeach
                    @if(count($expectedPeriods) > 10)
                        <span class="badge bg-secondary">+{{ count($expectedPeriods) - 10 }} daha</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Tanımlı Formlar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-list-check me-1"></i>
                Tanımlı Vergi Formları
            </h6>
        </div>
        <div class="card-body">
            @if($activeForms->isEmpty())
                <p class="text-muted mb-0">Bu firma için tanımlı vergi formu bulunmuyor.</p>
            @else
                <div class="d-flex flex-wrap gap-2">
                    @foreach($activeForms as $form)
                        <span class="badge bg-primary fs-6 py-2 px-3">
                            <i class="bi bi-file-earmark me-1"></i>
                            {{ $form->code }}
                            <small class="text-white-50 ms-1">({{ match($form->frequency) { 'monthly' => 'Aylık', 'quarterly' => 'Çeyrek', 'yearly' => 'Yıllık', default => $form->frequency } }})</small>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Beyanname Matrisi - Form bazlı --}}
    @if($groupedByForm->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-gradient text-white py-3" style="background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-grid-3x3-gap me-2"></i>
                {{ $year }} Yılı Beyanname Durumu
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="100">Dönem</th>
                            @foreach($groupedByForm->keys() as $formCode)
                                <th class="text-center">{{ $formCode }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @for($m = 1; $m <= 12; $m++)
                            @php $period = sprintf('%02d/%d', $m, $year); @endphp
                            <tr>
                                <td class="text-center fw-semibold bg-light">
                                    {{ \Carbon\Carbon::createFromDate($year, $m, 1)->translatedFormat('F') }}
                                </td>
                                @foreach($groupedByForm as $formCode => $formDeclarations)
                                    @php
                                        $decl = $formDeclarations->firstWhere('period_label', $period);
                                    @endphp
                                    <td class="text-center">
                                        @if($decl)
                                            @php 
                                                $s = $statusLabels[$decl->status] ?? $statusLabels['pending'];
                                                $isCompleted = in_array($decl->status, ['paid', 'filed', 'not_required']);
                                            @endphp
                                            @if($isCompleted)
                                                {{-- Verilmiş beyanname - Yeşil tik --}}
                                                <a href="{{ route('tax-declarations.edit', $decl) }}" 
                                                   class="text-success text-decoration-none"
                                                   title="{{ $s['label'] }} - {{ $decl->due_date?->format('d.m.Y') ?? '' }}">
                                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                                </a>
                                            @else
                                                {{-- Bekleyen beyanname - Badge --}}
                                                <a href="{{ route('tax-declarations.edit', $decl) }}" 
                                                   class="badge bg-{{ $s['class'] }} text-decoration-none"
                                                   title="{{ $decl->due_date?->format('d.m.Y') ?? 'Tarih Yok' }}">
                                                    {{ $s['label'] }}
                                                </a>
                                                @if($decl->isOverdue())
                                                    <i class="bi bi-exclamation-circle-fill text-danger ms-1" title="Gecikmiş!"></i>
                                                @endif
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-x fs-1 text-muted mb-3 d-block"></i>
            <p class="text-muted mb-2">{{ $year }} yılı için beyanname kaydı bulunamadı.</p>
            <a href="{{ route('tax-declarations.index') }}?firm_id={{ $firm->id }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Beyanname Ekle
            </a>
        </div>
    </div>
    @endif

    {{-- Renk Açıklaması --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap gap-4 justify-content-center align-items-center">
                @foreach($statusLabels as $key => $s)
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $s['class'] }}">●</span>
                    <small class="text-muted">{{ $s['label'] }}</small>
                </div>
                @endforeach
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                    <small class="text-muted">Gecikmiş</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
