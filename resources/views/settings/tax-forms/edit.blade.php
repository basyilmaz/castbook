@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h4 mb-1">Vergi Formu Düzenle</h1>
                <p class="text-muted mb-0">{{ $taxForm->code }} - {{ $taxForm->name }}</p>
            </div>
            <a href="{{ route('settings.tax-forms.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i>Listeye Dön
            </a>
        </div>
        <div class="card border-0 shadow-sm">
            <form action="{{ route('settings.tax-forms.update', $taxForm) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label">Form Kodu</label>
                        <input type="text" id="code" name="code" value="{{ old('code', $taxForm->code) }}"
                               class="form-control @error('code') is-invalid @enderror" 
                               placeholder="KDV-1" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Örn: KDV-1, BA-BS, Muhtasar</div>
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label">Form Adı</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $taxForm->name) }}"
                               class="form-control @error('name') is-invalid @enderror" 
                               placeholder="KDV Beyannamesi" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea id="description" name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror" 
                                  placeholder="Form hakkında kısa açıklama">{{ old('description', $taxForm->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="frequency" class="form-label">Periyot</label>
                        <select id="frequency" name="frequency" class="form-select @error('frequency') is-invalid @enderror" required>
                            <option value="monthly" {{ old('frequency', $taxForm->frequency) === 'monthly' ? 'selected' : '' }}>Aylık</option>
                            <option value="quarterly" {{ old('frequency', $taxForm->frequency) === 'quarterly' ? 'selected' : '' }}>3 Aylık (Çeyrek)</option>
                            <option value="annual" {{ old('frequency', $taxForm->frequency) === 'annual' ? 'selected' : '' }}>Yıllık</option>
                        </select>
                        @error('frequency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="default_due_day" class="form-label">Varsayılan Vade Günü</label>
                        <input type="number" id="default_due_day" name="default_due_day" 
                               value="{{ old('default_due_day', $taxForm->default_due_day) }}"
                               class="form-control @error('default_due_day') is-invalid @enderror" 
                               min="1" max="31" required>
                        @error('default_due_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Ayın kaçıncı günü (1-31)</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label d-block">Durum</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active"
                                   name="is_active" value="1" {{ old('is_active', $taxForm->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                        @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    
                    @if($taxForm->firms()->count() > 0)
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Bu form <strong>{{ $taxForm->firms()->count() }} firmaya</strong> atanmış durumda.
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                    <a href="{{ route('settings.tax-forms.index') }}" class="btn btn-light">İptal</a>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
