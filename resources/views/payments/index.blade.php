@extends('layouts.app')

@php
    use App\Support\Format;
@endphp

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h4 class="fw-semibold mb-0">Tahsilatlar</h4>
            <small class="text-muted">Tahsilat kayıtlarınızı görüntüleyin ve yönetin.</small>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2 mt-3 mt-md-0">
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Yeni Tahsilat Ekle
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('payments.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
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
                    <label class="form-label text-muted text-uppercase small">Ay</label>
                    <input type="month" name="month" class="form-control" value="{{ $filters['month'] ?? '' }}">
                </div>
                <div class="col-md-3">
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
                    <a href="{{ route('payments.index', ['per_page' => $perPage]) }}" class="btn btn-outline-secondary">Temizle</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tarih</th>
                            <th>Firma</th>
                            <th>Fatura</th>
                            <th class="text-end">Tutar</th>
                            <th>Yöntem</th>
                            <th>Not</th>
                            <th class="text-end d-none d-md-table-cell">İşlem</th>
                            <th class="text-end d-table-cell d-md-none">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->date?->format('d.m.Y') }}</td>
                                <td>
                                    @if ($payment->firm)
                                        <a href="{{ route('firms.show', $payment->firm) }}" class="text-decoration-none">
                                            {{ $payment->firm->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Silinmiş Firma</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($payment->invoice)
                                        <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-decoration-none">
                                            #{{ $payment->invoice->id }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end text-success">{{ Format::money($payment->amount) }}</td>
                                <td>{{ $payment->method ?? '-' }}</td>
                                <td>{{ $payment->note ?? '-' }}</td>
                                <td class="text-end d-none d-md-table-cell">
                                    <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Bu tahsilat kaydını silmek istediğinize emin misiniz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end d-table-cell d-md-none">
                                    <div class="dropdown table-actions-mobile">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if ($payment->firm)
                                                <li><a class="dropdown-item" href="{{ route('firms.show', $payment->firm) }}"><i class="bi bi-eye me-2"></i>Görüntüle</a></li>
                                            @endif
                                            <li>
                                                <form action="{{ route('payments.destroy', $payment) }}" method="POST" onsubmit="return confirm('Bu tahsilat kaydını silmek istediğinize emin misiniz?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Sil</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Görüntülenecek tahsilat bulunamadı.
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
                    @if ($payments->total() > 0)
                        {{ $payments->firstItem() }} - {{ $payments->lastItem() }} arası gösteriliyor. Toplam {{ $payments->total() }} kayıt.
                    @else
                        Kayıt bulunamadı.
                    @endif
                </div>
                <div class="col-md-6">
                    {{ $payments->onEachSide(1)->appends(['per_page' => $perPage] + $filters)->links('vendor.pagination.bootstrap-5-tr') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
