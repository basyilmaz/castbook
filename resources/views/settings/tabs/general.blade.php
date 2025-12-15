<form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <h6 class="fw-semibold mb-2">Lütfen formu kontrol edin.</h6>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Şirket Bilgileri --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">Şirket Bilgileri</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="company_name" class="form-label">Şirket Adı</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" 
                           value="{{ old('company_name', $settings['company_name'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label for="company_email" class="form-label">E-posta</label>
                    <input type="email" class="form-control" id="company_email" name="company_email" 
                           value="{{ old('company_email', $settings['company_email'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label for="company_phone" class="form-label">Telefon</label>
                    <input type="text" class="form-control" id="company_phone" name="company_phone" 
                           value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label for="company_address" class="form-label">Adres</label>
                    <input type="text" class="form-control" id="company_address" name="company_address" 
                           value="{{ old('company_address', $settings['company_address'] ?? '') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Logo ve Menü Özelleştirme --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">Logo ve Menü Özelleştirme</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="company_logo" class="form-label">Şirket Logosu</label>
                    <input type="file" class="form-control" id="company_logo" name="company_logo" accept="image/*">
                    <small class="text-muted">Maksimum 2MB, önerilen boyut: 200x50px</small>
                    @if($settings['company_logo_base64'] ?? false)
                        <div class="mt-2">
                            <img src="{{ $settings['company_logo_base64'] }}" 
                                 alt="Logo" class="img-thumbnail" style="max-height: 50px;">
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label for="theme_mode" class="form-label">Tema</label>
                    <select class="form-select" id="theme_mode" name="theme_mode">
                        <option value="light" @selected(old('theme_mode', $settings['theme_mode'] ?? 'light') === 'light')>Açık</option>
                        <option value="dark" @selected(old('theme_mode', $settings['theme_mode'] ?? 'light') === 'dark')>Koyu</option>
                        <option value="auto" @selected(old('theme_mode', $settings['theme_mode'] ?? 'light') === 'auto')>Otomatik</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="company_menu_title" class="form-label">Menü Başlığı</label>
                    <input type="text" class="form-control" id="company_menu_title" name="company_menu_title" 
                           value="{{ old('company_menu_title', $settings['company_menu_title'] ?? '') }}"
                           placeholder="Muhasebe">
                </div>
                <div class="col-md-6">
                    <label for="company_menu_subtitle" class="form-label">Menü Alt Başlığı</label>
                    <input type="text" class="form-control" id="company_menu_subtitle" name="company_menu_subtitle" 
                           value="{{ old('company_menu_subtitle', $settings['company_menu_subtitle'] ?? '') }}"
                           placeholder="Takip Paneli">
                </div>
            </div>
        </div>
    </div>

    {{-- Fatura Ayarları --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">Fatura Ayarları</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="invoice_default_due_days" class="form-label">Varsayılan Vade Günü</label>
                    <input type="number" class="form-control" id="invoice_default_due_days" 
                           name="invoice_default_due_days" min="1" max="31"
                           value="{{ old('invoice_default_due_days', $settings['invoice_default_due_days'] ?? 10) }}">
                </div>
                <div class="col-md-4">
                    <label for="invoice_prefix" class="form-label">Fatura Ön Eki</label>
                    <input type="text" class="form-control" id="invoice_prefix" name="invoice_prefix" 
                           value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV') }}">
                </div>
                <div class="col-md-4">
                    <label for="invoice_auto_notify" class="form-label d-block">Otomatik Bildirim</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="invoice_auto_notify" 
                               name="invoice_auto_notify" value="1"
                               @checked(old('invoice_auto_notify', $invoiceNotifyEnabled ?? false))>
                        <label class="form-check-label" for="invoice_auto_notify">
                            Aylık fatura oluşturulduğunda bildir
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <label for="invoice_default_description" class="form-label">Varsayılan Fatura Açıklaması</label>
                    <textarea class="form-control" id="invoice_default_description" 
                              name="invoice_default_description" rows="2"
                              placeholder="Aylık muhasebe hizmet bedeli">{{ old('invoice_default_description', $settings['invoice_default_description'] ?? '') }}</textarea>
                    <small class="text-muted">Yeni faturalarda otomatik olarak kullanılacak açıklama</small>
                </div>
                <div class="col-12" id="notify_recipients_section" style="display: {{ old('invoice_auto_notify', $invoiceNotifyEnabled ?? false) ? 'block' : 'none' }};">
                    <label for="invoice_notify_recipients" class="form-label">Bildirim Alıcıları</label>
                    @php
                        $recipientsValue = old('invoice_notify_recipients');
                        if ($recipientsValue === null) {
                            $recipientsValue = implode("\n", $invoiceNotifyRecipients ?? []);
                        }
                    @endphp
                    <textarea class="form-control" id="invoice_notify_recipients" 
                              name="invoice_notify_recipients" rows="3"
                              placeholder="ornek@email.com&#10;diger@email.com">{{ $recipientsValue }}</textarea>
                    <small class="text-muted">Her satıra bir e-posta adresi yazın</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tahsilat Yöntemleri --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="mb-0">Tahsilat Yöntemleri</h6>
        </div>
        <div class="card-body">
            @php
                $paymentMethodsValue = old('payment_methods');
                if ($paymentMethodsValue === null) {
                    $paymentMethodsValue = implode("\n", $paymentMethods ?? ['Nakit', 'Banka']);
                }
            @endphp
            <label for="payment_methods" class="form-label">Yöntemler (Her satıra bir tane)</label>
            <textarea class="form-control" id="payment_methods" name="payment_methods" 
                      rows="4">{{ $paymentMethodsValue }}</textarea>
            <small class="text-muted">Her satıra bir ödeme yöntemi yazın.</small>
        </div>
    </div>

    {{-- Mail Ayarları --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Mail Sunucu Ayarları</h6>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" 
                    data-bs-target="#mailSettings" aria-expanded="false">
                <i class="bi bi-chevron-down"></i> Göster/Gizle
            </button>
        </div>
        <div class="collapse" id="mailSettings">
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Bilgi:</strong> E-posta bildirimleri için SMTP sunucu bilgilerinizi girin.
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="mail_host" class="form-label">SMTP Sunucu</label>
                        <input type="text" class="form-control" id="mail_host" name="mail_host" 
                               value="{{ old('mail_host', $settings['mail_host'] ?? '') }}"
                               placeholder="smtp.gmail.com">
                    </div>
                    <div class="col-md-3">
                        <label for="mail_port" class="form-label">Port</label>
                        <input type="number" class="form-control" id="mail_port" name="mail_port" 
                               value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}"
                               placeholder="587">
                    </div>
                    <div class="col-md-3">
                        <label for="mail_encryption" class="form-label">Şifreleme</label>
                        <select class="form-select" id="mail_encryption" name="mail_encryption">
                            <option value="">Yok</option>
                            <option value="tls" @selected(old('mail_encryption', $settings['mail_encryption'] ?? 'tls') === 'tls')>TLS</option>
                            <option value="ssl" @selected(old('mail_encryption', $settings['mail_encryption'] ?? 'tls') === 'ssl')>SSL</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="mail_username" class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" id="mail_username" name="mail_username" 
                               value="{{ old('mail_username', $settings['mail_username'] ?? '') }}"
                               placeholder="kullanici@gmail.com">
                    </div>
                    <div class="col-md-6">
                        <label for="mail_password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="mail_password" name="mail_password" 
                               value="{{ old('mail_password', $settings['mail_password'] ?? '') }}"
                               placeholder="••••••••">
                        <small class="text-muted">Boş bırakırsanız mevcut şifre korunur</small>
                    </div>
                    <div class="col-md-6">
                        <label for="mail_from_address" class="form-label">Gönderen E-posta</label>
                        <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" 
                               value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}"
                               placeholder="noreply@sirket.com">
                    </div>
                    <div class="col-md-6">
                        <label for="mail_from_name" class="form-label">Gönderen Adı</label>
                        <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" 
                               value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}"
                               placeholder="Muhasebe Sistemi">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Kaydet
        </button>
    </div>
</form>

<script>
// Otomatik bildirim toggle
document.getElementById('invoice_auto_notify').addEventListener('change', function() {
    document.getElementById('notify_recipients_section').style.display = this.checked ? 'block' : 'none';
});
</script>
