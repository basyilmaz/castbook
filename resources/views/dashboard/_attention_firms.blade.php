{{-- Dikkat Gerektiren Firmalar Widget --}}
@php
    use App\Support\Format;
    use Illuminate\Support\Carbon;
    
    $now = Carbon::now();
    
    // Gecikmiş bakiyesi olan veya gecikmiş faturası olan firmalar
    $attentionFirms = collect($firms ?? [])
        ->filter(function ($firm) {
            return $firm['remaining'] > 0 && $firm['badge_class'] === 'danger';
        })
        ->sortByDesc('remaining')
        ->take(5)
        ->values();
    
    $totalAttentionCount = collect($firms ?? [])
        ->filter(function ($firm) {
            return $firm['remaining'] > 0 && in_array($firm['badge_class'], ['danger', 'warning text-dark']);
        })
        ->count();
@endphp

<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-exclamation-diamond me-2 text-danger"></i>Dikkat Gerektiren Firmalar
        </h6>
        @if($attentionFirms->count() > 0)
        <span class="badge bg-danger">{{ $totalAttentionCount }}</span>
        @endif
    </div>
    <div class="card-body p-0">
        @if($attentionFirms->isEmpty())
            <div class="text-center py-4">
                <i class="bi bi-emoji-smile fs-2 text-success d-block mb-2"></i>
                <p class="text-muted mb-0">Tüm firmalar düzenli!</p>
                <small class="text-muted">Gecikmiş ödeme bulunmuyor.</small>
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach($attentionFirms as $firm)
                <a href="{{ route('firms.show', $firm['id']) }}" 
                   class="list-group-item list-group-item-action py-3 attention-firm-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="attention-firm-avatar">
                                {{ mb_strtoupper(mb_substr($firm['name'], 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-medium text-truncate" style="max-width: 150px;">
                                    {{ $firm['name'] }}
                                </div>
                                <small class="text-danger">
                                    <i class="bi bi-clock-history me-1"></i>Gecikmiş
                                </small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-danger">
                                {{ Format::money($firm['remaining']) }}
                            </div>
                            <small class="text-muted">bakiye</small>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>
    @if($totalAttentionCount > 5)
    <div class="card-footer bg-white border-top text-center py-2">
        <a href="{{ route('firms.index', ['status' => 'overdue']) }}" class="btn btn-sm btn-outline-danger">
            Tümünü Gör ({{ $totalAttentionCount }}) <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    @elseif($attentionFirms->isNotEmpty())
    <div class="card-footer bg-white border-top text-center py-2">
        <a href="{{ route('firms.index') }}" class="btn btn-sm btn-link text-decoration-none">
            Tüm Firmalar <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    @endif
</div>

<style>
.attention-firm-item {
    transition: background-color 0.2s;
}

.attention-firm-item:hover {
    background-color: #fff5f5;
}

.attention-firm-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
}
</style>
