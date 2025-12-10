@php
    use App\Support\Format;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="firm_id">Firma</label>
        <select class="form-select @error('firm_id') is-invalid @enderror" id="firm_id" name="firm_id" required autofocus>
            <option value="">Firma seçin</option>
            @foreach ($firms as $firm)
                <option value="{{ $firm->id }}"
                    @selected(old('firm_id', $invoice->firm_id ?? $prefillFirmId ?? null) == $firm->id)>
                    {{ $firm->name }} ({{ Format::money($firm->monthly_fee) }})
                </option>
            @endforeach
        </select>
        @error('firm_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="date">Fatura Tarihi</label>
        <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date"
               value="{{ old('date', optional($invoice->date)->format('Y-m-d')) }}" required>
        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label" for="due_date">Vade Tarihi</label>
        <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date"
               value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}">
        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    
    <div class="col-md-6">
        <label class="form-label" for="official_number">Resmi Fatura No</label>
        <input type="text" class="form-control @error('official_number') is-invalid @enderror"
               id="official_number" name="official_number" maxlength="50"
               value="{{ old('official_number', $invoice->official_number) }}">
        @error('official_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Opsiyonel. Örn: 2024-00125</div>
    </div>
    
    <div class="col-md-6">
        <label class="form-label" for="description">Genel Açıklama</label>
        <input type="text" class="form-control @error('description') is-invalid @enderror"
               id="description" name="description" value="{{ old('description', $invoice->description) }}"
               placeholder="Opsiyonel - Fatura üst açıklaması">
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Toplam Tutar (Gizli - Line Items'dan hesaplanacak) --}}
    <input type="hidden" id="amount" name="amount" value="{{ old('amount', $invoice->amount ?? 0) }}">
    <input type="hidden" id="subtotal" name="subtotal" value="{{ old('subtotal', $invoice->subtotal ?? 0) }}">
    <input type="hidden" id="vat_amount" name="vat_amount" value="{{ old('vat_amount', $invoice->vat_amount ?? 0) }}">

    {{-- KDV Ayarları --}}
    <div class="col-12 mt-3">
        <div class="card border bg-light">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="vat_rate">
                            <i class="bi bi-percent me-1"></i>KDV Oranı
                        </label>
                        <select class="form-select @error('vat_rate') is-invalid @enderror" 
                                id="vat_rate" name="vat_rate">
                            @php
                                $currentVat = old('vat_rate', $invoice->vat_rate ?? $selectedFirm->default_vat_rate ?? 20);
                            @endphp
                            <option value="0" @selected((float)$currentVat == 0)>%0 (KDV Yok)</option>
                            <option value="1" @selected((float)$currentVat == 1)>%1</option>
                            <option value="10" @selected((float)$currentVat == 10)>%10</option>
                            <option value="20" @selected((float)$currentVat == 20)>%20</option>
                        </select>
                        @error('vat_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">KDV Tipi</label>
                        <div class="d-flex gap-3 pt-1">
                            @php
                                $vatIncluded = old('vat_included', $invoice->vat_included ?? $selectedFirm->default_vat_included ?? true);
                            @endphp
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="vat_included" 
                                       id="vat_included_yes" value="1" @checked($vatIncluded)>
                                <label class="form-check-label" for="vat_included_yes">KDV Dahil</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="vat_included" 
                                       id="vat_included_no" value="0" @checked(!$vatIncluded)>
                                <label class="form-check-label" for="vat_included_no">KDV Hariç</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end gap-4 text-end">
                            <div>
                                <small class="text-muted d-block">KDV Hariç</small>
                                <span class="fs-5" id="subtotalDisplay">₺ 0,00</span>
                            </div>
                            <div>
                                <small class="text-muted d-block">KDV Tutarı</small>
                                <span class="fs-5 text-success" id="vatAmountDisplay">₺ 0,00</span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Toplam</small>
                                <span class="fs-4 fw-bold text-primary" id="totalWithVatDisplay">₺ 0,00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fatura Satır Kalemleri --}}
    @include('invoices._line_items')
    @if(isset($extraFields) && $extraFields->isNotEmpty())
        <div class="col-12">
            <hr class="my-4">
            <h6 class="mb-3">Ek Bilgiler</h6>
        </div>
        @foreach($extraFields as $field)
            <div class="col-md-6">
                <label class="form-label" for="extra_field_{{ $field->id }}">
                    {{ $field->label }}
                    @if($field->is_required)
                        <span class="text-danger">*</span>
                    @endif
                </label>
                
                @if($field->field_type === 'text')
                    <input type="text" 
                           class="form-control @error('extra_fields.'.$field->id) is-invalid @enderror"
                           id="extra_field_{{ $field->id }}" 
                           name="extra_fields[{{ $field->id }}]"
                           value="{{ old('extra_fields.'.$field->id, $invoice->extraValues->where('field_id', $field->id)->first()?->value) }}"
                           @if($field->is_required) required @endif>
                
                @elseif($field->field_type === 'number')
                    <input type="number" 
                           step="0.01"
                           class="form-control @error('extra_fields.'.$field->id) is-invalid @enderror"
                           id="extra_field_{{ $field->id }}" 
                           name="extra_fields[{{ $field->id }}]"
                           value="{{ old('extra_fields.'.$field->id, $invoice->extraValues->where('field_id', $field->id)->first()?->value) }}"
                           @if($field->is_required) required @endif>
                
                @elseif($field->field_type === 'date')
                    <input type="date" 
                           class="form-control @error('extra_fields.'.$field->id) is-invalid @enderror"
                           id="extra_field_{{ $field->id }}" 
                           name="extra_fields[{{ $field->id }}]"
                           value="{{ old('extra_fields.'.$field->id, $invoice->extraValues->where('field_id', $field->id)->first()?->value) }}"
                           @if($field->is_required) required @endif>
                
                @elseif($field->field_type === 'select' && $field->options)
                    <select class="form-select @error('extra_fields.'.$field->id) is-invalid @enderror"
                            id="extra_field_{{ $field->id }}" 
                            name="extra_fields[{{ $field->id }}]"
                            @if($field->is_required) required @endif>
                        <option value="">Seçiniz</option>
                        @foreach($field->options as $option)
                            <option value="{{ $option }}" 
                                    @selected(old('extra_fields.'.$field->id, $invoice->extraValues->where('field_id', $field->id)->first()?->value) == $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                @endif
                
                @error('extra_fields.'.$field->id)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                
                @if($field->help_text)
                    <div class="form-text">{{ $field->help_text }}</div>
                @endif
            </div>
        @endforeach
    @endif
</div>
