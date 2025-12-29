@extends('install.layout', ['step' => 6])

@section('content')
<div class="text-center py-4">
    <div class="mb-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
    </div>
    
    <h3 class="mb-3">Kurulum Tamamlandı!</h3>
    
    <p class="text-muted mb-4">
        CastBook başarıyla kuruldu. Artık uygulamayı kullanmaya başlayabilirsiniz.
    </p>
    
    <div class="alert alert-warning text-start">
        <h6><i class="bi bi-shield-exclamation"></i> Güvenlik Uyarısı</h6>
        <p class="mb-0">
            Eğer manuel kurulum dosyaları oluşturduysanız 
            (<code>public/migrate.php</code>, <code>public/test_db.php</code> vb.) 
            bunları silmeyi unutmayın!
        </p>
    </div>
    
    <div class="d-grid gap-2 mt-4">
        <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
            <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
        </a>
    </div>
</div>
@endsection
