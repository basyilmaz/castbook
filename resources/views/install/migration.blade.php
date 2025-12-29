@extends('install.layout', ['step' => 3])

@section('content')
<h4 class="mb-4"><i class="bi bi-arrow-repeat"></i> Veritabanı Kurulumu</h4>

<div class="text-center mb-4">
    <div class="spinner-border text-primary mb-3 d-none" id="loadingSpinner" role="status">
        <span class="visually-hidden">Yükleniyor...</span>
    </div>
    
    <p class="text-muted" id="statusText">
        Veritabanı tablolarını oluşturmak için butona tıklayın.
    </p>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    Bu adımda veritabanı tabloları oluşturulacak ve varsayılan veriler yüklenecektir.
</div>

<form action="{{ route('install.migration.run') }}" method="POST" id="migrationForm">
    @csrf
    
    <div class="d-flex justify-content-between">
        <a href="{{ route('install.database') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Geri
        </a>
        <button type="submit" class="btn btn-primary" id="runBtn">
            <i class="bi bi-play-fill"></i> Migration Çalıştır
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('migrationForm').addEventListener('submit', function() {
    document.getElementById('loadingSpinner').classList.remove('d-none');
    document.getElementById('statusText').textContent = 'Migration çalıştırılıyor, lütfen bekleyin...';
    document.getElementById('runBtn').disabled = true;
    document.getElementById('runBtn').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Çalışıyor...';
});
</script>
@endpush
