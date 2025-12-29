@extends('install.layout', ['step' => 5])

@section('content')
<h4 class="mb-4"><i class="bi bi-building"></i> Şirket Bilgileri</h4>

<form action="{{ route('install.settings.save') }}" method="POST">
    @csrf
    
    <div class="mb-3">
        <label class="form-label">Şirket Adı <span class="text-danger">*</span></label>
        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" 
               value="{{ old('company_name') }}" required>
        @error('company_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">E-posta Adresi</label>
        <input type="email" name="company_email" class="form-control" 
               value="{{ old('company_email') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Telefon</label>
        <input type="text" name="company_phone" class="form-control" 
               value="{{ old('company_phone') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Adres</label>
        <textarea name="company_address" class="form-control" rows="2">{{ old('company_address') }}</textarea>
    </div>

    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Bu bilgileri daha sonra Ayarlar bölümünden değiştirebilirsiniz.
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('install.admin') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Geri
        </a>
        <button type="submit" class="btn btn-primary">
            Kurulumu Tamamla <i class="bi bi-check-lg"></i>
        </button>
    </div>
</form>
@endsection
