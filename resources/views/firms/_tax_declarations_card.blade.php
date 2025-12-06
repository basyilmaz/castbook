{{-- Vergi Beyannameleri Kartı --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Vergi Beyannameleri</h6>
        <small class="text-muted">Bu Ay</small>
    </div>
    <div class="card-body">
        @php
            $currentMonth = now()->startOfMonth();
            $recentDeclarations = $firm->taxDeclarations()
                ->with('taxForm')
                ->whereMonth('due_date', $currentMonth->month)
                ->whereYear('due_date', $currentMonth->year)
                ->orderBy('due_date')
                ->get();
        @endphp

        @if($recentDeclarations->isEmpty())
            <p class="text-muted text-center mb-0">Bu ay için beyanname bulunmuyor.</p>
        @else
            @foreach($recentDeclarations as $declaration)
                <div class="border-bottom pb-3 mb-3 tax-declaration-item" data-id="{{ $declaration->id }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong>{{ $declaration->taxForm->code }}</strong>
                            <small class="text-muted d-block">{{ $declaration->taxForm->name }}</small>
                        </div>
                        <span class="badge bg-{{ $declaration->status === 'paid' ? 'success' : ($declaration->status === 'filed' ? 'primary' : ($declaration->status === 'not_required' ? 'secondary' : 'warning text-dark')) }}">
                            {{ match($declaration->status) {
                                'pending' => 'Bekliyor',
                                'filed' => 'Dosyalandı',
                                'paid' => 'Ödendi',
                                'not_required' => 'Gerekli Değil',
                                default => $declaration->status
                            } }}
                        </span>
                    </div>
                    
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">Durum:</small>
                            <select class="form-select form-select-sm tax-status-select" 
                                    data-declaration-id="{{ $declaration->id }}"
                                    data-original-status="{{ $declaration->status }}">
                                <option value="pending" @selected($declaration->status === 'pending')>Bekliyor</option>
                                <option value="filed" @selected($declaration->status === 'filed')>Dosyalandı</option>
                                <option value="paid" @selected($declaration->status === 'paid')>Ödendi</option>
                                <option value="not_required" @selected($declaration->status === 'not_required')>Gerekli Değil</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            @php
                                $dueDate = $declaration->due_date;
                                $daysUntilDue = (int) now()->diffInDays($dueDate, false);
                                $isOverdue = $daysUntilDue < 0;
                                $isUrgent = $daysUntilDue >= 0 && $daysUntilDue <= 3;
                                $isWarning = $daysUntilDue > 3 && $daysUntilDue <= 7;
                            @endphp
                            <small class="text-muted d-block">Son Gün:</small>
                            <div class="d-flex align-items-center gap-2">
                                <span class="{{ $isOverdue ? 'text-danger' : ($isUrgent ? 'text-danger' : ($isWarning ? 'text-warning' : 'text-success')) }}">
                                    {{ $dueDate->format('d.m.Y') }}
                                </span>
                                @if($declaration->status !== 'paid' && $declaration->status !== 'not_required')
                                    <span class="badge bg-{{ $isOverdue ? 'danger' : ($isUrgent ? 'danger' : ($isWarning ? 'warning' : 'success')) }}">
                                        @if($isOverdue)
                                            {{ abs($daysUntilDue) }} gün gecikmiş
                                        @else
                                            {{ $daysUntilDue }} gün kaldı
                                        @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        <a href="{{ route('tax-declarations.index', ['firm_id' => $firm->id]) }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
            Tüm Beyannameleri Gör →
        </a>
    </div>
</div>

{{-- AJAX Script --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tax-status-select').forEach(selectEl => {
        selectEl.addEventListener('change', function() {
            const declarationId = this.dataset.declarationId;
            const newStatus = this.value;
            const originalStatus = this.dataset.originalStatus;
            const item = this.closest('.tax-declaration-item');
            
            // Onay iste
            if (!confirm('Beyanname durumunu değiştirmek istediğinize emin misiniz?')) {
                this.value = originalStatus;
                return;
            }
            
            // Loading state
            this.disabled = true;
            const self = this;
            
            axios.patch(`/tax-declarations/${declarationId}/status`, {
                status: newStatus
            }).then(response => {
                // Badge'i güncelle
                const badgeClass = newStatus === 'paid' ? 'success' : 
                                 (newStatus === 'filed' ? 'primary' : 
                                 (newStatus === 'not_required' ? 'secondary' : 'warning text-dark'));
                const badgeText = newStatus === 'paid' ? 'Ödendi' :
                                (newStatus === 'filed' ? 'Dosyalandı' :
                                (newStatus === 'not_required' ? 'Gerekli Değil' : 'Bekliyor'));
                
                const badge = item.querySelector('.badge');
                if (badge) {
                    badge.className = `badge bg-${badgeClass}`;
                    badge.textContent = badgeText;
                }
                self.dataset.originalStatus = newStatus;
                
                // Toast bildirimi
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.data.message || 'Durum güncellendi');
                } else {
                    alert(response.data.message || 'Durum güncellendi');
                }
            }).catch(error => {
                self.value = originalStatus;
                const message = error.response?.data?.message || 'Bir hata oluştu';
                
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            }).finally(() => {
                self.disabled = false;
            });
        });
    });
});
</script>
@endpush
