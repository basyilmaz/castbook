@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-1">Aktivite Günlüğü</h4>
            <p class="text-muted mb-0">Sistemdeki tüm önemli işlemlerin kaydı</p>
        </div>
    </div>

    {{-- Filtreler --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted text-uppercase">Kullanıcı</label>
                    <select name="user_id" class="form-select">
                        <option value="">Tümü</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted text-uppercase">İşlem</label>
                    <select name="action" class="form-select">
                        <option value="">Tümü</option>
                        @foreach(['login' => 'Giriş', 'logout' => 'Çıkış', 'create' => 'Oluşturma', 'update' => 'Güncelleme', 'delete' => 'Silme', 'export' => 'Dışa Aktarma'] as $key => $label)
                            <option value="{{ $key }}" @selected(request('action') == $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted text-uppercase">Başlangıç</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted text-uppercase">Bitiş</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filtrele
                    </button>
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary">Temizle</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Log Listesi --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 160px;">Tarih</th>
                            <th style="width: 140px;">Kullanıcı</th>
                            <th style="width: 100px;">İşlem</th>
                            <th>Açıklama</th>
                            <th style="width: 120px;">IP Adresi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <div>{{ $log->created_at->format('d.m.Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $log->user_name ?? 'Sistem' }}</div>
                                    @if($log->user)
                                        <small class="text-muted">{{ $log->user->email }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->action_color }}">
                                        <i class="bi {{ $log->action_icon }} me-1"></i>{{ $log->action_label }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $log->description }}</div>
                                    @if($log->old_values || $log->new_values)
                                        <button class="btn btn-sm btn-link p-0 text-muted" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#details-{{ $log->id }}">
                                            <small><i class="bi bi-chevron-down"></i> Detaylar</small>
                                        </button>
                                        <div class="collapse mt-2" id="details-{{ $log->id }}">
                                            @if($log->old_values)
                                                <div class="small bg-danger bg-opacity-10 p-2 rounded mb-1">
                                                    <strong>Önceki:</strong>
                                                    <code>{{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</code>
                                                </div>
                                            @endif
                                            @if($log->new_values)
                                                <div class="small bg-success bg-opacity-10 p-2 rounded">
                                                    <strong>Sonraki:</strong>
                                                    <code>{{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</code>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $log->ip_address }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                    Kayıt bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
