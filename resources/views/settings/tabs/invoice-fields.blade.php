<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Fatura Ekstra Alanları</h6>
        <a href="{{ route('settings.invoice-extra-fields.create') }}" class="btn btn-sm btn-primary">
            Yeni Alan Ekle
        </a>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Faturalara özel alanlar ekleyerek ek bilgiler toplayabilirsiniz.
            <a href="{{ route('settings.invoice-extra-fields.index') }}">Tüm alanları yönet →</a>
        </p>
    </div>
</div>
