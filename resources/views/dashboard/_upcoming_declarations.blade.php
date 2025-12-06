{{-- Yaklaşan Beyannameler Widget - Kompakt --}}
<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-file-earmark-medical me-1 text-primary"></i>
            Yaklaşan Beyannameler
        </h6>
        <span class="badge bg-primary">7 Gün</span>
    </div>
    <div class="card-body p-0">
        {{-- Bugün Son Günü Olan Beyannameler --}}
        @php
            $todayDeclarations = $upcomingDeclarations->filter(function($d) {
                return $d->due_date && $d->due_date->isToday();
            });
            $otherDeclarations = $upcomingDeclarations->filter(function($d) {
                return !$d->due_date || !$d->due_date->isToday();
            });
        @endphp
        
        @if($todayDeclarations->isNotEmpty())
            <div class="bg-danger text-white p-2 border-bottom">
                <div class="d-flex align-items-center gap-2 small">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>BUGÜN ({{ $todayDeclarations->count() }})</strong>
                </div>
            </div>
        @endif

        @if($otherDeclarations->isEmpty() && $todayDeclarations->isEmpty())
            <p class="text-muted text-center py-4 mb-0">
                <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                Yaklaşan beyanname yok
            </p>
        @else
            <div class="list-group list-group-flush">
                @foreach($otherDeclarations->take(4) as $declaration)
                    @php
                        $daysUntilDue = (int) now()->diffInDays($declaration->due_date, false);
                        $isOverdue = $daysUntilDue < 0;
                        $isUrgent = $daysUntilDue >= 0 && $daysUntilDue <= 3;
                        $badgeColor = $isOverdue ? 'danger' : ($isUrgent ? 'warning' : 'info');
                    @endphp
                    <a href="{{ route('tax-declarations.edit', $declaration) }}" 
                       class="list-group-item list-group-item-action py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-{{ $declaration->status === 'filed' ? 'primary' : ($declaration->status === 'pending' ? 'warning text-dark' : 'secondary') }} me-1">
                                    {{ $declaration->taxForm->code }}
                                </span>
                                <small class="text-muted">{{ Str::limit($declaration->firm->name, 15) }}</small>
                            </div>
                            <span class="badge bg-{{ $badgeColor }} {{ $badgeColor === 'warning' ? 'text-dark' : '' }}">
                                @if($isOverdue)
                                    {{ abs($daysUntilDue) }}g geç
                                @elseif($daysUntilDue == 1)
                                    Yarın
                                @else
                                    {{ $daysUntilDue }}g
                                @endif
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
    
    <div class="card-footer bg-white border-top py-2">
        <div class="d-flex gap-2">
            <a href="{{ route('tax-declarations.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning flex-grow-1">
                Bekleyenler
            </a>
            <a href="{{ route('tax-declarations.index') }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                Tümü
            </a>
        </div>
    </div>
</div>
