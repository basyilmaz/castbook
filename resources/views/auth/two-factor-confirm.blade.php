@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-qr-code text-primary fs-2"></i>
                        </div>
                        <h4 class="fw-semibold">2FA Kurulumu</h4>
                        <p class="text-muted">Authenticator uygulamanızla QR kodu tarayın</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            {{-- QR Kod --}}
                            <div class="text-center mb-4">
                                <div class="bg-white p-3 d-inline-block rounded border">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" 
                                         alt="2FA QR Code"
                                         width="200" height="200">
                                </div>
                            </div>

                            <div class="bg-light rounded p-3 mb-4">
                                <p class="small text-muted mb-2">Manuel giriş için:</p>
                                <code class="user-select-all">{{ $secret }}</code>
                            </div>
                        </div>

                        <div class="col-md-6">
                            {{-- Onay Formu --}}
                            <form method="POST" action="{{ route('two-factor.confirm') }}">
                                @csrf
                                
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Doğrulama Kodu</label>
                                    <input type="text" 
                                           name="code" 
                                           class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                                           placeholder="000000"
                                           maxlength="6"
                                           autocomplete="off"
                                           autofocus
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Uygulamadaki 6 haneli kodu girin</div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="bi bi-check-circle me-1"></i>Onayla ve Etkinleştir
                                </button>

                                <a href="{{ route('two-factor.show') }}" class="btn btn-outline-secondary w-100">
                                    İptal
                                </a>
                            </form>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Kurtarma Kodları --}}
                    <div class="bg-warning bg-opacity-10 border border-warning rounded p-4">
                        <h6 class="fw-semibold text-warning mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>Kurtarma Kodlarınız
                        </h6>
                        <p class="small text-muted mb-3">
                            Bu kodları güvenli bir yerde saklayın. Telefonunuza erişiminizi kaybederseniz bu kodları giriş yapmak için kullanabilirsiniz.
                        </p>
                        <div class="row">
                            @foreach($recoveryCodes as $code)
                                <div class="col-6 col-md-3 mb-2">
                                    <code class="d-block bg-dark text-light p-2 rounded text-center small">{{ $code }}</code>
                                </div>
                            @endforeach
                        </div>
                        <button class="btn btn-sm btn-warning mt-3" onclick="copyRecoveryCodes()">
                            <i class="bi bi-clipboard me-1"></i>Kodları Kopyala
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyRecoveryCodes() {
    const codes = @json($recoveryCodes);
    navigator.clipboard.writeText(codes.join('\n')).then(() => {
        alert('Kurtarma kodları panoya kopyalandı!');
    });
}
</script>
@endsection
