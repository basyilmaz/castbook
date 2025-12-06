/**
 * Swipe Actions - Mobil cihazlarda liste öğelerinde kaydırma işlemleri
 */
class SwipeActions {
    constructor(container, options = {}) {
        this.container = container;
        this.options = {
            threshold: 80,      // Minimum kaydırma mesafesi
            maxSwipe: 150,      // Maksimum kaydırma
            resistance: 0.5,    // Direnç faktörü
            ...options
        };

        this.items = container.querySelectorAll('.swipe-item');
        this.activeItem = null;
        this.startX = 0;
        this.currentX = 0;
        this.isDragging = false;

        this.init();
    }

    init() {
        this.items.forEach(item => {
            item.addEventListener('touchstart', (e) => this.onTouchStart(e, item), { passive: true });
            item.addEventListener('touchmove', (e) => this.onTouchMove(e, item), { passive: false });
            item.addEventListener('touchend', (e) => this.onTouchEnd(e, item));
        });

        // Dışarı tıklandığında kapat
        document.addEventListener('touchstart', (e) => {
            if (this.activeItem && !this.activeItem.contains(e.target)) {
                this.closeItem(this.activeItem);
            }
        });
    }

    onTouchStart(e, item) {
        if (this.activeItem && this.activeItem !== item) {
            this.closeItem(this.activeItem);
        }

        this.startX = e.touches[0].clientX;
        this.startY = e.touches[0].clientY;
        this.isDragging = false;

        item.style.transition = 'none';
    }

    onTouchMove(e, item) {
        const touch = e.touches[0];
        const deltaX = touch.clientX - this.startX;
        const deltaY = touch.clientY - this.startY;

        // Dikey kaydırma ise işleme
        if (Math.abs(deltaY) > Math.abs(deltaX) && !this.isDragging) {
            return;
        }

        this.isDragging = true;
        e.preventDefault();

        // Sadece sola kaydırma (negatif delta)
        if (deltaX < 0) {
            let translateX = deltaX * this.options.resistance;
            translateX = Math.max(translateX, -this.options.maxSwipe);

            item.style.transform = `translateX(${translateX}px)`;
            this.currentX = translateX;
        } else if (this.activeItem === item) {
            // Sağa kaydırarak kapatma
            let translateX = Math.min(0, deltaX * this.options.resistance);
            item.style.transform = `translateX(${translateX}px)`;
            this.currentX = translateX;
        }
    }

    onTouchEnd(e, item) {
        if (!this.isDragging) return;

        item.style.transition = 'transform 0.2s ease-out';

        if (Math.abs(this.currentX) > this.options.threshold) {
            // Açık bırak
            item.style.transform = `translateX(-${this.options.maxSwipe}px)`;
            this.activeItem = item;
        } else {
            // Kapat
            this.closeItem(item);
        }

        this.currentX = 0;
        this.isDragging = false;
    }

    closeItem(item) {
        if (!item) return;
        item.style.transition = 'transform 0.2s ease-out';
        item.style.transform = 'translateX(0)';
        if (this.activeItem === item) {
            this.activeItem = null;
        }
    }

    closeAll() {
        this.items.forEach(item => this.closeItem(item));
    }
}

// Global export
window.SwipeActions = SwipeActions;

// Auto-init on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    // Sadece dokunmatik cihazlarda init et
    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
        document.querySelectorAll('.swipe-container').forEach(container => {
            new SwipeActions(container);
        });
    }
});
