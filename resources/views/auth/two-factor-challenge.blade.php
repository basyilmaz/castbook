@extends('layouts.guest')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-shield-lock text-primary fs-2"></i>
                </div>
                <h4 class="fw-semibold">İki Faktörlü Doğrulama</h4>
                <p class="text-muted">Authenticator uygulamanızdaki kodu girin</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('two-factor.verify') }}" id="twoFactorForm">
                        @csrf
                        
                        <div class="mb-4" id="codeSection">
                            <label class="form-label">Doğrulama Kodu</label>
                            <input type="text" 
                                   name="code" 
                                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                                   placeholder="000000"
                                   maxlength="6"
                                   autocomplete="off"
                                   autofocus>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 d-none" id="recoverySection">
                            <label class="form-label">Kurtarma Kodu</label>
                            <input type="text" 
                                   name="recovery_code" 
                                   class="form-control form-control-lg text-center @error('recovery_code') is-invalid @enderror"
                                   placeholder="XXXXX-XXXXX"
                                   autocomplete="off">
                            @error('recovery_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-check-circle me-1"></i>Doğrula
                        </button>

                        <button type="button" class="btn btn-outline-secondary w-100" id="toggleMethod">
                            <i class="bi bi-key me-1"></i>Kurtarma Kodu Kullan
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-muted text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Giriş Sayfasına Dön
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('toggleMethod').addEventListener('click', function() {
    const codeSection = document.getElementById('codeSection');
    const recoverySection = document.getElementById('recoverySection');
    const button = this;
    
    if (codeSection.classList.contains('d-none')) {
        codeSection.classList.remove('d-none');
        recoverySection.classList.add('d-none');
        recoverySection.querySelector('input').value = '';
        button.innerHTML = '<i class="bi bi-key me-1"></i>Kurtarma Kodu Kullan';
    } else {
        codeSection.classList.add('d-none');
        recoverySection.classList.remove('d-none');
        codeSection.querySelector('input').value = '';
        button.innerHTML = '<i class="bi bi-phone me-1"></i>Authenticator Kodu Kullan';
    }
});
</script>
@endsection
