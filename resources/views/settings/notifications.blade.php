@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-semibold mb-1">Bildirim Tercihleri</h4>
                    <p class="text-muted mb-0">E-posta bildirimleri ve hatırlatma ayarlarını yapılandırın.</p>
                </div>
                <a href="{{ route('settings.edit') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left me-1"></i>Ayarlara Dön
                </a>
            </div>

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('settings.notifications.update') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Genel Ayarlar --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-bell me-2"></i>Genel Bildirim Ayarları</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_email_notifications" 
                                   name="enable_email_notifications" value="1"
                                   @checked($settings['enable_email_notifications'] ?? false)>
                            <label class="form-check-label" for="enable_email_notifications">
                                <strong>E-posta Bildirimleri</strong>
                                <small class="d-block text-muted">Tüm otomatik e-posta bildirimlerini aç/kapat</small>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label for="notification_recipients" class="form-label">Bildirim Alıcıları</label>
                            <textarea class="form-control" id="notification_recipients" name="notification_recipients" 
                                      rows="3" placeholder="ornek@email.com&#10;diger@email.com">{{ $settings['notification_recipients'] ?? '' }}</textarea>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Her satıra bir e-posta adresi yazın. Hatırlatma bildirimleri bu adreslere gönderilir.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ödeme Hatırlatmaları --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Ödeme Hatırlatmaları</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_payment_reminders" 
                                   name="enable_payment_reminders" value="1"
                                   @checked($settings['enable_payment_reminders'] ?? true)>
                            <label class="form-check-label" for="enable_payment_reminders">
                                <strong>Ödeme Hatırlatmaları</strong>
                                <small class="d-block text-muted">Vadesi yaklaşan ve gecikmiş ödemeler için bildirim gönder</small>
                            </label>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="payment_reminder_days" class="form-label">Kaç gün önce hatırlat?</label>
                                <select class="form-select" id="payment_reminder_days" name="payment_reminder_days">
                                    @foreach([1, 2, 3, 5, 7, 10, 14] as $days)
                                        <option value="{{ $days }}" @selected(($settings['payment_reminder_days'] ?? 3) == $days)>
                                            {{ $days }} gün önce
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="overdue_reminder_frequency" class="form-label">Gecikme hatırlatma sıklığı</label>
                                <select class="form-select" id="overdue_reminder_frequency" name="overdue_reminder_frequency">
                                    <option value="daily" @selected(($settings['overdue_reminder_frequency'] ?? 'daily') == 'daily')>Her gün</option>
                                    <option value="weekly" @selected(($settings['overdue_reminder_frequency'] ?? 'daily') == 'weekly')>Haftada bir</option>
                                    <option value="once" @selected(($settings['overdue_reminder_frequency'] ?? 'daily') == 'once')>Sadece bir kez</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Beyanname Hatırlatmaları --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Beyanname Hatırlatmaları</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_declaration_reminders" 
                                   name="enable_declaration_reminders" value="1"
                                   @checked($settings['enable_declaration_reminders'] ?? true)>
                            <label class="form-check-label" for="enable_declaration_reminders">
                                <strong>Beyanname Hatırlatmaları</strong>
                                <small class="d-block text-muted">Yaklaşan beyanname son tarihleri için bildirim gönder</small>
                            </label>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="declaration_reminder_days" class="form-label">Kaç gün önce hatırlat?</label>
                                <select class="form-select" id="declaration_reminder_days" name="declaration_reminder_days">
                                    @foreach([1, 2, 3, 5, 7, 10, 14] as $days)
                                        <option value="{{ $days }}" @selected(($settings['declaration_reminder_days'] ?? 3) == $days)>
                                            {{ $days }} gün önce
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Gönderim Zamanı --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Gönderim Zamanı</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="notification_time" class="form-label">Bildirim gönderim saati</label>
                                <select class="form-select" id="notification_time" name="notification_time">
                                    @foreach(['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '14:00', '16:00', '18:00'] as $time)
                                        <option value="{{ $time }}" @selected(($settings['notification_time'] ?? '09:00') == $time)>
                                            {{ $time }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Otomatik hatırlatmalar bu saatte gönderilir.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Haftalık Özet --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Haftalık Özet Raporu</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_weekly_summary" 
                                   name="enable_weekly_summary" value="1"
                                   @checked($settings['enable_weekly_summary'] ?? false)>
                            <label class="form-check-label" for="enable_weekly_summary">
                                <strong>Haftalık Özet E-postası</strong>
                                <small class="d-block text-muted">Her hafta özet raporu gönder (Faturalar, Tahsilatlar, Yaklaşan Beyannameler)</small>
                            </label>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="weekly_summary_day" class="form-label">Gönderim günü</label>
                                <select class="form-select" id="weekly_summary_day" name="weekly_summary_day">
                                    @foreach(['monday' => 'Pazartesi', 'tuesday' => 'Salı', 'wednesday' => 'Çarşamba', 'thursday' => 'Perşembe', 'friday' => 'Cuma', 'saturday' => 'Cumartesi', 'sunday' => 'Pazar'] as $day => $label)
                                        <option value="{{ $day }}" @selected(($settings['weekly_summary_day'] ?? 'monday') == $day)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Haftalık özet bu gün sabah gönderilir.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-lg me-2"></i>Ayarları Kaydet
                    </button>
                    <a href="{{ route('settings.edit') }}" class="btn btn-light btn-lg">İptal</a>
                </div>
            </form>

            {{-- Test Bölümü --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-send me-2"></i>Test E-postası Gönder</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Bildirim ayarlarınızı test etmek için aşağıdaki butonu kullanabilirsiniz.
                        Test e-postası yukarıda belirtilen alıcılara gönderilecektir.
                    </p>
                    <form action="{{ route('settings.notifications.test') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary" 
                                @if(empty($settings['notification_recipients'])) disabled @endif>
                            <i class="bi bi-envelope-check me-1"></i>Test E-postası Gönder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
