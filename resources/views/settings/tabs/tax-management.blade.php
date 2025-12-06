<div class="row g-4">
    {{-- Declaration Types Info --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h6 class="mb-0">Beyanname Türleri</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Sistemde desteklenen beyanname türleri:
                </p>
                <div class="list-group list-group-flush">
                    @foreach(\App\Enums\DeclarationType::cases() as $type)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $type->label() }}</h6>
                                <small class="text-muted">{{ $type->description() }}</small>
                            </div>
                            <span class="badge bg-secondary">{{ $type->value }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Tax Forms Management --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Vergi Formları</h6>
                <a href="{{ route('settings.tax-forms.create') }}" class="btn btn-sm btn-primary">
                    Yeni Form Ekle
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Sistemdeki vergi formlarını yönetin.
                </p>
                <a href="{{ route('settings.tax-forms.index') }}" class="btn btn-outline-primary w-100">
                    Tüm Formları Yönet
                </a>
            </div>
        </div>
    </div>
</div>
