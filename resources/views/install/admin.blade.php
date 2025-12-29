@extends('install.layout', ['step' => 4])

@section('content')
<h4 class="mb-4"><i class="bi bi-person-badge"></i> Admin Hesabı Oluştur</h4>

<form action="{{ route('install.admin.create') }}" method="POST">
    @csrf
    
    <div class="mb-3">
        <label class="form-label">Ad Soyad</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
               value="{{ old('name') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">E-posta Adresi</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
               value="{{ old('email') }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Şifre</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
               required minlength="8">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">En az 8 karakter</small>
    </div>

    <div class="mb-3">
        <label class="form-label">Şifre Tekrar</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('install.migration') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Geri
        </a>
        <button type="submit" class="btn btn-primary">
            Hesap Oluştur <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</form>
@endsection
