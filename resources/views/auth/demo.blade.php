@extends('layouts.guest')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center" 
     style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                {{-- Logo & Title --}}
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-4 p-3 mb-3"
                         style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);">
                        <i class="bi bi-book-half text-white" style="font-size: 2.5rem;"></i>
                    </div>
                    <h1 class="text-white fw-bold mb-2">CastBook Demo</h1>
                    <p class="text-white-50">Muhasebe takip sistemini test edin</p>
                </div>

                {{-- Demo Card --}}
                <div class="card border-0 shadow-lg" style="border-radius: 1.5rem;">
                    <div class="card-body p-4 p-lg-5">
                        <div class="text-center mb-4">
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                <i class="bi bi-check-circle me-1"></i>Demo hazır
                            </span>
                        </div>

                        <h5 class="text-center mb-4">Demo hesabıyla giriş yapın</h5>

                        {{-- Demo Credentials --}}
                        <div class="bg-light rounded-3 p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted d-block">E-posta</small>
                                            <code class="fs-6" id="demoEmail">demo@castbook.dev</code>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                onclick="copyToClipboard('demoEmail')">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted d-block">Şifre</small>
                                            <code class="fs-6" id="demoPassword">demo123</code>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="copyToClipboard('demoPassword')">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Quick Login Button --}}
                        <form action="{{ route('login') }}" method="POST" id="demoLoginForm">
                            @csrf
                            <input type="hidden" name="email" value="demo@castbook.dev">
                            <input type="hidden" name="password" value="demo123">
                            <input type="hidden" name="remember" value="1">
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Demo'ya Giriş Yap
                            </button>
                        </form>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-muted text-decoration-none">
                                <small>veya kendi hesabınızla giriş yapın</small>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Features --}}
                <div class="row g-3 mt-4">
                    <div class="col-6">
                        <div class="d-flex align-items-center text-white">
                            <div class="rounded-circle bg-white bg-opacity-20 p-2 me-2">
                                <i class="bi bi-building"></i>
                            </div>
                            <small>Firma Yönetimi</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center text-white">
                            <div class="rounded-circle bg-white bg-opacity-20 p-2 me-2">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <small>Fatura Takibi</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center text-white">
                            <div class="rounded-circle bg-white bg-opacity-20 p-2 me-2">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <small>Tahsilat Kaydı</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center text-white">
                            <div class="rounded-circle bg-white bg-opacity-20 p-2 me-2">
                                <i class="bi bi-bar-chart"></i>
                            </div>
                            <small>Raporlama</small>
                        </div>
                    </div>
                </div>

                {{-- Warning --}}
                <div class="alert alert-warning bg-warning bg-opacity-10 border-0 text-center mt-4" 
                     style="backdrop-filter: blur(10px);">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        Demo verileri periyodik olarak sıfırlanır
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const text = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(text).then(() => {
        // Başarılı kopyalama göstergesi
        const btn = event.currentTarget;
        btn.innerHTML = '<i class="bi bi-check text-success"></i>';
        setTimeout(() => {
            btn.innerHTML = '<i class="bi bi-clipboard"></i>';
        }, 1500);
    });
}
</script>
@endsection
