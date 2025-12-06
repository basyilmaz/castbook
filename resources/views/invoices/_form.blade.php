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
