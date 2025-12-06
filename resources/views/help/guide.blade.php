@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        {{-- Sidebar Navigation --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 1rem;">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-book me-2"></i>Kullanıcı Kılavuzu
                </div>
                <div class="list-group list-group-flush" id="guideNav">
                    <a href="#giris" class="list-group-item list-group-item-action">
                        <i class="bi bi-house me-2 text-muted"></i>Giriş
                    </a>
                    <a href="#dashboard" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2 me-2 text-muted"></i>Dashboard
                    </a>
                    <a href="#firmalar" class="list-group-item list-group-item-action">
                        <i class="bi bi-building me-2 text-muted"></i>Firma Yönetimi
                    </a>
                    <a href="#faturalar" class="list-group-item list-group-item-action">
                        <i class="bi bi-receipt me-2 text-muted"></i>Faturalar
                    </a>
                    <a href="#tahsilatlar" class="list-group-item list-group-item-action">
                        <i class="bi bi-cash-coin me-2 text-muted"></i>Tahsilatlar
                    </a>
                    <a href="#beyannameler" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark-text me-2 text-muted"></i>Beyannameler
                    </a>
                    <a href="#raporlar" class="list-group-item list-group-item-action">
                        <i class="bi bi-bar-chart me-2 text-muted"></i>Raporlar
                    </a>
                    <a href="#ayarlar" class="list-group-item list-group-item-action">
                        <i class="bi bi-gear me-2 text-muted"></i>Ayarlar
                    </a>
                    <a href="#kisayollar" class="list-group-item list-group-item-action">
                        <i class="bi bi-keyboard me-2 text-muted"></i>Kısayollar
                    </a>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="col-lg-9">
            {{-- Giriş --}}
            <section id="giris" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title text-primary"><i class="bi bi-house me-2"></i>Hoş Geldiniz</h4>
                    <p class="lead">
                        CastBook, muhasebe bürolarının müşteri takibini kolaylaştırmak için tasarlanmış kapsamlı bir sistemdir.
                    </p>
                    <div class="alert alert-info">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>İpucu:</strong> Hızlı arama için <kbd>Ctrl</kbd> + <kbd>K</kbd> tuşlarına basabilirsiniz.
                    </div>
                    <h6>Temel Özellikler:</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle text-success fs-4 me-2"></i>
                                <div>
                                    <strong>Firma Yönetimi</strong>
                                    <small class="d-block text-muted">Müşteri firmalarınızı ekleyin ve yönetin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle text-success fs-4 me-2"></i>
                                <div>
                                    <strong>Fatura Takibi</strong>
                                    <small class="d-block text-muted">Faturaları ve ödeme durumlarını izleyin</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle text-success fs-4 me-2"></i>
                                <div>
                                    <strong>Tahsilat Kaydı</strong>
                                    <small class="d-block text-muted">Ödemeleri kaydedin ve raporlayın</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-check-circle text-success fs-4 me-2"></i>
                                <div>
                                    <strong>Beyanname Takibi</strong>
                                    <small class="d-block text-muted">Vergi beyannamesi tarihlerini yönetin</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Dashboard --}}
            <section id="dashboard" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title text-primary"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>
                    <p>Dashboard, sisteminizin genel durumunu gösteren ana sayfadır.</p>
                    
                    <h6 class="mt-4">KPI Kartları</h6>
                    <ul>
                        <li><strong>Toplam Firma:</strong> Sistemdeki aktif firma sayısı</li>
                        <li><strong>Bekleyen Fatura:</strong> Ödenmemiş faturaların toplam tutarı</li>
                        <li><strong>Aylık Tahsilat:</strong> Bu ay yapılan tahsilatların toplamı</li>
                        <li><strong>Yaklaşan Beyanname:</strong> 7 gün içinde son tarihi olan beyanname sayısı</li>
                    </ul>

                    <h6 class="mt-4">Grafikler</h6>
                    <ul>
                        <li><strong>Aylık Gelir Trendi:</strong> Son 6 ayın tahsilat grafiği</li>
                        <li><strong>Fatura vs Tahsilat:</strong> Karşılaştırmalı çubuk grafik</li>
                    </ul>

                    <h6 class="mt-4">Son İşlemler</h6>
                    <p>Sağ panelde son eklenen faturalar ve tahsilatlar görüntülenir.</p>
                </div>
            </section>

            {{-- Firmalar --}}
            <section id="firmalar" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title text-primary"><i class="bi bi-building me-2"></i>Firma Yönetimi</h4>
                    
                    <h6 class="mt-4">Yeni Firma Ekleme</h6>
                    <ol>
                        <li>Menüden <strong>Firmalar</strong>'a tıklayın</li>
                        <li><strong>Yeni Firma</strong> butonuna tıklayın</li>
                        <li>Gerekli alanları doldurun (Firma Adı zorunludur)</li>
                        <li><strong>Kaydet</strong> butonuna tıklayın</li>
                    </ol>

                    <h6 class="mt-4">Toplu Firma Ekleme (CSV)</h6>
                    <ol>
                        <li>Firmalar listesinden <strong>Toplu Ekle</strong> butonuna tıklayın</li>
                        <li>Örnek şablonu indirin ve doldurun</li>
                        <li>CSV dosyanızı yükleyin</li>
                        <li>İşlem sonucunu kontrol edin</li>
                    </ol>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Dikkat:</strong> Vergi numarası aynı olan firmalar güncellenir, yenileri eklenir.
                    </div>

                    <h6 class="mt-4">Firma Detayı</h6>
                    <p>Firma listesinden bir firmaya tıkladığınızda:</p>
                    <ul>
                        <li>Firma bilgilerini düzenleyebilirsiniz</li>
                        <li>Fatura ve tahsilat geçmişini görebilirsiniz</li>
                        <li>Hesap ekstresi oluşturabilirsiniz</li>
                        <li>Fiyat geçmişini yönetebilirsiniz</li>
                    </ul>
                </div>
            </section>

            {{-- Faturalar --}}
            <section id="faturalar" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title text-primary"><i class="bi bi-receipt me-2"></i>Faturalar</h4>
                    
                    <h6 class="mt-4">Fatura Durumları</h6>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-danger">Ödenmedi</span>
                        <span class="badge bg-warning text-dark">Kısmi Ödeme</span>
                        <span class="badge bg-success">Ödendi</span>
                        <span class="badge bg-secondary">İptal</span>
                    </div>

                    <h6 class="mt-4">Fatura Oluşturma</h6>
                    <ol>
                        <li>Menüden <strong>Faturalar</strong> → <strong>Yeni Fatura</strong></li>
                        <li>Firma seçin veya yeni firma adı yazın</li>
                        <li>Fatura kalemlerini ekleyin (birden fazla olabilir)</li>
                        <li>Toplam tutar otomatik hesaplanır</li>
                        <li><strong>Kaydet</strong> ile tamamlayın</li>
                    </ol>

                    <h6 class="mt-4">Fatura Kopyalama</h6>
                    <p>Bir faturayı temel alarak yeni fatura oluşturmak için:</p>
                    <ol>
                        <li>Fatura detayına gidin</li>
                        <li><strong>Kopyala</strong> butonuna tıklayın</li>
                        <li>Tarihler güncel aya ayarlanır</li>
                        <li>Gerekli değişiklikleri yapıp kaydedin</li>
                    </ol>

                    <h6 class="mt-4">Toplu İşlemler</h6>
                    <p>Fatura listesinde birden fazla fatura seçerek toplu silme yapabilirsiniz.</p>
                    <div class="alert alert-danger">
                        <i class="bi bi-shield-exclamation me-2"></i>
                        Ödenmiş veya kısmi ödenmiş faturalar toplu silinemez.
                    </div>
                </div>
            </section>

            {{-- Tahsilatlar --}}
            <section id="tahsilatlar" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title text-primary"><i class="bi bi-cash-coin me-2"></i>Tahsilatlar</h4>
                    
                    <h6 class="mt-4">Tahsilat Kaydetme</h6>
                    <p>İki yöntemle tahsilat kaydedebilirsiniz:</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="bi bi-lightning text-warning me-2"></i>Hızlı Tahsilat</h6>
                                    <p class="small mb-0">Fatura detayından <strong>Hızlı Tahsilat</strong> butonunu kullanın. Kalan tutar otomatik doldurulur.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="bi bi-plus-circle text-primary me-2"></i>Manuel Tahsilat</h6>
                                    <p class="small mb-0"><strong>Tahsilatlar</strong> → <strong>Yeni Tahsilat</strong> menüsünden détaylı kayıt yapın.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mt-4">Ödeme Yöntemleri</h6>
                    <p>Ayarlardan özel ödeme yöntemleri tanımlayabilirsiniz (Nakit, Havale, Kredi Kartı vb.)</p>
                </div>
            </section>

            {{-- Beyannameler --}}
            <section id="beyannameler" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title text-primary"><i class="bi bi-file-earmark-text me-2"></i>Beyannameler</h4>
                    
                    <p>Beyanname sistemi, vergi beyannamelerin son tarihlerini takip etmenizi sağlar.</p>

                    <h6 class="mt-4">Beyanname Formları</h6>
                    <p><strong>Ayarlar</strong> → <strong>Beyanname Yönetimi</strong>'nden tanımlayabilirsiniz:</p>
                    <ul>
                        <li>KDV Beyannamesi (Aylık)</li>
                        <li>Muhtasar Beyanname (Aylık/3 Aylık)</li>
                        <li>Geçici Vergi (3 Aylık)</li>
                        <li>Kurumlar Vergisi (Yıllık)</li>
                    </ul>

                    <h6 class="mt-4">Durum Güncelleme</h6>
                    <p>Beyanname listesinden durum değişikliği yapabilirsiniz:</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-secondary">Bekliyor</span>
                        <span class="badge bg-info">Hazırlanıyor</span>
                        <span class="badge bg-success">Verildi</span>
                    </div>
                </div>
            </section>

            {{-- Raporlar --}}
            <section id="raporlar" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title text-primary"><i class="bi bi-bar-chart me-2"></i>Raporlar</h4>
                    
                    <h6 class="mt-4">Mevcut Raporlar</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Rapor</th>
                                    <th>Açıklama</th>
                                    <th>Export</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Bakiye Raporu</strong></td>
                                    <td>Firma bazında borç/alacak durumu</td>
                                    <td><span class="badge bg-success">CSV</span> <span class="badge bg-danger">PDF</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Tahsilat Raporu</strong></td>
                                    <td>Aylık tahsilat toplamları</td>
                                    <td><span class="badge bg-success">CSV</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Fatura Raporu</strong></td>
                                    <td>Yıllık fatura dağılımı ve durumları</td>
                                    <td><span class="badge bg-success">CSV</span> <span class="badge bg-danger">PDF</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Gecikmiş Ödemeler</strong></td>
                                    <td>Vadesi geçmiş faturalar</td>
                                    <td><span class="badge bg-success">CSV</span> <span class="badge bg-danger">PDF</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- Ayarlar --}}
            <section id="ayarlar" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title text-primary"><i class="bi bi-gear me-2"></i>Ayarlar</h4>
                    
                    <h6 class="mt-4">Genel Ayarlar</h6>
                    <ul>
                        <li>Şirket bilgileri (Logo, adres, iletişim)</li>
                        <li>Fatura varsayılan açıklaması ve vade günü</li>
                        <li>Tema modu (Açık/Koyu/Otomatik)</li>
                        <li>Ödeme yöntemleri</li>
                    </ul>

                    <h6 class="mt-4">Bildirim Ayarları</h6>
                    <p><strong>Ayarlar</strong> → <strong>Bildirim Ayarları</strong> butonundan:</p>
                    <ul>
                        <li>E-posta bildirimleri açma/kapatma</li>
                        <li>Ödeme hatırlatma zamanlaması</li>
                        <li>Beyanname hatırlatma günleri</li>
                        <li>Test e-postası gönderme</li>
                    </ul>

                    <h6 class="mt-4">Yedekleme</h6>
                    <p class="mb-0">Tüm verilerinizi JSON formatında yedekleyebilir ve geri yükleyebilirsiniz. Şifreli yedekleme desteklenir.</p>
                </div>
            </section>

            {{-- Kısayollar --}}
            <section id="kisayollar" class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title text-primary"><i class="bi bi-keyboard me-2"></i>Klavye Kısayolları</h4>
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kısayol</th>
                                    <th>İşlev</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><kbd>Ctrl</kbd> + <kbd>K</kbd></td>
                                    <td>Global arama kutusunu aç</td>
                                </tr>
                                <tr>
                                    <td><kbd>Esc</kbd></td>
                                    <td>Arama sonuçlarını kapat</td>
                                </tr>
                                <tr>
                                    <td><kbd>Enter</kbd></td>
                                    <td>Form gönder / Seçimi onayla</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- Destek --}}
            <section class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center py-4">
                    <i class="bi bi-life-preserver fs-1 mb-3"></i>
                    <h5>Yardıma mı ihtiyacınız var?</h5>
                    <p class="mb-3">Sorunlarınız için sistem yöneticinizle iletişime geçebilirsiniz.</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-light">
                        <i class="bi bi-house me-1"></i>Dashboard'a Dön
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
    #guideNav .list-group-item.active {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: white;
    }
    
    section {
        scroll-margin-top: 1rem;
    }
    
    kbd {
        background-color: #212529;
        border-radius: 3px;
        padding: 2px 6px;
        font-size: 0.85em;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll spy for navigation
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('#guideNav a');
    
    window.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            if (pageYOffset >= sectionTop) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });

    // Smooth scroll
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            target.scrollIntoView({ behavior: 'smooth' });
        });
    });
});
</script>
@endsection
