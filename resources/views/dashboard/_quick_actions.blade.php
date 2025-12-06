{{-- Hızlı İşlemler Widget - Yatay ve Kompakt --}}
<div class="quick-actions-bar h-100">
    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
        <a href="{{ route('invoices.create') }}" class="btn btn-primary quick-action-btn">
            <i class="bi bi-plus-lg me-1"></i>Yeni Fatura
        </a>
        <a href="{{ route('payments.create') }}" class="btn btn-success quick-action-btn">
            <i class="bi bi-cash me-1"></i>Tahsilat Kaydet
        </a>
        <a href="{{ route('firms.create') }}" class="btn btn-info quick-action-btn">
            <i class="bi bi-building me-1"></i>Yeni Firma
        </a>
        <a href="{{ route('tax-declarations.index') }}" class="btn btn-warning quick-action-btn">
            <i class="bi bi-file-earmark-text me-1"></i>Beyannameler
        </a>
        <a href="{{ route('reports.balance') }}" class="btn btn-outline-secondary quick-action-btn">
            <i class="bi bi-bar-chart me-1"></i>Raporlar
        </a>
    </div>
</div>

<style>
.quick-actions-bar {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1rem;
    border-radius: 0.75rem;
    border: 1px solid #dee2e6;
}

.quick-action-btn {
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 0.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

@media (max-width: 576px) {
    .quick-action-btn {
        flex: 1 1 45%;
        justify-content: center;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }
    
    .quick-action-btn i {
        display: none;
    }
}
</style>
