@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h1 class="h4 mb-1">Vergi Formları</h1>
        <p class="text-muted mb-0">Sistemdeki vergi formlarını yönetin.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('settings.edit') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Ayarlara Dön
        </a>
        <a href="{{ route('settings.tax-forms.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Yeni Form
        </a>
    </div>
</div>

@if($errors->has('tax_form'))
    <div class="alert alert-danger">{{ $errors->first('tax_form') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-striped table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>Kod</th>
                    <th>Form Adı</th>
                    <th>Açıklama</th>
                    <th>Periyot</th>
                    <th>Vade Günü</th>
                    <th>Durum</th>
                    <th>Firma Sayısı</th>
                    <th class="text-end">İşlem</th>
                </tr>
                </thead>
                <tbody>
                @forelse($taxForms as $form)
                    <tr>
                        <td class="fw-semibold"><code>{{ $form->code }}</code></td>
                        <td>{{ $form->name }}</td>
                        <td>
                            <small class="text-muted">
                                {{ Str::limit($form->description ?? '-', 50) }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                @if($form->frequency === 'monthly')
                                    Aylık
                                @elseif($form->frequency === 'quarterly')
                                    3 Aylık
                                @elseif($form->frequency === 'annual')
                                    Yıllık
                                @else
                                    {{ ucfirst($form->frequency) }}
                                @endif
                            </span>
                        </td>
                        <td>{{ $form->default_due_day }}. gün</td>
                        <td>
                            @if($form->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Pasif</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ $form->firms()->count() }} firma
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('settings.tax-forms.edit', $form) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Düzenle
                                </a>
                                <form action="{{ route('settings.tax-forms.destroy', $form) }}" method="POST"
                                      onsubmit="return confirm('Bu formu silmek istediğinize emin misiniz?');">
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
                            Henüz vergi formu oluşturulmadı.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($taxForms->hasPages())
        <div class="card-footer bg-white">
            {{ $taxForms->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
