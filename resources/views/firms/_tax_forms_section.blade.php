{{-- Vergi Formları Bölümü --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Vergi Beyannameleri</h6>
        <span class="badge bg-primary">{{ $firm->taxForms->count() }} Form</span>
    </div>
    <div class="card-body">
        {{-- Firma Türü Bilgisi --}}
        <div class="alert alert-info mb-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle me-2"></i>
                <div>
                    <strong>{{ $firm->company_type?->label() ?? 'Belirtilmemiş' }}</strong>
                    <br>
                    <small>{{ $firm->company_type?->description() ?? 'Firma türü belirtilmemiş' }}</small>
                </div>
            </div>
        </div>

        {{-- Atanmış Formlar Listesi --}}
        @if ($firm->taxForms->isEmpty())
            <div class="text-center py-4">
                <i class="bi bi-file-earmark-text text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mb-2">Henüz vergi formu atanmamış</p>
                <small class="text-muted">
                    Firma türüne göre otomatik form ataması yapılır.
                    <br>
                    Firmayı düzenleyerek firma türünü kontrol edin.
                </small>
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach ($firm->taxForms as $form)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2">
                                    <code class="bg-light px-2 py-1 rounded">{{ $form->code }}</code>
                                    <strong>{{ $form->name }}</strong>
                                </div>
                                @if ($form->description)
                                    <small class="text-muted d-block mt-1">{{ $form->description }}</small>
                                @endif
                                <div class="mt-2">
                                    <span class="badge bg-info">
                                        @if ($form->frequency === 'monthly')
                                            Aylık
                                        @elseif ($form->frequency === 'quarterly')
                                            3 Aylık
                                        @elseif ($form->frequency === 'annual')
                                            Yıllık
                                        @endif
                                    </span>
                                    <span class="badge bg-secondary">
                                        Vade: {{ $form->default_due_day }}. gün
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Bilgilendirme --}}
            <div class="mt-3 p-2 bg-light rounded">
                <small class="text-muted">
                    <i class="bi bi-lightbulb me-1"></i>
                    <strong>Not:</strong> Vergi formları firma türüne göre otomatik atanır. 
                    Firma türünü değiştirirseniz formlar otomatik güncellenir.
                </small>
            </div>
        @endif
    </div>
</div>
