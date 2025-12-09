@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-3">
        <div class="col">
            <h4 class="fw-semibold mb-0">Beyanname Düzenle</h4>
            <small class="text-muted">Beyanname durumunu güncelleyin.</small>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <dl class="row mb-4">
                <dt class="col-sm-3 text-muted">Firma</dt>
                <dd class="col-sm-9">{{ $declaration->firm?->name ?? 'Silinmiş Firma' }}</dd>

                <dt class="col-sm-3 text-muted">Beyanname</dt>
                <dd class="col-sm-9">{{ $declaration->taxForm?->code ?? '—' }} - {{ $declaration->taxForm?->name ?? 'Silinmiş Form' }}</dd>

                <dt class="col-sm-3 text-muted">Dönem</dt>
                <dd class="col-sm-9">{{ $declaration->period_label }}</dd>

                <dt class="col-sm-3 text-muted">Son Tarih</dt>
                <dd class="col-sm-9">{{ $declaration->due_date?->format('d.m.Y') ?? '-' }}</dd>
                
                @if($declaration->filed_at)
                <dt class="col-sm-3 text-muted">Verilme Tarihi</dt>
                <dd class="col-sm-9">{{ $declaration->filed_at->format('d.m.Y H:i') }}</dd>
                @endif
            </dl>

            <form action="{{ route('tax-declarations.update', $declaration) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label class="form-label">Durum</label>
                    <div class="d-flex gap-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="status_pending" 
                                   value="pending" @checked(old('status', $declaration->status) === 'pending')>
                            <label class="form-check-label" for="status_pending">
                                <span class="badge bg-warning text-dark">Bekliyor</span>
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="status_submitted" 
                                   value="submitted" @checked(old('status', $declaration->status) === 'submitted')>
                            <label class="form-check-label" for="status_submitted">
                                <span class="badge bg-success">Verildi</span>
                            </label>
                        </div>
                    </div>
                    @error('status')<div class="text-danger small">{{ $message }}</div>@enderror
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
