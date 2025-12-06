@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-semibold mb-1">
                        <i class="bi bi-shield-lock text-primary me-2"></i>İki Faktörlü Doğrulama (2FA)
                    </h4>
                    <p class="text-muted mb-0">Hesabınızı ekstra güvenlik katmanı ile koruyun</p>
                </div>
                <a href="{{ route('settings.edit') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Ayarlara Dön
                </a>
            </div>

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @if($enabled && $confirmed)
                        {{-- 2FA Aktif --}}
                        <div class="text-center py-4">
                            <div class="mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle" style="width: 80px; height: 80px;">
                                    <i class="bi bi-shield-check text-success fs-1"></i>
                                </div>
                            </div>
                            <h5 class="fw-semibold text-success mb-2">2FA Etkin</h5>
                            <p class="text-muted mb-4">
                                Hesabınız iki faktörlü doğrulama ile korunuyor.
                            </p>
                        </div>

                        <hr>

                        <h6 class="fw-semibold mb-3">Kurtarma Kodları</h6>
                        <p class="text-muted small mb-3">
                            Telefonunuza erişiminizi kaybederseniz bu kodları kullanabilirsiniz.
                            Her kod sadece bir kez kullanılabilir.
                        </p>
                        
                        <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Şifreniz</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-outline-warning">
                                <i class="bi bi-arrow-repeat me-1"></i>Kurtarma Kodlarını Yenile
                            </button>
                        </form>

                        @if(session('recoveryCodes'))
                            <div class="alert alert-warning mt-3">
                                <h6 class="alert-heading mb-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Yeni Kurtarma Kodlarınız
                                </h6>
                                <p class="small mb-2">Bu kodları güvenli bir yerde saklayın!</p>
                                <div class="bg-dark text-light p-3 rounded font-monospace">
                                    @foreach(session('recoveryCodes') as $code)
                                        <div>{{ $code }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <hr class="my-4">

                        <h6 class="fw-semibold text-danger mb-3">2FA'yı Devre Dışı Bırak</h6>
                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf
                            @method('DELETE')
                            <div class="mb-3">
                                <label class="form-label">Şifreniz</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('2FA\'yı devre dışı bırakmak istediğinizden emin misiniz?')">
                                <i class="bi bi-shield-x me-1"></i>2FA'yı Kapat
                            </button>
                        </form>
                    @else
                        {{-- 2FA Pasif --}}
                        <div class="text-center py-4">
                            <div class="mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-circle" style="width: 80px; height: 80px;">
                                    <i class="bi bi-shield-exclamation text-warning fs-1"></i>
                                </div>
                            </div>
                            <h5 class="fw-semibold mb-2">2FA Etkin Değil</h5>
                            <p class="text-muted mb-4">
                                İki faktörlü doğrulama ile hesabınızı daha güvenli hale getirin.
                            </p>
                        </div>

                        <div class="bg-light rounded p-4 mb-4">
                            <h6 class="fw-semibold mb-3">
                                <i class="bi bi-info-circle text-primary me-2"></i>Nasıl Çalışır?
                            </h6>
                            <ol class="mb-0 ps-3">
                                <li class="mb-2">Google Authenticator veya benzer bir uygulama indirin</li>
                                <li class="mb-2">QR kodu tarayın veya kodu manuel girin</li>
                                <li class="mb-2">Her girişte uygulamadan 6 haneli kodu girin</li>
                                <li>Telefonunuza erişiminizi kaybederseniz kurtarma kodlarını kullanın</li>
                            </ol>
                        </div>

                        <form method="POST" action="{{ route('two-factor.enable') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Şifrenizi Onaylayın</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-shield-plus me-2"></i>2FA'yı Etkinleştir
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
