@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h1 class="h4 mb-1">Kullanıcılar</h1>
        <p class="text-muted mb-0">Takım üyelerini yönetin, rollerini ve erişim durumlarını kontrol edin.</p>
    </div>
    <div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i>Yeni Kullanıcı
        </a>
    </div>
</div>

@if($errors->has('user'))
    <div class="alert alert-danger">{{ $errors->first('user') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-striped table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th>İsim</th>
                    <th>E-posta</th>
                    <th>Rol</th>
                    <th>Durum</th>
                    <th class="text-end">İşlem</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : 'secondary' }}">
                                {{ $user->role === 'admin' ? 'Yönetici' : 'Kullanıcı' }}
                            </span>
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Pasif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-pencil"></i> Düzenle
                                </a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST"
                                      onsubmit="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?');">
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
                        <td colspan="5" class="text-center text-muted py-4">
                            Henüz kullanıcı oluşturulmadı.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
