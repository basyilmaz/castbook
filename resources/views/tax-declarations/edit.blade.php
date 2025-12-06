@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-3">
        <div class="col">
            <h4 class="fw-semibold mb-0">Beyanname Düzenle</h4>
            <small class="text-muted">Beyanname durumunu ve tarihlerini güncelleyin.</small>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <dl class="row mb-4">
                <dt class="col-sm-3 text-muted">Firma</dt>
                <dd class="col-sm-9">{{ $declaration->firm?->name }}</dd>

                <dt class="col-sm-3 text-muted">Beyanname</dt>
                <dd class="col-sm-9">{{ $declaration->taxForm?->code }} - {{ $declaration->taxForm?->name }}</dd>

                <dt class="col-sm-3 text-muted">Dönem</dt>
                <dd class="col-sm-9">{{ $declaration->period_label }}</dd>

                <dt class="col-sm-3 text-muted">Son Tarih</dt>
                <dd class="col-sm-9">{{ $declaration->due_date?->format('Y-m-d') }}</dd>
            </dl>

            <form action="{{ route('tax-declarations.update', $declaration) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-4">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        @foreach (['pending' => 'Bekliyor', 'filed' => 'Dosyalandı', 'paid' => 'Ödendi', 'not_required' => 'Gerekli Değil'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $declaration->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Bildirim Tarihi</label>
                    <input type="date" name="filed_at" class="form-control @error('filed_at') is-invalid @enderror"
                           value="{{ old('filed_at', optional($declaration->filed_at)->format('Y-m-d')) }}">
                    @error('filed_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ödeme Tarihi</label>
                    <input type="date" name="paid_at" class="form-control @error('paid_at') is-invalid @enderror"
                           value="{{ old('paid_at', optional($declaration->paid_at)->format('Y-m-d')) }}">
                    @error('paid_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Notlar</label>
                    <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $declaration->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('tax-declarations.index') }}" class="btn btn-light">İptal</a>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
