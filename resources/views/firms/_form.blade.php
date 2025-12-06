@php
    $statuses = ['active' => 'Aktif', 'inactive' => 'Pasif'];
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Firma Ünvanı</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="name" name="name" value="{{ old('name', $firm->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="company_type">Firma Türü</label>
        <select class="form-select @error('company_type') is-invalid @enderror" id="company_type" name="company_type" required>
            @foreach (\App\Enums\CompanyType::cases() as $type)
                <option value="{{ $type->value }}" 
                        @selected(old('company_type', $firm->company_type?->value ?? 'individual') === $type->value)
                        data-description="{{ $type->description() }}">
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
        @error('company_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text" id="company_type_help">
            <small class="text-muted">Vergi beyannameleri firma türüne göre otomatik atanacaktır.</small>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="tax_no">Vergi No</label>
        <input type="text" class="form-control @error('tax_no') is-invalid @enderror"
               id="tax_no" name="tax_no" value="{{ old('tax_no', $firm->tax_no) }}">
        @error('tax_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="contact_person">Yetkili Kişi</label>
        <input type="text" class="form-control @error('contact_person') is-invalid @enderror"
               id="contact_person" name="contact_person" value="{{ old('contact_person', $firm->contact_person) }}">
        @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="contact_email">E-Posta</label>
        <input type="email" class="form-control @error('contact_email') is-invalid @enderror"
               id="contact_email" name="contact_email" value="{{ old('contact_email', $firm->contact_email) }}">
        @error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="contact_phone">Telefon</label>
        <input type="text" class="form-control @error('contact_phone') is-invalid @enderror"
               id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $firm->contact_phone) }}">
        @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="monthly_fee">Aylık Ücret (₺)</label>
        <input type="number" step="0.01" class="form-control @error('monthly_fee') is-invalid @enderror"
               id="monthly_fee" name="monthly_fee" value="{{ old('monthly_fee', $firm->monthly_fee ?? 0) }}" required>
        @error('monthly_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="contract_start_at">Sözleşme Başlangıç Tarihi</label>
        <input type="date" class="form-control @error('contract_start_at') is-invalid @enderror"
               id="contract_start_at" name="contract_start_at"
               value="{{ old('contract_start_at', optional($firm->contract_start_at)->format('Y-m-d')) }}" required>
        @error('contract_start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Geçmiş dönem faturalarının oluşması için kullanılır.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="status">Durum</label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $firm->status ?? 'active') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="notes">Notlar</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes"
                  name="notes" rows="3">{{ old('notes', $firm->notes) }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>