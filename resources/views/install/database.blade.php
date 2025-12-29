@extends('install.layout', ['step' => 2])

@section('content')
<h4 class="mb-4"><i class="bi bi-database"></i> Veritabanı Ayarları</h4>

<form action="{{ route('install.database.test') }}" method="POST">
    @csrf
    
    <div class="mb-3">
        <label class="form-label">Site URL</label>
        <input type="url" name="app_url" class="form-control" 
               value="{{ old('app_url', request()->getSchemeAndHttpHost()) }}" 
               placeholder="https://example.com" required>
        <small class="text-muted">SSL kullanıyorsanız https:// ile başlayın</small>
    </div>

    <hr class="my-4">

    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="form-label">Veritabanı Sunucusu</label>
            <input type="text" name="db_host" class="form-control" 
                   value="{{ old('db_host', 'localhost') }}" required>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Port</label>
            <input type="text" name="db_port" class="form-control" 
                   value="{{ old('db_port', '3306') }}" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Veritabanı Adı</label>
        <input type="text" name="db_database" class="form-control" 
               value="{{ old('db_database') }}" placeholder="castbook" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Kullanıcı Adı</label>
        <input type="text" name="db_username" class="form-control" 
               value="{{ old('db_username') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Şifre</label>
        <input type="password" name="db_password" class="form-control" 
               value="{{ old('db_password') }}">
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('install.requirements') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Geri
        </a>
        <button type="submit" class="btn btn-primary">
            Bağlantıyı Test Et ve Devam Et <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</form>
@endsection
