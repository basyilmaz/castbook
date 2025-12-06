@extends('layouts.app')

@php
    $pageTitle = 'Fatura İçe Aktar';
@endphp

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Başlık --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-semibold mb-1">
                        <i class="bi bi-upload text-primary me-2"></i>Fatura İçe Aktar
                    </h4>
                    <p class="text-muted mb-0">CSV dosyasından toplu fatura yükleyin</p>
                </div>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Geri
                </a>
            </div>

            {{-- Hata mesajları --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <h6 class="alert-heading mb-2">
                        <i class="bi bi-exclamation-circle me-1"></i>Hatalar
                    </h6>
                    <ul class="mb-0 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Talimatlar --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i>CSV Dosya Formatı
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">CSV dosyanız aşağıdaki sütunları içermelidir:</p>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th>Sütun</th>
                                    <th>Zorunlu</th>
                                    <th>Format</th>
                                    <th>Örnek</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>firma</code></td>
                                    <td><span class="badge bg-danger">Evet</span></td>
                                    <td>Metin</td>
                                    <td>ABC Ltd.</td>
                                </tr>
                                <tr>
                                    <td><code>tutar</code></td>
                                    <td><span class="badge bg-danger">Evet</span></td>
                                    <td>Sayı (virgüllü)</td>
                                    <td>1.500,00</td>
                                </tr>
                                <tr>
                                    <td><code>tarih</code></td>
                                    <td><span class="badge bg-danger">Evet</span></td>
                                    <td>gg.aa.yyyy</td>
                                    <td>15.12.2024</td>
                                </tr>
                                <tr>
                                    <td><code>vade_tarihi</code></td>
                                    <td><span class="badge bg-secondary">Hayır</span></td>
                                    <td>gg.aa.yyyy</td>
                                    <td>15.01.2025</td>
                                </tr>
                                <tr>
                                    <td><code>fatura_no</code></td>
                                    <td><span class="badge bg-secondary">Hayır</span></td>
                                    <td>Metin</td>
                                    <td>FA-2024-001</td>
                                </tr>
                                <tr>
                                    <td><code>aciklama</code></td>
                                    <td><span class="badge bg-secondary">Hayır</span></td>
                                    <td>Metin</td>
                                    <td>Aylık hizmet</td>
                                </tr>
                                <tr>
                                    <td><code>durum</code></td>
                                    <td><span class="badge bg-secondary">Hayır</span></td>
                                    <td>ödenmedi, kısmi, ödendi, iptal</td>
                                    <td>ödenmedi</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>İpucu:</strong> CSV ayırıcı olarak noktalı virgül (;) kullanın.
                        <a href="{{ route('invoices.import.template') }}" class="alert-link">
                            Örnek şablon indir
                        </a>
                    </div>
                </div>
            </div>

            {{-- Upload Form --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('invoices.import') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">CSV Dosyası Seçin</label>
                            <input type="file" 
                                   name="csv_file" 
                                   class="form-control form-control-lg @error('csv_file') is-invalid @enderror"
                                   accept=".csv,.txt"
                                   required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maksimum 5MB, CSV formatında</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                <i class="bi bi-upload me-2"></i>Faturaları İçe Aktar
                            </button>
                            <a href="{{ route('invoices.import.template') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-download me-1"></i>Şablon
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- İpuçları --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-question-circle text-warning me-2"></i>Dikkat Edilmesi Gerekenler</h6>
                    <ul class="mb-0 text-muted small">
                        <li>Firma adları sistemdeki kayıtlarla eşleşmelidir (kısmi eşleşme desteklenir)</li>
                        <li>Aynı fatura numarasına sahip kayıtlar atlanır</li>
                        <li>Vade tarihi belirtilmezse, fatura tarihinden 30 gün sonrası alınır</li>
                        <li>Tutarlar Türk Lirası olarak kaydedilir</li>
                        <li>Dosya UTF-8 kodlamasında olmalıdır</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
