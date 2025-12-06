@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h4 mb-1">Ekstra Alan Düzenle</h1>
                <p class="text-muted mb-0">{{ $field->firm->name }} - {{ $field->label }}</p>
            </div>
            <a href="{{ route('settings.invoice-extra-fields.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i>Listeye Dön
            </a>
        </div>
        <div class="card border-0 shadow-sm">
            <form action="{{ route('settings.invoice-extra-fields.update', $field) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label for="firm_id" class="form-label">Firma</label>
                        <select id="firm_id" name="firm_id" class="form-select @error('firm_id') is-invalid @enderror" required>
                            <option value="">Firma Seçin</option>
                            @foreach($firms as $firm)
                                <option value="{{ $firm->id }}" {{ old('firm_id', $field->firm_id) == $firm->id ? 'selected' : '' }}>
                                    {{ $firm->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('firm_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label">Alan Adı</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $field->name) }}"
                               class="form-control @error('name') is-invalid @enderror" 
                               placeholder="ornek_alan" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Sadece küçük harf ve alt çizgi kullanın.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="label" class="form-label">Etiket</label>
                        <input type="text" id="label" name="label" value="{{ old('label', $field->label) }}"
                               class="form-control @error('label') is-invalid @enderror" 
                               placeholder="Örnek Alan" required>
                        @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="type" class="form-label">Tip</label>
                        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="text" {{ old('type', $field->type) === 'text' ? 'selected' : '' }}>Metin</option>
                            <option value="number" {{ old('type', $field->type) === 'number' ? 'selected' : '' }}>Sayı</option>
                            <option value="date" {{ old('type', $field->type) === 'date' ? 'selected' : '' }}>Tarih</option>
                            <option value="select" {{ old('type', $field->type) === 'select' ? 'selected' : '' }}>Seçim Listesi</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="sort_order" class="form-label">Sıra</label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $field->sort_order) }}"
                               class="form-control @error('sort_order') is-invalid @enderror" 
                               min="0" required>
                        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12" id="options-container" style="display: none;">
                        <label for="options" class="form-label">Seçenekler</label>
                        <input type="text" id="options" name="options" value="{{ old('options', $field->options) }}"
                               class="form-control @error('options') is-invalid @enderror" 
                               placeholder="Seçenek1, Seçenek2, Seçenek3">
                        @error('options')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Virgülle ayırarak seçenekleri girin.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Zorunlu</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_required" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_required"
                                   name="is_required" value="1" {{ old('is_required', $field->is_required) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_required">Bu alan zorunlu</label>
                        </div>
                        @error('is_required')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Durum</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active"
                                   name="is_active" value="1" {{ old('is_active', $field->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                        @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                    <a href="{{ route('settings.invoice-extra-fields.index') }}" class="btn btn-light">İptal</a>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const optionsContainer = document.getElementById('options-container');
    
    function toggleOptions() {
        if (typeSelect.value === 'select') {
            optionsContainer.style.display = 'block';
        } else {
            optionsContainer.style.display = 'none';
        }
    }
    
    typeSelect.addEventListener('change', toggleOptions);
    toggleOptions(); // Initial check
});
</script>
@endsection
