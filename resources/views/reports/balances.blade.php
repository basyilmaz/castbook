@extends('layouts.app')

@php
    use App\Support\Format;
@endphp

@section('content')
<div class="mb-3">
    <h4 class="mb-0">Müşteri Bazında Bakiye</h4>
    <small class="text-muted">Borç / alacak dağılımı ve güncel bakiyeler.</small>
</div>

@include('reports._tabs')

<form method="GET" class="card border-0 shadow-sm mb-3">
    <div class="card-body row g-3 align-items-end">
        <div class="col-md-4">
            <label for="status" class="form-label">Durum</label>
            <select name="status" id="status" class="form-select">
                <option value="">Tümü</option>
                <option value="active" @selected($status === 'active')>Aktif</option>
                <option value="inactive" @selected($status === 'inactive')>Pasif</option>
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
            <a href="{{ route('reports.balance') }}" class="btn btn-light">Temizle</a>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <a href="{{ route('reports.balance.export', request()->query()) }}" class="btn btn-outline-success flex-grow-1">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
            </a>
            <a href="{{ route('reports.balance.pdf', request()->query()) }}" class="btn btn-outline-danger flex-grow-1">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
        </div>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted text-uppercase">Toplam Borç</small>
                <div class="h4 mb-0 text-danger">{{ Format::money($totals['debit']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted text-uppercase">Toplam Tahsilat</small>
                <div class="h4 mb-0 text-success">{{ Format::money($totals['credit']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted text-uppercase">Net Bakiye</small>
                <div class="h4 mb-0 {{ $totals['balance'] > 0 ? 'text-danger' : ($totals['balance'] < 0 ? 'text-success' : '') }}">
                    {{ Format::money($totals['balance']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Firma</th>
                        <th>Durum</th>
                        <th class="text-end">Borç</th>
                        <th class="text-end">Tahsilat</th>
                        <th class="text-end">Bakiye</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($firms as $firm)
                        @php $balance = $firm->balance_total; @endphp
                        <tr>
                            <td>
                                <a href="{{ route('firms.show', $firm) }}" class="text-decoration-none fw-semibold">
                                    {{ $firm->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-{{ $firm->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $firm->status === 'active' ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="text-end text-danger">{{ Format::money($firm->debit_total) }}</td>
                            <td class="text-end text-success">{{ Format::money($firm->credit_total) }}</td>
                            <td class="text-end {{ $balance > 0 ? 'text-danger' : ($balance < 0 ? 'text-success' : '') }}">
                                {{ Format::money($balance) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Kayıt bulunamadı.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="text-muted small">
                Toplam {{ $firms->total() }} kayıt · Sayfa {{ $firms->currentPage() }} / {{ $firms->lastPage() }}
            </div>
            {{ $firms->links() }}
        </div>
    </div>
</div>
@endsection
