{{-- GİB Resmi Vergi Takvimi Widget --}}
<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-gradient text-white py-3" style="background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-calendar-event me-1"></i>
                GİB Vergi Takvimi
            </h6>
            <a href="https://gib.gov.tr/vergi-takvimi" target="_blank" class="text-white-50 small" title="Resmi Kaynak">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        @if($gibCalendar->isEmpty())
            <p class="text-muted text-center py-4 mb-0">
                <i class="bi bi-calendar-check fs-3 d-block mb-2 text-success"></i>
                Yaklaşan resmi tarih yok
            </p>
        @else
            <div class="list-group list-group-flush">
                @foreach($gibCalendar as $item)
                    @php
                        $days = $item->daysUntilDue();
                        $isToday = $days === 0;
                        $isOverdue = $days < 0;
                    @endphp
                    <div class="list-group-item py-2 {{ $isToday ? 'bg-danger bg-opacity-10 border-start border-danger border-3' : '' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-{{ $item->badge_class }} {{ $item->badge_class === 'warning' ? 'text-dark' : '' }}">
                                        {{ $item->code }}
                                    </span>
                                    @if($isToday)
                                        <span class="badge bg-danger">BUGÜN!</span>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-1">
                                    {{ $item->due_date->format('d.m.Y') }}
                                    @if($isToday)
                                        <span class="text-danger">(Bugün)</span>
                                    @elseif($isOverdue)
                                        <span class="text-danger">({{ abs($days) }}g geçti)</span>
                                    @elseif($days === 1)
                                        <span class="text-warning">(Yarın)</span>
                                    @else
                                        <span class="text-muted">({{ $days }}g)</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    
    <div class="card-footer bg-white border-top text-center py-2">
        <a href="{{ route('tax-calendar.index') }}" class="btn btn-sm btn-link text-decoration-none">
            Tüm Takvim <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>
