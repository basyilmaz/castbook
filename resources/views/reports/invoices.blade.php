@extends('layouts.app')

@php
    use App\Support\Format;

    $statusLabels = [
        'unpaid' => ['label' => 'Ödenmedi', 'class' => 'danger'],
        'partial' => ['label' => 'Kısmi Ödeme', 'class' => 'warning text-dark'],
        'paid' => ['label' => 'Ödendi', 'class' => 'success'],
        'cancelled' => ['label' => 'İptal', 'class' => 'secondary'],
    ];
@endphp

@section('content')
<div class="mb-3">
    <h4 class="mb-0">Fatura Durum Raporu</h4>
    <small class="text-muted">Yıl bazında fatura dağılımı ve ödeme durum analizi.</small>
</div>

@include('reports._tabs')

<form method="GET" class="card border-0 shadow-sm mb-3">
    <div class="card-body row g-3 align-items-end">
        <div class="col-md-4">
            <label for="firm_id" class="form-label">Firma</label>
            <select name="firm_id" id="firm_id" class="form-select">
                <option value="">Tümü</option>
                @foreach ($firms as $firm)
                    <option value="{{ $firm->id }}" @selected($selectedFirm == $firm->id)>{{ $firm->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="year" class="form-label">Yıl</label>
            <select name="year" id="year" class="form-select">
                @foreach ($years as $item)
                    <option value="{{ $item }}" @selected($year == $item)>{{ $item }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="per_page" class="form-label">Sayfa Başına Kayıt</label>
            <select name="per_page" id="per_page" class="form-select">
                @foreach ([10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid gap-2">
            <button type="submit" class="btn btn-primary">Filtrele</button>
            <a href="{{ route('reports.invoices') }}" class="btn btn-light">Temizle</a>
        </div>
    </div>
    <div class="card-footer bg-light d-flex gap-2">
        <a href="{{ route('reports.invoices.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
        </a>
        <a href="{{ route('reports.invoices.pdf', request()->query()) }}" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted text-uppercase">Fatura Sayısı</small>
                <div class="h4 mb-0">{{ number_format($summary['count'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted text-uppercase">Toplam Tutar</small>
                <div class="h4 mb-0">{{ Format::money($summary['total_amount']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted text-uppercase">Ödenen Toplam</small>
                <div class="h4 mb-0 text-success">{{ Format::money($summary['paid_amount']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted text-uppercase">Bekleyen Toplam</small>
                <div class="h4 mb-0 text-danger">{{ Format::money($summary['unpaid_amount']) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Durum Dağılımı ve Grafik --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Durum Dağılımı</h6>
                <canvas id="statusChart" style="max-height: 200px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Aylık Fatura Dağılımı</h6>
                <div style="height: 200px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">Durum Özeti</h6>
        <div class="d-flex gap-3 flex-wrap">
            @foreach ($statusLabels as $key => $meta)
                <div>
                    <span class="badge bg-{{ $meta['class'] }} me-2">{{ $meta['label'] }}</span>
                    {{ number_format($statusBreakdown[$key] ?? 0, 0, ',', '.') }} adet
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Firma</th>
                        <th>Tarih</th>
                        <th>Vade</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->id }}</td>
                            <td>
                                <a href="{{ route('firms.show', $invoice->firm) }}" class="text-decoration-none">
                                    {{ $invoice->firm->name }}
                                </a>
                            </td>
                            <td>{{ $invoice->date?->format('d.m.Y') }}</td>
                            <td>{{ $invoice->due_date?->format('d.m.Y') ?? '-' }}</td>
                            <td>{{ Format::money($invoice->amount) }}</td>
                            <td>
                                @php $meta = $statusLabels[$invoice->status] ?? $statusLabels['unpaid']; @endphp
                                <span class="badge bg-{{ $meta['class'] }}">{{ $meta['label'] }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                    Görüntüle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Fatura bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="text-muted small">
                Toplam {{ $invoices->total() }} kayıt · Sayfa {{ $invoices->currentPage() }} / {{ $invoices->lastPage() }}
            </div>
            {{ $invoices->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Durum pasta grafiği
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusData = @json($statusBreakdown);
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Ödenmedi', 'Kısmi Ödeme', 'Ödendi', 'İptal'],
                datasets: [{
                    data: [
                        statusData.unpaid || 0,
                        statusData.partial || 0,
                        statusData.paid || 0,
                        statusData.cancelled || 0
                    ],
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(100, 116, 139, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 10 }
                    }
                }
            }
        });
    }

    // Aylık fatura grafiği
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        const invoices = @json($invoices->items());
        const months = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 
                        'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        const values = new Array(12).fill(0);
        
        invoices.forEach(inv => {
            if (inv.date) {
                const month = new Date(inv.date).getMonth();
                values[month] += parseFloat(inv.amount);
            }
        });
        
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Fatura Tutarı',
                    data: values,
                    borderColor: 'rgb(37, 99, 235)',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('tr-TR', {
                                    style: 'currency', currency: 'TRY', maximumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
