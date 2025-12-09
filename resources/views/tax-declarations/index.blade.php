@extends('layouts.app')

@php
    use Illuminate\Support\Carbon;
@endphp

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h4 class="fw-semibold mb-0">Beyanname Takibi</h4>
            <small class="text-muted">Firmalarınızın beyanname durumlarını takip edin.</small>
        </div>
        <div class="btn-group mt-2 mt-md-0" role="group" aria-label="Görünüm">
            <button type="button" class="btn btn-outline-primary active" id="listViewBtn">
                <i class="bi bi-list-ul me-1"></i>Liste
            </button>
            <button type="button" class="btn btn-outline-primary" id="calendarViewBtn">
                <i class="bi bi-calendar3 me-1"></i>Takvim
            </button>
        </div>
    </div>

    {{-- İstatistik Kartları --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase">Toplam</div>
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-file-earmark-text fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase">Bekleyen</div>
                            <h3 class="mb-0 fw-bold">{{ $stats['pending'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase">Gecikmiş</div>
                            <h3 class="mb-0 fw-bold">{{ $stats['overdue'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small text-uppercase">Bu Hafta</div>
                            <h3 class="mb-0 fw-bold">{{ $stats['this_week'] ?? 0 }}</h3>
                        </div>
                        <i class="bi bi-calendar-week fs-1 opacity-50"></i>
                    </div>
                    @if(($stats['today'] ?? 0) > 0)
                    <div class="mt-2">
                        <span class="badge bg-white text-danger fw-bold">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            Bugün {{ $stats['today'] }} beyanname!
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('tax-declarations.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-muted text-uppercase small">Firma</label>
                    <select name="firm_id" class="form-select">
                        <option value="">Tümü</option>
                        @foreach ($firms as $firm)
                            <option value="{{ $firm->id }}" @selected(($filters['firm_id'] ?? null) == $firm->id)>
                                {{ $firm->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted text-uppercase small">Beyanname</label>
                    <select name="tax_form_id" class="form-select">
                        <option value="">Tümü</option>
                        @foreach ($forms as $form)
                            <option value="{{ $form->id }}" @selected(($filters['tax_form_id'] ?? null) == $form->id)>
                                {{ $form->code }} - {{ $form->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted text-uppercase small">Yıl</label>
                    <input type="number" name="year" class="form-control" value="{{ $filters['year'] ?? '' }}" placeholder="{{ now()->year }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted text-uppercase small">Ay</label>
                    <select name="month" class="form-select">
                        <option value="">Tümü</option>
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected(($filters['month'] ?? null) == $m)>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted text-uppercase small">Durum</label>
                    <select name="status" class="form-select">
                        <option value="">Tümü</option>
                        <option value="pending" @selected(($filters['status'] ?? null) === 'pending')>Bekliyor</option>
                        <option value="submitted" @selected(($filters['status'] ?? null) === 'submitted')>Verildi</option>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-funnel-fill me-1"></i> Filtrele
                    </button>
                    <a href="{{ route('tax-declarations.index', ['status' => '']) }}" class="btn btn-outline-secondary">Tümünü Göster</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Takvim Görünümü --}}
    <div id="calendarView" class="d-none mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <button class="btn btn-outline-primary btn-sm" id="prevMonth">
                    <i class="bi bi-chevron-left"></i> Önceki
                </button>
                <h5 class="mb-0 fw-semibold" id="calendarTitle">{{ now()->translatedFormat('F Y') }}</h5>
                <button class="btn btn-outline-primary btn-sm" id="nextMonth">
                    Sonraki <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="calendar-grid">
                    <div class="row text-center fw-bold border-bottom pb-2 mb-2">
                        <div class="col">Pzt</div>
                        <div class="col">Sal</div>
                        <div class="col">Çar</div>
                        <div class="col">Per</div>
                        <div class="col">Cum</div>
                        <div class="col text-muted">Cmt</div>
                        <div class="col text-muted">Paz</div>
                    </div>
                    <div id="calendarDays" class="calendar-days">
                        {{-- JavaScript ile doldurulacak --}}
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <small><span class="badge bg-warning">●</span> Bekliyor</small>
                    <small><span class="badge bg-primary">●</span> Dosyalandı</small>
                    <small><span class="badge bg-success">●</span> Ödendi</small>
                    <small><span class="badge bg-danger">●</span> Gecikmiş</small>
                    <small><span class="badge bg-secondary">●</span> Gerekli Değil</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Toplu İşlem Araç Çubuğu --}}
    <div id="bulkActionBar" class="card border-0 shadow-sm mb-3 d-none" style="position: sticky; top: 60px; z-index: 100;">
        <div class="card-body py-2 bg-primary text-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <i class="bi bi-check2-square me-1"></i>
                    <strong id="selectedCount">0</strong> beyanname seçildi
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkUpdateStatus('submitted')">
                        <i class="bi bi-check-circle me-1"></i>Verildi
                    </button>
                    <button type="button" class="btn btn-warning btn-sm text-dark" onclick="bulkUpdateStatus('pending')">
                        <i class="bi bi-hourglass-split me-1"></i>Bekliyor
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm" onclick="clearSelection()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Liste Görünümü (Tablo) --}}
    <div id="listView" class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox" title="Tümünü Seç">
                            </th>
                            <th>Firma</th>
                            <th>Beyanname</th>
                            <th>Dönem</th>
                            <th>Son Gün</th>
                            <th>Durum</th>
                            <th width="200">Hızlı İşlem</th>
                            <th class="text-center">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($declarations as $declaration)
                            @php
                                $daysUntilDue = (int) now()->diffInDays($declaration->due_date, false);
                                $isOverdue = $daysUntilDue < 0;
                                $isUrgent = $daysUntilDue >= 0 && $daysUntilDue <= 3;
                                $isToday = $daysUntilDue == 0;
                                $officialDate = $declaration->official_due_date;
                                $matchesOfficial = $declaration->matches_official_date;
                            @endphp
                            <tr class="tax-declaration-row" data-id="{{ $declaration->id }}">
                                <td>
                                    <input type="checkbox" class="form-check-input declaration-checkbox" 
                                           value="{{ $declaration->id }}" data-id="{{ $declaration->id }}">
                                </td>
                                <td>{{ $declaration->firm?->name ?? 'Silinmiş Firma' }}</td>
                                <td>
                                    <strong>{{ $declaration->taxForm?->code ?? '—' }}</strong>
                                    <small class="text-muted d-block">{{ $declaration->taxForm?->name ?? 'Silinmiş Form' }}</small>
                                </td>
                                <td>{{ $declaration->period_label }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="{{ $isOverdue ? 'text-danger fw-semibold' : ($isUrgent ? 'text-warning fw-semibold' : '') }}">
                                            {{ $declaration->due_date->format('d.m.Y') }}
                                        </span>
                                        @if($officialDate)
                                            @if($matchesOfficial)
                                                <i class="bi bi-check-circle-fill text-success" 
                                                   title="GİB resmi tarihiyle eşleşiyor"></i>
                                            @else
                                                <i class="bi bi-exclamation-circle-fill text-warning" 
                                                   title="GİB resmi tarihi: {{ $officialDate->format('d.m.Y') }}"></i>
                                            @endif
                                        @endif
                                    </div>
                                    @if($declaration->status === 'pending')
                                        @if($isToday)
                                            <span class="badge bg-danger ms-1 pulse-badge">BUGÜN!</span>
                                        @elseif($isOverdue)
                                            <small class="d-block text-danger">{{ abs($daysUntilDue) }} gün gecikmiş</small>
                                        @else
                                            <small class="d-block {{ $isUrgent ? 'text-warning' : 'text-muted' }}">{{ $daysUntilDue }} gün kaldı</small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $declaration->status === 'submitted' ? 'success' : 'warning text-dark' }} status-badge">
                                        {{ $declaration->status === 'submitted' ? 'Verildi' : 'Bekliyor' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input tax-status-quick-toggle" 
                                               data-declaration-id="{{ $declaration->id }}"
                                               {{ $declaration->status === 'submitted' ? 'checked' : '' }}
                                               style="width: 1.3em; height: 1.3em; cursor: pointer;"
                                               title="{{ $declaration->status === 'submitted' ? 'Verildi' : 'Bekliyor' }}">
                                        <label class="form-check-label small">{{ $declaration->status === 'submitted' ? 'Verildi' : 'Bekliyor' }}</label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('tax-declarations.edit', $declaration) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Beyanname bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($declarations->hasPages())
            <div class="card-footer bg-white">
                {{ $declarations->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Beyanname Detay Modal --}}
<div class="modal fade" id="declarationDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Beyanname Detayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="declarationDetailContent">
                <!-- AJAX ile doldurulacak -->
            </div>
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
    .calendar-days .row {
        min-height: 90px;
    }
    .calendar-days .col {
        border: 1px solid #eee;
        padding: 5px;
        min-height: 90px;
    }
    .calendar-days .col:hover {
        background: #f8f9fa;
    }
    .declaration-pill {
        font-size: 0.7rem;
        padding: 2px 6px;
        margin-bottom: 2px;
        cursor: pointer;
        transition: transform 0.1s;
    }
    .declaration-pill:hover {
        transform: scale(1.05);
    }
    .table-row-selected {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
</style>
@endpush

{{-- AJAX Script --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Seçili beyanname ID'leri
    let selectedIds = [];
    
    // Checkbox değişikliği
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('declaration-checkbox')) {
            const checkbox = e.target;
            const id = parseInt(checkbox.dataset.id);
            const row = checkbox.closest('tr');
            
            if (checkbox.checked) {
                if (!selectedIds.includes(id)) {
                    selectedIds.push(id);
                }
                row.classList.add('table-row-selected');
            } else {
                selectedIds = selectedIds.filter(i => i !== id);
                row.classList.remove('table-row-selected');
            }
            
            updateBulkActionBar();
        }
    });
    
    // Tümünü seç
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.declaration-checkbox').forEach(cb => {
                cb.checked = isChecked;
                cb.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }
    
    // Toplu işlem çubuğunu güncelle
    function updateBulkActionBar() {
        const bar = document.getElementById('bulkActionBar');
        const count = selectedIds.length;
        
        if (count > 0) {
            bar.classList.remove('d-none');
            document.getElementById('selectedCount').textContent = count;
        } else {
            bar.classList.add('d-none');
        }
    }
    
    // Seçimi temizle
    window.clearSelection = function() {
        selectedIds = [];
        document.querySelectorAll('.declaration-checkbox').forEach(cb => cb.checked = false);
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        document.querySelectorAll('.tax-declaration-row').forEach(row => row.classList.remove('table-row-selected'));
        updateBulkActionBar();
    }
    
    // Toplu durum güncelleme
    window.bulkUpdateStatus = function(status) {
        if (selectedIds.length === 0) return;
        
        const statusLabels = {
            'pending': 'Bekliyor',
            'filed': 'Dosyalandı',
            'paid': 'Ödendi',
            'not_required': 'Gerekli Değil'
        };
        
        if (!confirm(`${selectedIds.length} beyanname "${statusLabels[status]}" olarak işaretlenecek. Devam etmek istiyor musunuz?`)) {
            return;
        }
        
        axios.patch('{{ route("tax-declarations.bulk-status") }}', {
            ids: selectedIds,
            status: status
        }).then(response => {
            if (response.data.success) {
                window.location.reload();
            }
        }).catch(error => {
            alert(error.response?.data?.message || 'Bir hata oluştu');
        });
    }
    
    // Tek beyanname hızlı durum toggle (checkbox)
    document.querySelectorAll('.tax-status-quick-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const declarationId = this.dataset.declarationId;
            const newStatus = this.checked ? 'submitted' : 'pending';
            const row = this.closest('.tax-declaration-row');
            const checkbox = this;
            const label = row.querySelector('.form-check-label');
            
            checkbox.disabled = true;
            
            axios.patch(`/tax-declarations/${declarationId}/status`, {
                status: newStatus
            }).then(response => {
                const badge = row.querySelector('.status-badge');
                if (badge) {
                    badge.className = `badge bg-${newStatus === 'submitted' ? 'success' : 'warning text-dark'} status-badge`;
                    badge.textContent = newStatus === 'submitted' ? 'Verildi' : 'Bekliyor';
                }
                if (label) {
                    label.textContent = newStatus === 'submitted' ? 'Verildi' : 'Bekliyor';
                }
                checkbox.title = newStatus === 'submitted' ? 'Verildi' : 'Bekliyor';
                
                // Toast
                const toast = document.createElement('div');
                toast.className = 'alert alert-success position-fixed bottom-0 end-0 m-3 shadow';
                toast.style.zIndex = '9999';
                toast.innerHTML = response.data.message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            }).catch(error => {
                checkbox.checked = !checkbox.checked; // Revert
                alert(error.response?.data?.message || 'Bir hata oluştu');
            }).finally(() => {
                checkbox.disabled = false;
            });
        });
    });
    
    // Takvim/Liste Görünüm Toggle
    const listViewBtn = document.getElementById('listViewBtn');
    const calendarViewBtn = document.getElementById('calendarViewBtn');
    const listView = document.getElementById('listView');
    const calendarView = document.getElementById('calendarView');
    
    // Takvim değişkenleri
    let currentCalendarDate = new Date();
    const monthNames = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
                        'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    
    if (listViewBtn) {
        listViewBtn.addEventListener('click', function() {
            listView.classList.remove('d-none');
            calendarView.classList.add('d-none');
            document.getElementById('bulkActionBar').classList.remove('d-none-forced');
            listViewBtn.classList.add('active');
            calendarViewBtn.classList.remove('active');
            localStorage.setItem('declarationView', 'list');
        });
    }
    
    if (calendarViewBtn) {
        calendarViewBtn.addEventListener('click', function() {
            listView.classList.add('d-none');
            calendarView.classList.remove('d-none');
            document.getElementById('bulkActionBar').classList.add('d-none');
            calendarViewBtn.classList.add('active');
            listViewBtn.classList.remove('active');
            localStorage.setItem('declarationView', 'calendar');
            renderCalendar();
        });
        
        if (localStorage.getItem('declarationView') === 'calendar') {
            calendarViewBtn.click();
        }
    }
    
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    
    if (prevMonthBtn) {
        prevMonthBtn.addEventListener('click', function() {
            currentCalendarDate.setMonth(currentCalendarDate.getMonth() - 1);
            renderCalendar();
        });
    }
    
    if (nextMonthBtn) {
        nextMonthBtn.addEventListener('click', function() {
            currentCalendarDate.setMonth(currentCalendarDate.getMonth() + 1);
            renderCalendar();
        });
    }
    
    function renderCalendar() {
        const year = currentCalendarDate.getFullYear();
        const month = currentCalendarDate.getMonth() + 1;
        
        document.getElementById('calendarTitle').textContent = `${monthNames[month - 1]} ${year}`;
        document.getElementById('calendarDays').innerHTML = '<div class="text-center py-5"><i class="bi bi-arrow-repeat spin"></i> Yükleniyor...</div>';
        
        // API'den verileri al (axios interceptor otomatik token ekler)
        axios.get('{{ route("tax-declarations.calendar") }}', {
            params: { year: year, month: month }
        }).then(response => {
            buildCalendarGrid(year, month, response.data.data);
        }).catch(error => {
            console.error('Calendar API Error:', error.response?.data || error.message);
            document.getElementById('calendarDays').innerHTML = '<div class="text-center text-danger py-5">Veriler yüklenemedi</div>';
        });
    }
    
    function buildCalendarGrid(year, month, data) {
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const startDay = (firstDay.getDay() + 6) % 7;
        
        let html = '';
        let dayCount = 1;
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        for (let week = 0; week < 6; week++) {
            html += '<div class="row g-0">';
            for (let day = 0; day < 7; day++) {
                const cellIndex = week * 7 + day;
                if (cellIndex < startDay || dayCount > lastDay.getDate()) {
                    html += '<div class="col py-2 bg-light" style="min-height: 90px;"></div>';
                } else {
                    const cellDate = new Date(year, month - 1, dayCount);
                    const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(dayCount).padStart(2, '0')}`;
                    const isToday = cellDate.getTime() === today.getTime();
                    const isPast = cellDate < today;
                    
                    const dayDeclarations = data[dateStr] || [];
                    
                    html += `<div class="col py-2 ${isToday ? 'bg-primary bg-opacity-10 border-primary' : ''}" style="min-height: 90px;">`;
                    html += `<div class="fw-bold mb-1 ${day >= 5 ? 'text-muted' : ''} ${isToday ? 'text-primary' : ''}">${dayCount}</div>`;
                    
                    dayDeclarations.forEach(d => {
                        let bgClass = 'bg-warning text-dark';
                        if (d.status === 'submitted') bgClass = 'bg-success';
                        else if (d.is_overdue) bgClass = 'bg-danger';
                        
                        html += `<div class="badge ${bgClass} w-100 text-truncate declaration-pill mb-1" 
                                     title="${d.firm_name} - ${d.tax_form_code} (${d.period_label})"
                                     data-id="${d.id}">
                                    ${d.tax_form_code}
                                </div>`;
                    });
                    
                    html += '</div>';
                    dayCount++;
                }
            }
            html += '</div>';
            if (dayCount > lastDay.getDate()) break;
        }
        
        document.getElementById('calendarDays').innerHTML = html;
        
        // Pill'lere tıklama olayı
        document.querySelectorAll('.declaration-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                const id = this.dataset.id;
                window.location.href = `/tax-declarations/${id}/edit`;
            });
        });
    }
});
</script>
<style>
.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}
</style>
@endpush
@endsection
