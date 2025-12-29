<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Sistem Güncellemeleri</h5>
        <span class="badge bg-primary">{{ $versionInfo['formatted'] }}</span>
    </div>
    <div class="card-body">
        {{-- Versiyon Bilgisi --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-3"><i class="bi bi-info-circle"></i> Versiyon Bilgileri</h6>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Mevcut Versiyon:</td>
                            <td class="fw-bold">{{ $versionInfo['formatted'] }}</td>
                        </tr>
                        @if($versionInfo['last_update'])
                        <tr>
                            <td class="text-muted">Son Güncelleme:</td>
                            <td>{{ $versionInfo['last_update'] }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-3"><i class="bi bi-gear"></i> Bakım İşlemleri</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <form action="{{ route('settings.updates.migration') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm" 
                                    onclick="return confirm('Migration çalıştırılsın mı?')">
                                <i class="bi bi-database-gear"></i> Migration
                            </button>
                        </form>
                        <form action="{{ route('settings.updates.clear-cache') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-trash"></i> Önbellek Temizle
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Yedekleme İşlemleri --}}
        <h6 class="mb-3"><i class="bi bi-cloud-download"></i> Yedekleme</h6>
        <div class="d-flex gap-2 mb-4">
            <form action="{{ route('settings.updates.backup-database') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-database-add"></i> Veritabanı Yedekle
                </button>
            </form>
            <form action="{{ route('settings.updates.backup-files') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-info text-white">
                    <i class="bi bi-file-zip"></i> Dosyaları Yedekle
                </button>
            </form>
        </div>

        {{-- Yedekleme Listesi --}}
        @if(count($backups) > 0)
        <h6 class="mb-3"><i class="bi bi-archive"></i> Yedeklemeler</h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Dosya</th>
                        <th>Tip</th>
                        <th>Boyut</th>
                        <th>Tarih</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $backup)
                    <tr>
                        <td>
                            <i class="bi {{ $backup['type'] === 'database' ? 'bi-database' : 'bi-file-zip' }}"></i>
                            {{ $backup['name'] }}
                        </td>
                        <td>
                            <span class="badge {{ $backup['type'] === 'database' ? 'bg-primary' : 'bg-info' }}">
                                {{ $backup['type'] === 'database' ? 'Veritabanı' : 'Dosya' }}
                            </span>
                        </td>
                        <td>{{ $backup['size'] }}</td>
                        <td>{{ $backup['date'] }}</td>
                        <td class="text-end">
                            <a href="{{ route('settings.updates.download-backup', $backup['name']) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i>
                            </a>
                            <form action="{{ route('settings.updates.delete-backup', $backup['name']) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Bu yedeklemeyi silmek istediğinizden emin misiniz?')">
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
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Henüz yedekleme oluşturulmamış.
        </div>
        @endif

        {{-- Notlar --}}
        <div class="alert alert-warning mt-4 mb-0">
            <h6><i class="bi bi-exclamation-triangle"></i> Önemli Notlar</h6>
            <ul class="mb-0 small">
                <li>Güncelleme yapmadan önce mutlaka veritabanı ve dosya yedeklemesi alın.</li>
                <li>Yedeklemeleri güvenli bir yerde saklayın.</li>
                <li>Migration işlemi veritabanı yapısını güncelleyecektir.</li>
            </ul>
        </div>
    </div>
</div>
