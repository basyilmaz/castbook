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

{{-- Otomasyon Ayarları --}}
<div class="card mt-4 border-0 bg-light">
    <div class="card-header bg-transparent">
        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Otomasyon Ayarları</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input type="hidden" name="auto_invoice_enabled" value="0">
                    <input class="form-check-input" type="checkbox" id="auto_invoice_enabled" 
                           name="auto_invoice_enabled" value="1"
                           @checked(old('auto_invoice_enabled', $firm->auto_invoice_enabled ?? true))>
                    <label class="form-check-label" for="auto_invoice_enabled">
                        <strong>Aylık Otomatik Fatura Oluştur</strong>
                    </label>
                </div>
                <div class="form-text">Kapalıysa bu firmaya otomatik aylık fatura oluşturulmaz.</div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input type="hidden" name="tax_tracking_enabled" value="0">
                    <input class="form-check-input" type="checkbox" id="tax_tracking_enabled" 
                           name="tax_tracking_enabled" value="1"
                           @checked(old('tax_tracking_enabled', $firm->tax_tracking_enabled ?? true))>
                    <label class="form-check-label" for="tax_tracking_enabled">
                        <strong>Beyanname Takibi Yap</strong>
                    </label>
                </div>
                <div class="form-text">Kapalıysa bu firmaya yeni beyanname oluşturulmaz.</div>
            </div>
        </div>
    </div>
</div>

{{-- KDV Varsayılanları --}}
<div class="card mt-3 border-0 bg-light">
    <div class="card-header bg-transparent">
        <h6 class="mb-0"><i class="bi bi-percent me-2"></i>Varsayılan KDV Ayarları</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="default_vat_rate">Varsayılan KDV Oranı</label>
                <select class="form-select @error('default_vat_rate') is-invalid @enderror" 
                        id="default_vat_rate" name="default_vat_rate">
                    @php
                        $currentRate = old('default_vat_rate', $firm->default_vat_rate ?? 20);
                        $standardRates = [0, 1, 10, 20];
                    @endphp
                    @foreach($standardRates as $rate)
                        <option value="{{ $rate }}" @selected((float)$currentRate == $rate)>
                            %{{ $rate }}
                        </option>
                    @endforeach
                    @if(!in_array((float)$currentRate, $standardRates))
                        <option value="{{ $currentRate }}" selected>%{{ number_format($currentRate, 2) }} (Özel)</option>
                    @endif
                </select>
                @error('default_vat_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">KDV Tipi</label>
                <div class="mt-2">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="default_vat_included" 
                               id="vat_included_yes" value="1"
                               @checked(old('default_vat_included', $firm->default_vat_included ?? true))>
                        <label class="form-check-label" for="vat_included_yes">KDV Dahil</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="default_vat_included" 
                               id="vat_included_no" value="0"
                               @checked(!old('default_vat_included', $firm->default_vat_included ?? true))>
                        <label class="form-check-label" for="vat_included_no">KDV Hariç</label>
                    </div>
                </div>
                <div class="form-text">Fatura oluşturulurken varsayılan KDV tipi.</div>
            </div>
        </div>
    </div>
</div>