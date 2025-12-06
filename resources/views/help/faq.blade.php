@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Header --}}
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 mb-3" 
                     style="width: 80px; height: 80px;">
                    <i class="bi bi-question-circle text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h2 class="fw-bold">Sıkça Sorulan Sorular</h2>
                <p class="text-muted">CastBook hakkında en çok merak edilen sorular ve cevapları</p>
            </div>

            {{-- Search --}}
            <div class="mb-4">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control" id="faqSearch" placeholder="Sorularda ara...">
                </div>
            </div>

            {{-- Categories --}}
            <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center">
                <button class="btn btn-primary btn-sm faq-filter active" data-category="all">Tümü</button>
                <button class="btn btn-outline-primary btn-sm faq-filter" data-category="genel">Genel</button>
                <button class="btn btn-outline-primary btn-sm faq-filter" data-category="fatura">Faturalar</button>
                <button class="btn btn-outline-primary btn-sm faq-filter" data-category="tahsilat">Tahsilatlar</button>
                <button class="btn btn-outline-primary btn-sm faq-filter" data-category="rapor">Raporlar</button>
                <button class="btn btn-outline-primary btn-sm faq-filter" data-category="ayar">Ayarlar</button>
            </div>

            {{-- FAQ Accordion --}}
            <div class="accordion" id="faqAccordion">
                {{-- Genel Sorular --}}
                <div class="faq-item" data-category="genel">
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="bi bi-building me-2 text-primary"></i>
                                CastBook ne işe yarar?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                CastBook, muhasebe bürolarının müşteri takibini kolaylaştırmak için tasarlanmış bir sistemdir. 
                                Firmalarınızı, faturalarınızı, tahsilatlarınızı ve beyannamelerinizi tek bir yerden yönetebilirsiniz.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="genel">
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="bi bi-keyboard me-2 text-primary"></i>
                                Klavye kısayolları var mı?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Evet! <kbd>Ctrl</kbd> + <kbd>K</kbd> ile hızlı arama yapabilirsiniz. 
                                Bu kısayol sayesinde firmaları, faturaları ve tahsilatları anında bulabilirsiniz.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fatura Soruları --}}
                <div class="faq-item" data-category="fatura">
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="bi bi-receipt me-2 text-primary"></i>
                                Fatura nasıl oluştururum?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <ol>
                                    <li>Menüden <strong>Faturalar</strong>'a tıklayın</li>
                                    <li><strong>Yeni Fatura Oluştur</strong> butonuna basın</li>
                                    <li>Firma seçin ve fatura kalemlerini ekleyin</li>
                                    <li><strong>Kaydet</strong> ile işlemi tamamlayın</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="fatura">
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="bi bi-copy me-2 text-primary"></i>
                                Faturayı kopyalayabilir miyim?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Evet! Fatura detay sayfasında <strong>Kopyala</strong> butonuna tıklayarak 
                                mevcut faturayı temel alarak yeni bir fatura oluşturabilirsiniz. 
                                Tarihler otomatik olarak güncellenir.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="fatura">
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                <i class="bi bi-trash me-2 text-primary"></i>
                                Ödenmiş faturayı silebilir miyim?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <strong>Hayır.</strong> Veri bütünlüğünü korumak için ödenmiş veya kısmi ödenmiş faturalar silinemez. 
                                Önce ilgili tahsilatları silmeniz gerekir.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tahsilat Soruları --}}
                <div class="faq-item" data-category="tahsilat">
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                <i class="bi bi-cash-coin me-2 text-primary"></i>
                                Hızlı tahsilat nedir?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Fatura detay sayfasındaki <strong>Hızlı Tahsilat</strong> butonu, 
                                kalan tutarı otomatik olarak dolduran hızlı bir tahsilat kayıt yöntemidir. 
                                Tek tıkla ödeme kaydedebilirsiniz.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Rapor Soruları --}}
                <div class="faq-item" data-category="rapor">
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                <i class="bi bi-download me-2 text-primary"></i>
                                Raporları nasıl dışa aktarırım?
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Tüm rapor sayfalarında <strong>CSV</strong> ve <strong>PDF</strong> indirme butonları bulunur. 
                                Mevcut filtreleme seçenekleri korunarak raporlarınızı dışa aktarabilirsiniz.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ayar Soruları --}}
                <div class="faq-item" data-category="ayar">
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                <i class="bi bi-bell me-2 text-primary"></i>
                                E-posta bildirimlerini nasıl ayarlarım?
                            </button>
                        </h2>
                        <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <strong>Ayarlar</strong> → <strong>Bildirim Ayarları</strong> sayfasından 
                                ödeme hatırlatmaları, beyanname hatırlatmaları ve bildirim alıcılarını 
                                yapılandırabilirsiniz.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="ayar">
                    <div class="accordion-item border-0 shadow-sm mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                                <i class="bi bi-cloud-download me-2 text-primary"></i>
                                Verilerimi nasıl yedeklerim?
                            </button>
                        </h2>
                        <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <strong>Ayarlar</strong> sayfasında <strong>Yedekleme</strong> bölümünden 
                                tüm verilerinizi JSON formatında indirebilirsiniz. 
                                Şifreli yedekleme seçeneği de mevcuttur.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- No Results --}}
            <div class="text-center py-5 d-none" id="noResults">
                <i class="bi bi-search fs-1 text-muted mb-3"></i>
                <p class="text-muted">Aramanızla eşleşen soru bulunamadı.</p>
            </div>

            {{-- Back to Help --}}
            <div class="text-center mt-5">
                <p class="text-muted mb-3">Aradığınız cevabı bulamadınız mı?</p>
                <a href="{{ route('help') }}" class="btn btn-primary">
                    <i class="bi bi-book me-1"></i>Kullanıcı Kılavuzuna Git
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('faqSearch');
    const faqItems = document.querySelectorAll('.faq-item');
    const filterButtons = document.querySelectorAll('.faq-filter');
    const noResults = document.getElementById('noResults');
    
    let currentCategory = 'all';
    
    // Search functionality
    searchInput.addEventListener('input', filterFaqs);
    
    // Category filter
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary', 'active');
            
            currentCategory = this.dataset.category;
            filterFaqs();
        });
    });
    
    function filterFaqs() {
        const searchTerm = searchInput.value.toLowerCase();
        let visibleCount = 0;
        
        faqItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            const category = item.dataset.category;
            
            const matchesSearch = text.includes(searchTerm);
            const matchesCategory = currentCategory === 'all' || category === currentCategory;
            
            if (matchesSearch && matchesCategory) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        noResults.classList.toggle('d-none', visibleCount > 0);
    }
});
</script>
@endsection
