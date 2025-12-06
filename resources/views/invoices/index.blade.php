@extends('layouts.app')

@php
    use App\Support\Format;

    $statusOptions = [
        '' => 'Tümü',
        'unpaid' => 'Ödenmedi',
        'partial' => 'Kısmi ödeme',
        'paid' => 'Ödendi',
        'cancelled' => 'İptal',
    ];
@endphp

@section('content')
<div class="container py-4">
    {{-- Aylık Fatura Üret - Hero Bölümü --}}
    <div class="monthly-invoice-hero mb-4">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="monthly-invoice-gradient">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <div class="p-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="monthly-invoice-icon">
                                    <i class="bi bi-magic"></i>
                                </div>
                                <div>
                                    <h4 class="text-white mb-0 fw-bold">Aylık Fatura Üret</h4>
                                    <p class="text-white-50 mb-0 small">Tüm aktif firmalara tek tıkla aylık fatura oluşturun</p>
                                </div>
                            </div>
                            <form action="{{ route('invoices.sync-monthly') }}" method="POST" class="d-flex flex-wrap align-items-center gap-3">
                                @csrf
                                <div class="d-flex align-items-center gap-2">
                                    <label class="text-white-50 small text-nowrap">Dönem:</label>
                                    <input type="month" 
                                           name="month" 
                                           class="form-control form-control-lg bg-white border-0" 
                                           value="{{ now()->format('Y-m') }}"
                                           style="max-width: 180px;">
                                </div>
                                <button type="submit" class="btn btn-light btn-lg fw-semibold px-4 generate-btn">
                                    <i class="bi bi-lightning-charge-fill me-2 text-warning"></i>
                                    Faturaları Oluştur
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-5 d-none d-lg-block">
                        <div class="p-4">
                            @php
                                $activeFirmCount = \App\Models\Firm::where('status', 'active')->count();
                                $totalMonthlyFee = \App\Models\Firm::where('status', 'active')->sum('monthly_fee');
                                $thisMonthInvoiceCount = \App\Models\Invoice::whereMonth('date', now()->month)
                                    ->whereYear('date', now()->year)
                                    ->count();
                            @endphp
                            <div class="row text-white text-center g-3">
                                <div class="col-4">
                                    <div class="monthly-stat-box">
                                        <div class="monthly-stat-value">{{ $activeFirmCount }}</div>
                                        <div class="monthly-stat-label">Aktif Firma</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="monthly-stat-box">
                                        <div class="monthly-stat-value">{{ Format::money($totalMonthlyFee) }}</div>
                                        <div class="monthly-stat-label">Aylık Toplam</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="monthly-stat-box">
                                        <div class="monthly-stat-value">{{ $thisMonthInvoiceCount }}</div>
                                        <div class="monthly-stat-label">Bu Ay Fatura</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h4 class="fw-semibold mb-0">Faturalar</h4>
            <small class="text-muted">Faturalarınızı filtreleyip yönetin.</small>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2 mt-3 mt-md-0">
            <a href="{{ route('invoices.import.form') }}" class="btn btn-outline-success">
                <i class="bi bi-upload me-1"></i>CSV İçe Aktar
            </a>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Yeni Fatura Oluştur
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('invoices.index') }}" method="GET" class="row g-3">
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
                    <label class="form-label text-muted text-uppercase small">Durum</label>
                    <select name="status" class="form-select">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted text-uppercase small">Tarih Aralığı</label>
                    <div class="d-flex gap-2">
                        <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                        <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted text-uppercase small">Sayfa Boyutu</label>
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        @foreach ([10, 20, 50, 100] as $size)
                            <option value="{{ $size }}" @selected(($filters['per_page'] ?? $perPage) == $size)>{{ $size }} / sayfa</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-funnel-fill me-1"></i> Filtrele
                    </button>
                    <a href="{{ route('invoices.index', ['per_page' => $perPage]) }}" class="btn btn-outline-secondary">Temizle</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Toplu İşlem Toolbar --}}
    <div class="alert alert-info d-none mb-3" id="bulkActionBar">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <i class="bi bi-check-square me-2"></i>
                <span id="selectedCount">0</span> fatura seçildi
            </div>
            <div class="d-flex flex-wrap gap-2">
                {{-- Durum Güncelleme Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-pencil-square me-1"></i>Durumu Değiştir
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="#" onclick="bulkUpdateStatus('unpaid'); return false;">
                                <span class="badge bg-danger me-2">●</span>Ödenmedi
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="bulkUpdateStatus('partial'); return false;">
                                <span class="badge bg-warning me-2">●</span>Kısmi Ödeme
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="bulkUpdateStatus('paid'); return false;">
                                <span class="badge bg-success me-2">●</span>Ödendi
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-secondary" href="#" onclick="bulkUpdateStatus('cancelled'); return false;">
                                <span class="badge bg-secondary me-2">●</span>İptal
                            </a>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkDelete()">
                    <i class="bi bi-trash me-1"></i>Seçilenleri Sil
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                    <i class="bi bi-x-lg me-1"></i>Seçimi Temizle
                </button>
            </div>
        </div>
    </div>

    <form id="bulkDeleteForm" action="{{ route('invoices.bulk-destroy') }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>
    
    <form id="bulkStatusForm" action="{{ route('invoices.bulk-status') }}" method="POST" style="display: none;">
        @csrf
        @method('PATCH')
        <input type="hidden" name="ids" id="bulkStatusIds">
        <input type="hidden" name="status" id="bulkStatusValue">
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" title="Tümünü Seç">
                            </th>
                            <th>Fatura No</th>
                            <th>Firma</th>
                            <th>Tarih</th>
                            <th class="text-end">Tutar</th>
                            <th class="text-end">Ödenen</th>
                            <th class="text-end">Kalan</th>
                            <th class="text-center">Durum</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            @php
                                $paid = (float) ($invoice->payments_sum_amount ?? 0);
                                $remaining = max(0, (float) $invoice->amount - $paid);
                                $statusMeta = match ($invoice->status) {
                                    'paid' => ['label' => 'Ödendi', 'class' => 'success'],
                                    'partial' => ['label' => 'Kısmi ödeme', 'class' => 'warning text-dark'],
                                    'cancelled' => ['label' => 'İptal', 'class' => 'secondary'],
                                    default => ['label' => 'Ödenmedi', 'class' => 'danger'],
                                };
                                $canDelete = !in_array($invoice->status, ['paid', 'partial']);
                            @endphp
                            <tr>
                                <td>
                                    @if($canDelete)
                                        <input type="checkbox" class="form-check-input invoice-checkbox" 
                                               value="{{ $invoice->id }}" data-amount="{{ $invoice->amount }}">
                                    @else
                                        <input type="checkbox" class="form-check-input" disabled 
                                               title="Ödeme yapılmış faturalar silinemez">
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">#{{ $invoice->id }}</div>
                                    @if ($invoice->official_number)
                                        <small class="text-muted">{{ $invoice->official_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('firms.show', $invoice->firm) }}" class="text-decoration-none">
                                        {{ $invoice->firm->name }}
                                    </a>
                                </td>
                                <td>{{ $invoice->date?->format('d.m.Y') }}</td>
                                <td class="text-end">{{ Format::money($invoice->amount) }}</td>
                                <td class="text-end text-success">{{ Format::money($paid) }}</td>
                                <td class="text-end {{ $remaining > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ Format::money($remaining) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $statusMeta['class'] }}">
                                        {{ $statusMeta['label'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($canDelete)
                                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Bu faturayı silmek istediğinize emin misiniz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Görüntülenecek fatura bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="row g-2 align-items-center justify-content-between">
                <div class="col-md-6 text-muted small">
                    @if ($invoices->total() > 0)
                        {{ $invoices->firstItem() }} - {{ $invoices->lastItem() }} arası gösteriliyor. Toplam {{ $invoices->total() }} kayıt.
                    @else
                        Kayıt bulunamadı.
                    @endif
                </div>
                <div class="col-md-6">
                    {{ $invoices->onEachSide(1)->appends(['per_page' => $perPage] + $filters)->links('vendor.pagination.bootstrap-5-tr') }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.invoice-checkbox');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCountEl = document.getElementById('selectedCount');
    
    // Tümünü seç/kaldır
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
        });
        updateBulkActionBar();
    });
    
    // Tekil checkbox değişikliği
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActionBar);
    });
    
    function updateBulkActionBar() {
        const checkedCount = document.querySelectorAll('.invoice-checkbox:checked').length;
        selectedCountEl.textContent = checkedCount;
        
        if (checkedCount > 0) {
            bulkActionBar.classList.remove('d-none');
        } else {
            bulkActionBar.classList.add('d-none');
        }
        
        // Tümü seçili mi kontrol et
        selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
    }
});

function bulkDelete() {
    const checked = document.querySelectorAll('.invoice-checkbox:checked');
    const ids = Array.from(checked).map(cb => cb.value);
    
    if (ids.length === 0) {
        alert('Lütfen silinecek faturaları seçin.');
        return;
    }
    
    if (!confirm(`${ids.length} faturayı silmek istediğinize emin misiniz?`)) {
        return;
    }
    
    document.getElementById('bulkDeleteIds').value = ids.join(',');
    document.getElementById('bulkDeleteForm').submit();
}

function clearSelection() {
    document.querySelectorAll('.invoice-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    document.getElementById('bulkActionBar').classList.add('d-none');
}

function bulkUpdateStatus(status) {
    const checked = document.querySelectorAll('.invoice-checkbox:checked');
    const ids = Array.from(checked).map(cb => cb.value);
    
    if (ids.length === 0) {
        alert('Lütfen durumu güncellenecek faturaları seçin.');
        return;
    }
    
    const statusLabels = {
        'unpaid': 'Ödenmedi',
        'partial': 'Kısmi Ödeme',
        'paid': 'Ödendi',
        'cancelled': 'İptal'
    };
    
    if (!confirm(`${ids.length} faturanın durumunu "${statusLabels[status]}" olarak değiştirmek istiyor musunuz?`)) {
        return;
    }
    
    document.getElementById('bulkStatusIds').value = ids.join(',');
    document.getElementById('bulkStatusValue').value = status;
    document.getElementById('bulkStatusForm').submit();
}
</script>
@endpush

@push('styles')
<style>
/* Aylık Fatura Üret - Hero Bölümü */
.monthly-invoice-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    position: relative;
    overflow: hidden;
}

.monthly-invoice-gradient::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    pointer-events: none;
}

.monthly-invoice-gradient::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 250px;
    height: 250px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    pointer-events: none;
}

.monthly-invoice-icon {
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
    backdrop-filter: blur(10px);
}

.generate-btn {
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.generate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.generate-btn::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.3),
        transparent
    );
    transition: left 0.5s;
}

.generate-btn:hover::after {
    left: 100%;
}

.monthly-stat-box {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1rem 0.5rem;
}

.monthly-stat-value {
    font-size: 1.25rem;
    font-weight: 700;
}

.monthly-stat-label {
    font-size: 0.7rem;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Mobil için responsive */
@media (max-width: 991px) {
    .monthly-invoice-gradient {
        text-align: center;
    }
    
    .monthly-invoice-gradient .d-flex {
        justify-content: center;
    }
    
    .monthly-invoice-gradient form {
        justify-content: center !important;
    }
}

@media (max-width: 576px) {
    .monthly-invoice-icon {
        width: 48px;
        height: 48px;
        font-size: 1.5rem;
    }
    
    .generate-btn {
        width: 100%;
    }
}
</style>
@endpush

@endsection

