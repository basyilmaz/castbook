<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Sistem Güncellemeleri</h5>
        @if($versionInfo['update_available'] ?? false)
            <span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> Güncelleme Mevcut</span>
        @else
            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Güncel</span>
        @endif
    </div>
    <div class="card-body">
        {{-- Versiyon Bilgisi --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-3"><i class="bi bi-info-circle"></i> Versiyon Bilgisi</h6>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Mevcut Versiyon:</td>
                            <td class="fw-bold">{{ $versionInfo['formatted'] ?? 'v?.?.?' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Son Güncelleme:</td>
                            <td>{{ $versionInfo['last_update'] ?? '-' }}</td>
                        </tr>
                        @if($versionInfo['update_available'] ?? false)
                        <tr>
                            <td class="text-muted">Yeni Versiyon:</td>
                            <td class="text-success fw-bold">v{{ $versionInfo['latest_version'] }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-3"><i class="bi bi-cloud-download"></i> Güncelleme Kontrolü</h6>
                    <p class="mb-2 small text-muted">
                        {{ $versionInfo['update_available'] ?? false 
                            ? 'Yeni bir güncelleme mevcut!' 
                            : 'Sisteminiz güncel.' }}
                    </p>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="checkUpdateBtn">
                        <i class="bi bi-arrow-clockwise"></i> Güncelleme Kontrol Et
                    </button>
                    @if($versionInfo['update_available'] ?? false)
                    <form action="{{ route('settings.updates.apply') }}" method="POST" class="d-inline ms-2">
                        @csrf
                        <input type="hidden" name="download_url" value="{{ $versionInfo['download_url'] ?? '' }}">
                        <button type="submit" class="btn btn-success btn-sm" 
                                onclick="return confirm('Güncelleme başlatılacak. Devam etmek istiyor musunuz?')">
                            <i class="bi bi-download"></i> Güncelle
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Changelog --}}
        @if($versionInfo['changelog'] ?? false)
        <div class="alert alert-info mb-4">
            <h6 class="mb-2"><i class="bi bi-journal-text"></i> Değişiklik Notları</h6>
            <div class="small">{!! nl2br(e($versionInfo['changelog'])) !!}</div>
        </div>
        @endif

        {{-- Bakım İşlemleri --}}
        <h6 class="mb-3"><i class="bi bi-gear"></i> Bakım İşlemleri</h6>
        <div class="row mb-4">
            <div class="col-md-3 mb-2">
                <form action="{{ route('settings.updates.migration') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100" 
                            onclick="return confirm('Migration çalıştırılacak. Devam etmek istiyor musunuz?')">
                        <i class="bi bi-database-gear"></i> Migration Çalıştır
                    </button>
                </form>
            </div>
            <div class="col-md-3 mb-2">
                <form action="{{ route('settings.updates.clear-cache') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-info w-100">
                        <i class="bi bi-trash"></i> Önbellek Temizle
                    </button>
                </form>
            </div>
            <div class="col-md-3 mb-2">
                <form action="{{ route('settings.updates.create-rollback') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-save"></i> Rollback Noktası
                    </button>
                </form>
            </div>
            <div class="col-md-3 mb-2">
                <form action="{{ route('settings.updates.rollback') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100" 
                            onclick="return confirm('Son rollback noktasına geri dönülecek. Devam etmek istiyor musunuz?')"
                            {{ empty($rollbacks ?? []) ? 'disabled' : '' }}>
                        <i class="bi bi-arrow-counterclockwise"></i> Rollback
                    </button>
                </form>
            </div>
        </div>

        {{-- Yedekleme --}}
        <h6 class="mb-3"><i class="bi bi-shield-check"></i> Yedekleme</h6>
        <div class="row mb-4">
            <div class="col-md-6 mb-2">
                <form action="{{ route('settings.updates.backup-database') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-database"></i> Veritabanı Yedekle
                    </button>
                </form>
            </div>
            <div class="col-md-6 mb-2">
                <form action="{{ route('settings.updates.backup-files') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-file-zip"></i> Dosyaları Yedekle
                    </button>
                </form>
            </div>
        </div>

        {{-- Rollback Noktaları --}}
        @if(!empty($rollbacks ?? []))
        <h6 class="mb-3"><i class="bi bi-clock-history"></i> Rollback Noktaları</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Dosya</th>
                        <th>Boyut</th>
                        <th>Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rollbacks as $rollback)
                    <tr>
                        <td><i class="bi bi-arrow-counterclockwise text-warning"></i> {{ $rollback['name'] }}</td>
                        <td>{{ $rollback['size'] }}</td>
                        <td>{{ $rollback['date'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Yedekleme Listesi --}}
        @if(!empty($backups ?? []))
        <h6 class="mb-3"><i class="bi bi-archive"></i> Yedeklemeler</h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Dosya</th>
                        <th>Tip</th>
                        <th>Boyut</th>
                        <th>Tarih</th>
                        <th style="width: 140px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $backup)
                    <tr>
                        <td>
                            <i class="bi {{ $backup['type'] === 'database' ? 'bi-database text-primary' : 'bi-file-zip text-success' }}"></i>
                            {{ $backup['name'] }}
                        </td>
                        <td>{{ $backup['type'] === 'database' ? 'Veritabanı' : 'Dosya' }}</td>
                        <td>{{ $backup['size'] }}</td>
                        <td>{{ $backup['date'] }}</td>
                        <td>
                            <a href="{{ route('settings.updates.download-backup', $backup['name']) }}" 
                               class="btn btn-sm btn-outline-success" title="İndir">
                                <i class="bi bi-download"></i>
                            </a>
                            <form action="{{ route('settings.updates.delete-backup', $backup['name']) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Bu yedeği silmek istediğinizden emin misiniz?')"
                                        title="Sil">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-muted text-center py-3">
            <i class="bi bi-inbox fs-1"></i>
            <p class="mb-0 mt-2">Henüz yedekleme yok.</p>
        </div>
        @endif
    </div>
</div>

<script>
document.getElementById('checkUpdateBtn')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Kontrol ediliyor...';
    
    fetch('{{ route("settings.updates.check") }}')
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                alert('Yeni versiyon mevcut: v' + data.version + '\nSayfayı yenileyerek güncelleme yapabilirsiniz.');
                location.reload();
            } else {
                alert(data.message || 'Sisteminiz güncel.');
            }
        })
        .catch(error => {
            alert('Güncelleme kontrolü başarısız.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Güncelleme Kontrol Et';
        });
});
</script>
