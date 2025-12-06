@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h1 class="h4 mb-1">Fatura Ekstra Alanları</h1>
        <p class="text-muted mb-0">Firmalara özel fatura alanlarını yönetin.</p>
    </div>
    <div>
        <a href="{{ route('settings.invoice-extra-fields.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Yeni Alan
        </a>
    </div>
</div>

@if($errors->has('field'))
    <div class="alert alert-danger">{{ $errors->first('field') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-striped table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>Firma</th>
                    <th>Alan Adı</th>
                    <th>Etiket</th>
                    <th>Tip</th>
                    <th>Zorunlu</th>
                    <th>Durum</th>
                    <th>Sıra</th>
                    <th class="text-end">İşlem</th>
                </tr>
                </thead>
                <tbody>
                @forelse($fields as $field)
                    <tr>
                        <td class="fw-semibold">{{ $field->firm->name }}</td>
                        <td><code>{{ $field->name }}</code></td>
                        <td>{{ $field->label }}</td>
                        <td>
                            <span class="badge bg-info">
                                {{ ucfirst($field->type) }}
                            </span>
                        </td>
                        <td>
                            @if($field->is_required)
                                <span class="badge bg-warning">Zorunlu</span>
                            @else
                                <span class="badge bg-secondary">İsteğe Bağlı</span>
                            @endif
                        </td>
                        <td>
                            @if($field->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Pasif</span>
                            @endif
                        </td>
                        <td>{{ $field->sort_order }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('settings.invoice-extra-fields.edit', $field) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Düzenle
                                </a>
                                <form action="{{ route('settings.invoice-extra-fields.destroy', $field) }}" method="POST"
                                      onsubmit="return confirm('Bu alanı silmek istediğinize emin misiniz?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i> Sil
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Henüz ekstra alan oluşturulmadı.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($fields->hasPages())
        <div class="card-footer bg-white">
            {{ $fields->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
