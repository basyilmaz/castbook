<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-shield-check"></i> Lisans Bilgileri</h5>
        @if($licenseInfo['is_valid'])
            <span class="badge bg-{{ $licenseInfo['is_trial'] ? 'warning' : 'success' }}">
                {{ $licenseInfo['type_name'] }}
            </span>
        @else
            <span class="badge bg-danger">Geçersiz</span>
        @endif
    </div>
    <div class="card-body">
        {{-- Lisans Durumu --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-3"><i class="bi bi-info-circle"></i> Mevcut Lisans</h6>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Lisans Tipi:</td>
                            <td class="fw-bold">{{ $licenseInfo['type_name'] }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Durum:</td>
                            <td>
                                @if($licenseInfo['is_valid'])
                                    <span class="text-success"><i class="bi bi-check-circle"></i> Aktif</span>
                                @else
                                    <span class="text-danger"><i class="bi bi-x-circle"></i> Geçersiz</span>
                                @endif
                            </td>
                        </tr>
                        @if($licenseInfo['expires_at'])
                        <tr>
                            <td class="text-muted">Bitiş Tarihi:</td>
                            <td>{{ $licenseInfo['expires_at'] }}</td>
                        </tr>
                        @endif
                        @if($licenseInfo['is_trial'] && $licenseInfo['days_remaining'] !== null)
                        <tr>
                            <td class="text-muted">Kalan Gün:</td>
                            <td>
                                <span class="badge bg-{{ $licenseInfo['days_remaining'] <= 3 ? 'danger' : 'warning' }}">
                                    {{ $licenseInfo['days_remaining'] }} gün
                                </span>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 bg-light">
                    <h6 class="mb-3"><i class="bi bi-building"></i> Kullanım</h6>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Firma Limiti:</td>
                            <td>
                                @if($licenseInfo['max_firms'] === -1)
                                    <span class="text-success">Sınırsız</span>
                                @else
                                    {{ $licenseInfo['current_firms'] }} / {{ $licenseInfo['max_firms'] }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kullanım:</td>
                            <td>
                                @php
                                    $percentage = $licenseInfo['max_firms'] > 0 
                                        ? min(100, ($licenseInfo['current_firms'] / $licenseInfo['max_firms']) * 100) 
                                        : 0;
                                @endphp
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success') }}" 
                                         style="width: {{ $percentage }}%">
                                        {{ round($percentage) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Trial Uyarısı --}}
        @if($licenseInfo['is_trial'])
        <div class="alert alert-warning">
            <h6><i class="bi bi-clock-history"></i> Deneme Sürümü</h6>
            <p class="mb-2">14 günlük deneme sürümünü kullanıyorsunuz. Tam erişim için lisans satın alın.</p>
            <a href="#" class="btn btn-sm btn-warning" target="_blank">
                <i class="bi bi-cart"></i> Lisans Satın Al
            </a>
        </div>
        @endif

        {{-- Lisans Aktivasyonu --}}
        <h6 class="mb-3"><i class="bi bi-key"></i> Lisans Aktivasyonu</h6>
        <form action="{{ route('settings.license.activate') }}" method="POST" class="mb-4">
            @csrf
            <div class="row">
                <div class="col-md-8">
                    <input type="text" name="license_key" class="form-control" 
                           placeholder="XXXX-XXXX-XXXX-XXXX" 
                           pattern="[A-Za-z0-9]{3,4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}"
                           style="text-transform: uppercase;">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg"></i> Aktive Et
                    </button>
                </div>
            </div>
        </form>

        @if($licenseInfo['is_licensed'])
        <form action="{{ route('settings.license.deactivate') }}" method="POST" class="mb-4">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm"
                    onclick="return confirm('Lisansı kaldırmak istediğinizden emin misiniz?')">
                <i class="bi bi-x-lg"></i> Lisansı Kaldır
            </button>
        </form>
        @endif

        {{-- Lisans Tipleri --}}
        <h6 class="mb-3"><i class="bi bi-list-check"></i> Lisans Tipleri</h6>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Tip</th>
                        <th>Firma Limiti</th>
                        <th>Süre</th>
                        <th>Özellikler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($licenseTypes as $key => $type)
                    <tr class="{{ $licenseInfo['type'] === $key ? 'table-primary' : '' }}">
                        <td class="fw-bold">{{ $type['name'] }}</td>
                        <td>{{ $type['max_firms'] === -1 ? 'Sınırsız' : $type['max_firms'] }}</td>
                        <td>{{ $type['duration_days'] === -1 ? 'Ömür Boyu' : $type['duration_days'] . ' gün' }}</td>
                        <td>
                            @if(in_array('all', $type['features']))
                                <span class="badge bg-success">Tüm Özellikler</span>
                            @else
                                @foreach($type['features'] as $feature)
                                    <span class="badge bg-secondary">{{ $feature }}</span>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
