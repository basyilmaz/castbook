{{-- Haftalık Takvim Widget --}}
@php
    use App\Models\TaxDeclaration;
    use App\Models\Invoice;
    use Illuminate\Support\Carbon;
    
    $now = Carbon::now();
    $weekDays = [];
    
    // 7 gün için veri hazırla (bugün dahil)
    for ($i = 0; $i < 7; $i++) {
        $date = $now->copy()->addDays($i);
        $dateStr = $date->toDateString();
        
        // O gün son gün olan beyannameler
        $declarations = TaxDeclaration::query()
            ->with('taxForm:id,code')
            ->whereIn('status', ['pending', 'filed'])
            ->whereDate('due_date', $dateStr)
            ->limit(3)
            ->get();
        
        // O gün vadesi gelen faturalar
        $invoiceCount = Invoice::query()
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereDate('due_date', $dateStr)
            ->count();
        
        $weekDays[] = [
            'date' => $date,
            'dateStr' => $dateStr,
            'dayName' => $date->translatedFormat('D'),
            'dayNumber' => $date->format('d'),
            'monthName' => $date->translatedFormat('M'),
            'isToday' => $i === 0,
            'isWeekend' => $date->isWeekend(),
            'declarations' => $declarations,
            'declarationCount' => $declarations->count(),
            'invoiceCount' => $invoiceCount,
            'hasItems' => $declarations->count() > 0 || $invoiceCount > 0,
        ];
    }
@endphp

<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-calendar-week me-2 text-primary"></i>Haftalık Takvim
        </h6>
        <small class="text-muted">{{ $now->translatedFormat('d M') }} - {{ $now->copy()->addDays(6)->translatedFormat('d M') }}</small>
    </div>
    <div class="card-body p-0">
        <div class="weekly-calendar">
            @foreach($weekDays as $day)
            <div class="weekly-day {{ $day['isToday'] ? 'weekly-day-today' : '' }} {{ $day['isWeekend'] ? 'weekly-day-weekend' : '' }} {{ $day['hasItems'] ? 'weekly-day-has-items' : '' }}">
                <div class="weekly-day-header">
                    <span class="weekly-day-name">{{ $day['dayName'] }}</span>
                    <span class="weekly-day-number {{ $day['isToday'] ? 'bg-primary text-white' : '' }}">
                        {{ $day['dayNumber'] }}
                    </span>
                </div>
                <div class="weekly-day-content">
                    @if($day['hasItems'])
                        {{-- Beyannameler --}}
                        @foreach($day['declarations'] as $decl)
                        <div class="weekly-item weekly-item-declaration" title="{{ $decl->taxForm->code ?? '' }}">
                            <i class="bi bi-file-text"></i>
                            <span>{{ $decl->taxForm->code ?? 'Beyanname' }}</span>
                        </div>
                        @endforeach
                        
                        {{-- Faturalar --}}
                        @if($day['invoiceCount'] > 0)
                        <div class="weekly-item weekly-item-invoice" title="{{ $day['invoiceCount'] }} fatura vadesi">
                            <i class="bi bi-receipt"></i>
                            <span>{{ $day['invoiceCount'] }} fatura</span>
                        </div>
                        @endif
                    @else
                        <div class="weekly-item weekly-item-empty">
                            <span class="text-muted">—</span>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="card-footer bg-white border-top text-center py-2">
        <a href="{{ route('tax-declarations.index') }}" class="btn btn-sm btn-link text-decoration-none">
            Tüm Beyannameleri Gör <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

<style>
.weekly-calendar {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background-color: #e9ecef;
}

.weekly-day {
    background-color: #fff;
    min-height: 100px;
    display: flex;
    flex-direction: column;
}

.weekly-day-today {
    background-color: #e8f4fd;
}

.weekly-day-weekend {
    background-color: #f8f9fa;
}

.weekly-day-has-items {
    border-top: 2px solid #0d6efd;
}

.weekly-day-header {
    padding: 0.5rem;
    text-align: center;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.weekly-day-name {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #6c757d;
    font-weight: 600;
}

.weekly-day-number {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
}

.weekly-day-content {
    padding: 0.25rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.weekly-item {
    font-size: 0.65rem;
    padding: 0.15rem 0.25rem;
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.weekly-item i {
    font-size: 0.6rem;
}

.weekly-item-declaration {
    background-color: #cfe2ff;
    color: #084298;
}

.weekly-item-invoice {
    background-color: #fff3cd;
    color: #664d03;
}

.weekly-item-empty {
    justify-content: center;
    color: #adb5bd;
}

@media (max-width: 992px) {
    .weekly-calendar {
        grid-template-columns: repeat(4, 1fr);
    }
    .weekly-day:nth-child(n+5) {
        display: none;
    }
}

@media (max-width: 576px) {
    .weekly-calendar {
        grid-template-columns: repeat(3, 1fr);
    }
    .weekly-day:nth-child(n+4) {
        display: none;
    }
    .weekly-day {
        min-height: 80px;
    }
}
</style>
