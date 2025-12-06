/**
 * Feature Tour - Yeni kullanıcılar için interaktif özellik turu
 */
class FeatureTour {
    constructor(options = {}) {
        this.options = {
            storageKey: 'castbook_tour_completed',
            overlayClass: 'tour-overlay',
            spotlightClass: 'tour-spotlight',
            tooltipClass: 'tour-tooltip',
            ...options
        };

        this.currentStep = 0;
        this.steps = [];
        this.overlay = null;
        this.tooltip = null;
        this.isActive = false;
    }

    /**
     * Tour adımlarını tanımla
     */
    setSteps(steps) {
        this.steps = steps;
        return this;
    }

    /**
     * Tour'u başlat
     */
    start(forceStart = false) {
        // Daha önce tamamlandıysa başlatma
        if (!forceStart && localStorage.getItem(this.options.storageKey)) {
            return;
        }

        if (this.steps.length === 0) {
            console.warn('FeatureTour: No steps defined');
            return;
        }

        this.isActive = true;
        this.currentStep = 0;
        this.createOverlay();
        this.showStep(0);
    }

    /**
     * Overlay oluştur
     */
    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = this.options.overlayClass;
        this.overlay.innerHTML = `
            <div class="tour-backdrop"></div>
            <div class="tour-spotlight-container"></div>
        `;
        document.body.appendChild(this.overlay);

        // ESC tuşu ile kapat
        document.addEventListener('keydown', this.handleKeydown.bind(this));
    }

    /**
     * Belirli adımı göster
     */
    showStep(index) {
        if (index < 0 || index >= this.steps.length) {
            this.end();
            return;
        }

        const step = this.steps[index];
        const element = document.querySelector(step.element);

        if (!element) {
            console.warn(`FeatureTour: Element not found: ${step.element}`);
            this.next();
            return;
        }

        // Önceki tooltip'i kaldır
        this.removeTooltip();

        // Elementi spotlight'a al
        this.spotlightElement(element);

        // Tooltip oluştur
        this.createTooltip(step, element, index);

        // Sayfayı elemana kaydır
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    /**
     * Elementi spotlight'a al
     */
    spotlightElement(element) {
        const rect = element.getBoundingClientRect();
        const padding = 8;

        const spotlight = this.overlay.querySelector('.tour-spotlight-container');
        spotlight.innerHTML = `
            <div class="${this.options.spotlightClass}" style="
                top: ${rect.top + window.scrollY - padding}px;
                left: ${rect.left - padding}px;
                width: ${rect.width + padding * 2}px;
                height: ${rect.height + padding * 2}px;
            "></div>
        `;
    }

    /**
     * Tooltip oluştur
     */
    createTooltip(step, element, index) {
        const rect = element.getBoundingClientRect();
        const isLastStep = index === this.steps.length - 1;

        this.tooltip = document.createElement('div');
        this.tooltip.className = this.options.tooltipClass;
        this.tooltip.innerHTML = `
            <div class="tour-tooltip-arrow"></div>
            <div class="tour-tooltip-content">
                <div class="tour-tooltip-header">
                    <span class="tour-step-indicator">${index + 1} / ${this.steps.length}</span>
                    <button class="tour-close-btn" title="Turu Kapat">×</button>
                </div>
                <h6 class="tour-tooltip-title">${step.title}</h6>
                <p class="tour-tooltip-text">${step.content}</p>
                <div class="tour-tooltip-actions">
                    ${index > 0 ? '<button class="tour-btn tour-btn-prev">← Önceki</button>' : ''}
                    <button class="tour-btn tour-btn-next tour-btn-primary">
                        ${isLastStep ? 'Turu Bitir ✓' : 'Sonraki →'}
                    </button>
                </div>
            </div>
        `;

        // Pozisyonu hesapla
        const position = step.position || 'bottom';
        this.positionTooltip(rect, position);

        document.body.appendChild(this.tooltip);

        // Event listeners
        this.tooltip.querySelector('.tour-close-btn').addEventListener('click', () => this.end());
        this.tooltip.querySelector('.tour-btn-next').addEventListener('click', () => this.next());

        const prevBtn = this.tooltip.querySelector('.tour-btn-prev');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.prev());
        }
    }

    /**
     * Tooltip pozisyonunu ayarla
     */
    positionTooltip(rect, position) {
        const tooltipWidth = 320;
        const margin = 16;
        let top, left;

        switch (position) {
            case 'top':
                top = rect.top + window.scrollY - margin;
                left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                this.tooltip.classList.add('position-top');
                break;
            case 'left':
                top = rect.top + window.scrollY + (rect.height / 2);
                left = rect.left - tooltipWidth - margin;
                this.tooltip.classList.add('position-left');
                break;
            case 'right':
                top = rect.top + window.scrollY + (rect.height / 2);
                left = rect.right + margin;
                this.tooltip.classList.add('position-right');
                break;
            default: // bottom
                top = rect.bottom + window.scrollY + margin;
                left = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                this.tooltip.classList.add('position-bottom');
        }

        // Ekran sınırlarını kontrol et
        left = Math.max(margin, Math.min(left, window.innerWidth - tooltipWidth - margin));

        this.tooltip.style.top = `${top}px`;
        this.tooltip.style.left = `${left}px`;
    }

    /**
     * Sonraki adım
     */
    next() {
        this.currentStep++;
        this.showStep(this.currentStep);
    }

    /**
     * Önceki adım
     */
    prev() {
        this.currentStep--;
        this.showStep(this.currentStep);
    }

    /**
     * Turu bitir
     */
    end() {
        this.isActive = false;
        localStorage.setItem(this.options.storageKey, 'true');

        this.removeTooltip();

        if (this.overlay) {
            this.overlay.remove();
            this.overlay = null;
        }

        document.removeEventListener('keydown', this.handleKeydown.bind(this));

        // Callback
        if (this.options.onComplete) {
            this.options.onComplete();
        }
    }

    /**
     * Tooltip'i kaldır
     */
    removeTooltip() {
        if (this.tooltip) {
            this.tooltip.remove();
            this.tooltip = null;
        }
    }

    /**
     * Klavye olayları
     */
    handleKeydown(e) {
        if (!this.isActive) return;

        switch (e.key) {
            case 'Escape':
                this.end();
                break;
            case 'ArrowRight':
            case 'Enter':
                this.next();
                break;
            case 'ArrowLeft':
                this.prev();
                break;
        }
    }

    /**
     * Tour'u sıfırla (yeniden başlatmak için)
     */
    reset() {
        localStorage.removeItem(this.options.storageKey);
    }
}

// Global export
window.FeatureTour = FeatureTour;

// Dashboard için varsayılan tour
document.addEventListener('DOMContentLoaded', function () {
    // Sadece dashboard sayfasındaysa
    if (!window.location.pathname.includes('/dashboard')) return;

    const tour = new FeatureTour({
        onComplete: () => {
            console.log('Tour tamamlandı!');
        }
    });

    tour.setSteps([
        {
            element: '.app-navbar .navbar-brand',
            title: 'CastBook\'a Hoş Geldiniz! 🎉',
            content: 'Bu kısa tur ile uygulamanın temel özelliklerini keşfedin.',
            position: 'bottom'
        },
        {
            element: '[href*="firms"]',
            title: 'Firma Yönetimi',
            content: 'Müşteri firmalarınızı buradan yönetebilirsiniz. Yeni firma ekleyin, düzenleyin veya cari hesap hareketlerini görüntüleyin.',
            position: 'bottom'
        },
        {
            element: '[href*="invoices"]',
            title: 'Fatura Takibi',
            content: 'Tüm faturalarınızı buradan görüntüleyin. Toplu fatura oluşturma, durum güncelleme ve PDF export özellikleri mevcuttur.',
            position: 'bottom'
        },
        {
            element: '[href*="payments"]',
            title: 'Tahsilat Yönetimi',
            content: 'Müşterilerinizden gelen ödemeleri kaydedin. Faturalarla eşleştirme otomatik yapılır.',
            position: 'bottom'
        },
        {
            element: '[href*="reports"]',
            title: 'Raporlar',
            content: 'Detaylı bakiye raporları, tahsilat analizleri ve fatura raporlarına buradan erişebilirsiniz.',
            position: 'bottom'
        },
        {
            element: '#globalSearchInput',
            title: 'Hızlı Arama',
            content: 'Firma, fatura veya tahsilat aramak için bu kutuyu kullanın. Ctrl+K kısayolunu da kullanabilirsiniz.',
            position: 'bottom'
        },
        {
            element: '[href*="settings"]',
            title: 'Ayarlar',
            content: 'Şirket bilgilerinizi, e-posta ayarlarını, tema tercihlerinizi ve daha fazlasını buradan yönetin.',
            position: 'bottom'
        }
    ]);

    // İlk ziyarette turu başlat
    setTimeout(() => tour.start(), 1000);

    // Manuel başlatma için global erişim
    window.startTour = () => {
        tour.reset();
        tour.start(true);
    };
});
