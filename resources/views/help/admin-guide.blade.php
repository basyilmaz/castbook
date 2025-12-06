@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-3">Admin Kılavuzu</h6>
                    <nav class="nav flex-column" id="adminGuideNav">
                        <a class="nav-link px-0" href="#users">
                            <i class="bi bi-people me-2"></i>Kullanıcı Yönetimi
                        </a>
                        <a class="nav-link px-0" href="#audit">
                            <i class="bi bi-journal-text me-2"></i>Aktivite Günlüğü
                        </a>
                        <a class="nav-link px-0" href="#settings">
                            <i class="bi bi-gear me-2"></i>Sistem Ayarları
                        </a>
                        <a class="nav-link px-0" href="#backup">
                            <i class="bi bi-cloud-download me-2"></i>Yedekleme
                        </a>
                        <a class="nav-link px-0" href="#notifications">
                            <i class="bi bi-bell me-2"></i>Bildirim Yönetimi
                        </a>
                        <a class="nav-link px-0" href="#security">
                            <i class="bi bi-shield-check me-2"></i>Güvenlik
                        </a>
                        <a class="nav-link px-0" href="#maintenance">
                            <i class="bi bi-tools me-2"></i>Bakım İşlemleri
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        {{-- İçerik --}}
        <div class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Admin Kılavuzu</h2>
                    <p class="text-muted mb-0">Sistem yönetimi için detaylı dokümantasyon</p>
                </div>
                <a href="{{ route('help') }}" class="btn btn-outline-primary">
                    <i class="bi bi-book me-1"></i>Kullanıcı Kılavuzu
                </a>
            </div>

            {{-- Kullanıcı Yönetimi --}}
            <section id="users" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="fw-semibold mb-3">
                        <i class="bi bi-people text-primary me-2"></i>Kullanıcı Yönetimi
                    </h4>
                    
                    <h6 class="fw-semibold mt-4">Yeni Kullanıcı Oluşturma</h6>
                    <ol>
                        <li>Navbar'dan <strong>Kullanıcılar</strong> menüsüne gidin</li>
                        <li><strong>Yeni Kullanıcı</strong> butonuna tıklayın</li>
                        <li>Gerekli alanları doldurun:
                            <ul>
                                <li><strong>Ad Soyad:</strong> Kullanıcının tam adı</li>
                                <li><strong>E-posta:</strong> Giriş için kullanılacak benzersiz e-posta</li>
                                <li><strong>Şifre:</strong> En az 8 karakter</li>
                                <li><strong>Rol:</strong> Admin veya Standart</li>
                            </ul>
                        </li>
                    </ol>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Admin rolü</strong> olan kullanıcılar tüm sistem ayarlarına erişebilir ve diğer kullanıcıları yönetebilir.
                    </div>

                    <h6 class="fw-semibold mt-4">Kullanıcı Pasifleştirme</h6>
                    <p>Bir kullanıcıyı silmek yerine pasifleştirebilirsiniz:</p>
                    <ol>
                        <li>Kullanıcı düzenleme sayfasına gidin</li>
                        <li><strong>Durum</strong> alanını "Pasif" olarak değiştirin</li>
                        <li>Pasif kullanıcılar sisteme giriş yapamaz</li>
                    </ol>
                </div>
            </section>

            {{-- Aktivite Günlüğü --}}
            <section id="audit" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="fw-semibold mb-3">
                        <i class="bi bi-journal-text text-primary me-2"></i>Aktivite Günlüğü
                    </h4>
                    
                    <p>Sistem, tüm önemli işlemleri otomatik olarak kaydeder:</p>
                    
                    <ul>
                        <li><strong>Giriş/Çıkış:</strong> Kullanıcı oturumları</li>
                        <li><strong>Firma işlemleri:</strong> Oluşturma, güncelleme, silme</li>
                        <li><strong>Fatura işlemleri:</strong> Yeni fatura, düzenleme, iptal</li>
                        <li><strong>Tahsilat işlemleri:</strong> Ödeme kayıtları</li>
                        <li><strong>Dışa aktarma:</strong> PDF/CSV export işlemleri</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Logları Filtreleme</h6>
                    <p>Aktivite sayfasında şu filtreleri kullanabilirsiniz:</p>
                    <ul>
                        <li>Kullanıcıya göre</li>
                        <li>İşlem türüne göre</li>
                        <li>Tarih aralığına göre</li>
                    </ul>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Audit logları sadece admin rolündeki kullanıcılar tarafından görüntülenebilir.
                    </div>
                </div>
            </section>

            {{-- Sistem Ayarları --}}
            <section id="settings" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="fw-semibold mb-3">
                        <i class="bi bi-gear text-primary me-2"></i>Sistem Ayarları
                    </h4>
                    
                    <h6 class="fw-semibold mt-4">Şirket Bilgileri</h6>
                    <ul>
                        <li><strong>Şirket Adı:</strong> Navbar ve raporlarda gösterilen isim</li>
                        <li><strong>Logo:</strong> Sol üst köşede görünen şirket logosu</li>
                        <li><strong>Tema:</strong> Açık, Koyu veya Otomatik mod</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">E-posta Ayarları</h6>
                    <p>SMTP sunucu bilgilerini yapılandırarak e-posta bildirimleri gönderebilirsiniz:</p>
                    <ul>
                        <li>SMTP Host ve Port</li>
                        <li>Kullanıcı adı ve şifre</li>
                        <li>Şifreleme türü (TLS/SSL)</li>
                        <li>Gönderen adresi ve adı</li>
                    </ul>

                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>İpucu:</strong> SMTP ayarlarını yaptıktan sonra "Test E-postası Gönder" ile kontrol edin.
                    </div>
                </div>
            </section>

            {{-- Yedekleme --}}
            <section id="backup" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="fw-semibold mb-3">
                        <i class="bi bi-cloud-download text-primary me-2"></i>Yedekleme
                    </h4>
                    
                    <h6 class="fw-semibold mt-4">Manuel Yedekleme</h6>
                    <ol>
                        <li>Ayarlar sayfasına gidin</li>
                        <li>"Yedekleme" bölümünü bulun</li>
                        <li><strong>Yedek İndir</strong> butonuna tıklayın</li>
                        <li>JSON formatında yedek dosyası indirilecektir</li>
                    </ol>

                    <h6 class="fw-semibold mt-4">Yedekten Geri Yükleme</h6>
                    <ol>
                        <li>Daha önce indirilen yedek dosyasını seçin</li>
                        <li><strong>Geri Yükle</strong> butonuna tıklayın</li>
                        <li>Mevcut veriler yedekteki verilerle değiştirilecektir</li>
                    </ol>

                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-octagon me-2"></i>
                        <strong>Dikkat:</strong> Geri yükleme işlemi geri alınamaz. İşlem öncesi mevcut verilerinizi yedekleyin.
                    </div>
                </div>
            </section>

            {{-- Bildirim Yönetimi --}}
            <section id="notifications" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="fw-semibold mb-3">
                        <i class="bi bi-bell text-primary me-2"></i>Bildirim Yönetimi
                    </h4>
                    
                    <h6 class="fw-semibold mt-4">E-posta Bildirimleri</h6>
                    <ul>
                        <li><strong>Ödeme Hatırlatmaları:</strong> Vadesi yaklaşan ve gecikmiş faturalar için</li>
                        <li><strong>Beyanname Hatırlatmaları:</strong> Son tarihi yaklaşan beyannameler için</li>
                        <li><strong>Haftalık Özet:</strong> Haftalık aktivite raporu</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Bildirim Alıcıları</h6>
                    <p>Birden fazla e-posta adresi ekleyebilirsiniz. Her satıra bir adres yazın.</p>

                    <h6 class="fw-semibold mt-4">Zamanlamalar</h6>
                    <ul>
                        <li>Günlük hatırlatmalar belirlenen saatte gönderilir</li>
                        <li>Haftalık özet seçilen günde gönderilir</li>
                    </ul>
                </div>
            </section>

            {{-- Güvenlik --}}
            <section id="security" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="fw-semibold mb-3">
                        <i class="bi bi-shield-check text-primary me-2"></i>Güvenlik
                    </h4>
                    
                    <h6 class="fw-semibold mt-4">Güvenlik Özellikleri</h6>
                    <ul>
                        <li><strong>Rate Limiting:</strong> Brute-force saldırılarına karşı koruma</li>
                        <li><strong>CSRF Koruması:</strong> Cross-site request forgery önleme</li>
                        <li><strong>Güvenlik Başlıkları:</strong> XSS, Clickjacking koruması</li>
                        <li><strong>Şifre Hashleme:</strong> Bcrypt ile güvenli şifre saklama</li>
                    </ul>

                    <h6 class="fw-semibold mt-4">Öneriler</h6>
                    <ul>
                        <li>Düzenli olarak şifreleri değiştirin</li>
                        <li>Kullanılmayan hesapları pasifleştirin</li>
                        <li>Aktivite günlüğünü düzenli kontrol edin</li>
                        <li>HTTPS kullanın (üretimde zorunlu)</li>
                    </ul>
                </div>
            </section>

            {{-- Bakım İşlemleri --}}
            <section id="maintenance" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="fw-semibold mb-3">
                        <i class="bi bi-tools text-primary me-2"></i>Bakım İşlemleri
                    </h4>
                    
                    <h6 class="fw-semibold mt-4">Cache Temizleme</h6>
                    <p>Performans sorunları yaşıyorsanız terminal üzerinden:</p>
                    <pre class="bg-dark text-light p-3 rounded"><code>php artisan cache:clear
php artisan config:clear
php artisan view:clear</code></pre>

                    <h6 class="fw-semibold mt-4">Veritabanı Optimizasyonu</h6>
                    <p>Büyük veritabanları için periyodik olarak:</p>
                    <pre class="bg-dark text-light p-3 rounded"><code>php artisan optimize</code></pre>

                    <h6 class="fw-semibold mt-4">Log Dosyaları</h6>
                    <p>Log dosyaları <code>storage/logs/</code> klasöründe bulunur. Düzenli olarak temizleyin.</p>
                </div>
            </section>

            {{-- Geri Dön --}}
            <div class="text-center mt-4">
                <a href="{{ route('help') }}" class="btn btn-outline-primary me-2">
                    <i class="bi bi-book me-1"></i>Kullanıcı Kılavuzu
                </a>
                <a href="{{ route('faq') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-question-circle me-1"></i>SSS
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Smooth scroll
document.querySelectorAll('#adminGuideNav a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>
@endsection
