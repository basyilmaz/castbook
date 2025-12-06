@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-semibold mb-1">Toplu Firma Ekle</h4>
                    <p class="text-muted mb-0">CSV dosyasından birden fazla firma ekleyin.</p>
                </div>
                <a href="{{ route('firms.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left me-1"></i>Firmalar
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="row g-4">
                {{-- Upload Form --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-upload me-2"></i>Dosya Yükle</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('firms.import.process') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <label for="file" class="form-label">CSV Dosyası</label>
                                    <input type="file" class="form-control form-control-lg" id="file" name="file" 
                                           accept=".csv,.txt" required>
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Dosya formatı: CSV (virgül veya noktalı virgül ile ayrılmış)
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-cloud-upload me-2"></i>Firmaları İçe Aktar
                                    </button>
                                    <a href="{{ route('firms.import.template') }}" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-download me-2"></i>Örnek Şablon İndir
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Talimatlar --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Desteklenen Alanlar</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Alan</th>
                                        <th>Zorunlu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>Firma Adı</code></td>
                                        <td><span class="badge bg-danger">Evet</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>Vergi No</code></td>
                                        <td><span class="badge bg-secondary">Hayır</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>Aylık Ücret</code></td>
                                        <td><span class="badge bg-secondary">Hayır</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>Yetkili</code></td>
                                        <td><span class="badge bg-secondary">Hayır</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>Telefon</code></td>
                                        <td><span class="badge bg-secondary">Hayır</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>E-posta</code></td>
                                        <td><span class="badge bg-secondary">Hayır</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>Adres</code></td>
                                        <td><span class="badge bg-secondary">Hayır</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>Şirket Türü</code></td>
                                        <td><span class="badge bg-secondary">Hayır</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>Notlar</code></td>
                                        <td><span class="badge bg-secondary">Hayır</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- İpuçları --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>İpuçları</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    İlk satır sütun başlıkları olmalıdır.
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Aynı vergi numarası veya firma adı varsa güncelleme yapılır.
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Aylık ücret için <code>3500</code> veya <code>3.500,00</code> formatı kullanabilirsiniz.
                                </li>
                                <li class="mb-3">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Dosya UTF-8 kodlamasında olmalıdır (Türkçe karakterler için).
                                </li>
                                <li class="mb-0">
                                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                    Maksimum dosya boyutu: 5MB
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
