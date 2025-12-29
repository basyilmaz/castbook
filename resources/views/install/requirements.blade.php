@extends('install.layout', ['step' => 1])

@section('content')
<h4 class="mb-4"><i class="bi bi-gear"></i> Sistem Gereksinimleri</h4>

<div class="mb-4">
    <h6 class="text-muted mb-3">PHP Uzantıları</h6>
    @foreach($requirements as $key => $requirement)
        <div class="requirement-item">
            <span>
                <strong>{{ $requirement['name'] }}</strong>
                @if(!$requirement['required'])
                    <span class="badge bg-secondary">Opsiyonel</span>
                @endif
            </span>
            <span>
                @if($requirement['status'])
                    <i class="bi bi-check-circle-fill status-ok"></i>
                    <span class="text-muted small">{{ $requirement['current'] }}</span>
                @else
                    <i class="bi bi-x-circle-fill status-fail"></i>
                    <span class="text-danger small">{{ $requirement['current'] }}</span>
                @endif
            </span>
        </div>
    @endforeach
</div>

<div class="mb-4">
    <h6 class="text-muted mb-3">Klasör İzinleri</h6>
    @foreach($permissions as $key => $permission)
        <div class="requirement-item">
            <span><code>{{ $permission['path'] }}</code></span>
            <span>
                @if($permission['writable'])
                    <i class="bi bi-check-circle-fill status-ok"></i>
                    <span class="text-success small">Yazılabilir</span>
                @else
                    <i class="bi bi-x-circle-fill status-fail"></i>
                    <span class="text-danger small">Yazılamaz</span>
                @endif
            </span>
        </div>
    @endforeach
</div>

<div class="d-grid gap-2">
    @if($allMet)
        <a href="{{ route('install.database') }}" class="btn btn-primary">
            Devam Et <i class="bi bi-arrow-right"></i>
        </a>
    @else
        <button class="btn btn-secondary" disabled>
            <i class="bi bi-exclamation-triangle"></i> Lütfen gereksinimleri karşılayın
        </button>
        <a href="{{ route('install.requirements') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-clockwise"></i> Tekrar Kontrol Et
        </a>
    @endif
</div>
@endsection
