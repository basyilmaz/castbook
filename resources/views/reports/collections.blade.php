@extends('layouts.app')

@php
    use App\Support\Format;
@endphp

@section('content')
<div class="mb-3">
    <h4 class="mb-0">Aylık Tahsilat Raporu</h4>
    <small class="text-muted">Seçilen yıl için firma bazında tahsilat toplamları.</small>
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
                @foreach ([6, 12, 24] as $size)
                    <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid gap-2">
            <button type="submit" class="btn btn-primary">Filtrele</button>
            <a href="{{ route('reports.collections') }}" class="btn btn-light">Temizle</a>
        </div>
    </div>
    <div class="card-footer bg-light">
        <a href="{{ route('reports.collections.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-download me-1"></i>CSV İndir
        </a>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted text-uppercase">Yıl İçi Toplam Tahsilat</small>
                <div class="h4 mb-0 text-success">{{ Format::money($totals['year_total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted text-uppercase">Tahsilat Sayısı</small>
                <div class="h4 mb-0">{{ number_format($totals['payment_count'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Tahsilat Grafiği --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="card-title mb-3">
            <i class="bi bi-bar-chart me-2 text-primary"></i>{{ $year }} Yılı Aylık Tahsilat Grafiği
        </h6>
        <div style="height: 300px;">
            <canvas id="collectionsChart"></canvas>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ay</th>
                        <th class="text-end">Tahsilat Toplamı</th>
                        <th class="text-end">İşlem Adedi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($monthly as $row)
                        <tr>
                            <td>{{ Format::monthLabel($row->period) }}</td>
                            <td class="text-end text-success">{{ Format::money($row->total_amount) }}</td>
                            <td class="text-end">{{ number_format($row->payment_count, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Veri bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="text-muted small">
                Toplam {{ $monthly->total() }} kayıt · Sayfa {{ $monthly->currentPage() }} / {{ $monthly->lastPage() }}
            </div>
            {{ $monthly->appends(request()->query())->links('vendor.pagination.bootstrap-5-tr') }}
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('collectionsChart');
    if (!ctx) return;
    
    // Aylık veriyi JSON formatına çevir
    const monthlyData = @json($monthly->items());
    
    // 12 aya göre veriyi düzenle
    const months = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 
                    'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    const values = new Array(12).fill(0);
    
    monthlyData.forEach(row => {
        const parts = row.period.split('-');
        const monthIndex = parseInt(parts[1]) - 1;
        values[monthIndex] = parseFloat(row.total_amount);
    });
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Tahsilat (₺)',
                data: values,
                backgroundColor: 'rgba(34, 197, 94, 0.7)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return new Intl.NumberFormat('tr-TR', {
                                style: 'currency',
                                currency: 'TRY'
                            }).format(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('tr-TR', {
                                style: 'currency',
                                currency: 'TRY',
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
