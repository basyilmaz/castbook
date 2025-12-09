{{-- Vergi Beyannameleri Kartı - Sadece Bu Ay --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0"><i class="bi bi-file-earmark-text text-primary me-1"></i>Beyannameler</h6>
        <span class="badge bg-light text-dark">{{ now()->translatedFormat('F Y') }}</span>
    </div>
    <div class="card-body p-0">
        @php
            $currentMonth = now();
            $monthDeclarations = $firm->taxDeclarations()
                ->with('taxForm')
                ->whereMonth('due_date', $currentMonth->month)
                ->whereYear('due_date', $currentMonth->year)
                ->orderBy('due_date')
                ->get();
        @endphp

        @if($monthDeclarations->isEmpty())
            <p class="text-muted text-center py-3 mb-0">Bu ay için beyanname bulunmuyor.</p>
        @else
            <div class="list-group list-group-flush">
                @foreach($monthDeclarations as $declaration)
                    @php
                        $isSubmitted = $declaration->status === 'submitted';
                        $dueDate = $declaration->due_date;
                        $isOverdue = !$isSubmitted && $dueDate && $dueDate->isPast();
                    @endphp
                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input declaration-quick-toggle" 
                                       data-id="{{ $declaration->id }}"
                                       {{ $isSubmitted ? 'checked' : '' }}
                                       style="width: 1.2em; height: 1.2em; cursor: pointer;"
                                       title="{{ $isSubmitted ? 'Verildi' : 'Bekliyor' }}">
                            </div>
                            <div>
                                <strong class="small">{{ $declaration->taxForm?->code ?? '—' }}</strong>
                                <span class="text-muted small">{{ $declaration->period_label }}</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <small class="{{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ $dueDate?->format('d.m') ?? '-' }}
                                @if($isOverdue)
                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                @endif
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    @if($monthDeclarations->count() > 0)
    <div class="card-footer bg-white text-center py-2">
        <a href="{{ route('firms.declarations', $firm) }}" class="text-decoration-none small">
            Tüm Beyannameleri Gör →
        </a>
    </div>
    @endif
</div>

{{-- AJAX Script --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.declaration-quick-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const id = this.dataset.id;
            const newStatus = this.checked ? 'submitted' : 'pending';
            const checkbox = this;
            
            checkbox.disabled = true;
            
            axios.patch(`/tax-declarations/${id}/status`, {
                status: newStatus
            }).then(response => {
                checkbox.disabled = false;
                checkbox.title = newStatus === 'submitted' ? 'Verildi' : 'Bekliyor';
                
                // Toast
                const toast = document.createElement('div');
                toast.className = 'alert alert-success position-fixed bottom-0 end-0 m-3 shadow';
                toast.style.zIndex = '9999';
                toast.innerHTML = response.data.message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            }).catch(error => {
                checkbox.disabled = false;
                checkbox.checked = !checkbox.checked;
                alert('İşlem başarısız oldu');
                console.error(error);
            });
        });
    });
});
</script>
@endpush
