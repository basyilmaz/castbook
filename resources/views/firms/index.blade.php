@extends('layouts.app')

@php
    use App\Support\Format;
@endphp

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <div>
            <h4 class="fw-semibold mb-0">Firmalar</h4>
            <small class="text-muted">Müşteri listesi, durum ve aylık ücret bilgileri.</small>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('firms.import') }}" class="btn btn-outline-primary">
                <i class="bi bi-upload me-1"></i>Toplu Ekle
            </a>
            <a href="{{ route('firms.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Yeni Firma Ekle
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('firms.index') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted text-uppercase small" for="search">Arama</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Firma adı, vergi no veya e-posta"
                           value="{{ $search }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted text-uppercase small" for="per_page">Sayfa Boyutu</label>
                    <select name="per_page" id="per_page" class="form-select" onchange="this.form.submit()">
                        @foreach ([10, 20, 50, 100] as $size)
                            <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }} / sayfa</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i> Ara
                    </button>
                    <a href="{{ route('firms.index') }}" class="btn btn-outline-secondary">Temizle</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Firma Adı</th>
                            <th>Vergi No</th>
                            <th class="text-end">Aylık Ücret</th>
                            <th>E-posta</th>
                            <th>Durum</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($firms as $firm)
                            <tr>
                                <td>
                                    <a href="{{ route('firms.show', $firm) }}" class="text-decoration-none fw-semibold">
                                        {{ $firm->name }}
                                    </a>
                                </td>
                                <td>{{ $firm->tax_no ?? '-' }}</td>
                                <td class="text-end">{{ Format::money($firm->monthly_fee) }}</td>
                                <td>{{ $firm->contact_email ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $firm->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ $firm->status === 'active' ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('firms.edit', $firm) }}" class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('firms.destroy', $firm) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('⚠️ DİKKAT!\n\n{{ $firm->name }} firması ve tüm verileri KALICI olarak silinecektir:\n\n• Tüm faturalar\n• Tüm ödemeler\n• Tüm beyannameler\n• Tüm cari işlemler\n\nBu işlem geri alınamaz!\n\nDevam etmek istiyor musunuz?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Firmayı Sil">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Görüntülenecek firma bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="row g-2 align-items-center justify-content-between">
                <div class="col-md-6 text-muted small">
                    @if ($firms->total() > 0)
                        {{ $firms->firstItem() }} - {{ $firms->lastItem() }} arası gösteriliyor. Toplam {{ $firms->total() }} kayıt.
                    @else
                        Kayıt bulunamadı.
                    @endif
                </div>
                <div class="col-md-6">
                    {{ $firms->onEachSide(1)->appends(['search' => $search, 'per_page' => $perPage])->links('vendor.pagination.bootstrap-5-tr') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
