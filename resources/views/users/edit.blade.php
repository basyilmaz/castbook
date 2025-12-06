@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h4 mb-1">Kullanıcı Düzenle</h1>
                <p class="text-muted mb-0">{{ $user->name }} - {{ $user->email }}</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i>Listeye Dön
            </a>
        </div>
        <div class="card border-0 shadow-sm">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label">İsim</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="email" class="form-label">E-posta</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                               class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="role" class="form-label">Rol</label>
                        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Yönetici</option>
                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Kullanıcı</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Durum</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active"
                                   name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                        @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <hr>
                        <p class="text-muted small mb-2">Şifre değiştirmek için aşağıdaki alanları doldurun. Boş bırakırsanız mevcut şifre korunur.</p>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Yeni Şifre</label>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Şifre Tekrar</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control">
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-light">İptal</a>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
