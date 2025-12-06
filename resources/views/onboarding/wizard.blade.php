@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Wizard Header --}}
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white mb-3" 
                     style="width: 80px; height: 80px;">
                    <i class="bi bi-rocket-takeoff fs-1"></i>
                </div>
                <h2 class="fw-bold">CastBook'a Hoş Geldiniz!</h2>
                <p class="text-muted">Başlamak için birkaç basit adımı tamamlayın</p>
            </div>

            {{-- Progress Steps --}}
            <div class="d-flex justify-content-center mb-5">
                <div class="d-flex align-items-center gap-2" id="wizardSteps">
                    <div class="step active" data-step="1">
                        <div class="step-circle">1</div>
                        <span class="step-label d-none d-md-inline">Şirket</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" data-step="2">
                        <div class="step-circle">2</div>
                        <span class="step-label d-none d-md-inline">İlk Firma</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" data-step="3">
                        <div class="step-circle">3</div>
                        <span class="step-label d-none d-md-inline">Tercihler</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" data-step="4">
                        <div class="step-circle">4</div>
                        <span class="step-label d-none d-md-inline">Tamamla</span>
                    </div>
                </div>
            </div>

            {{-- Wizard Content --}}
            <form action="{{ route('onboarding.complete') }}" method="POST" id="onboardingForm">
                @csrf

                {{-- Step 1: Company Info --}}
                <div class="wizard-step active" data-step="1">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-lg-5">
                            <h4 class="mb-4"><i class="bi bi-building-gear text-primary me-2"></i>Şirket Bilgileri</h4>
                            <p class="text-muted mb-4">Muhasebe büronuzun temel bilgilerini girin.</p>

                            <div class="mb-3">
                                <label for="company_name" class="form-label">Şirket Adı <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="company_name" name="company_name" 
                                       value="{{ old('company_name', $settings['company_name'] ?? '') }}" required>
                            </div>

                            <div class="row g-3">
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
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2: First Firm --}}
                <div class="wizard-step" data-step="2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-lg-5">
                            <h4 class="mb-4"><i class="bi bi-person-plus text-primary me-2"></i>İlk Müşterinizi Ekleyin</h4>
                            <p class="text-muted mb-4">İsterseniz şimdi ilk müşteri firmanızı ekleyebilirsiniz. Bu adımı atlayabilirsiniz.</p>

                            <div class="mb-3">
                                <label for="first_firm_name" class="form-label">Firma Adı</label>
                                <input type="text" class="form-control form-control-lg" id="first_firm_name" name="first_firm_name">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="first_firm_tax_no" class="form-label">Vergi Numarası</label>
                                    <input type="text" class="form-control" id="first_firm_tax_no" name="first_firm_tax_no">
                                </div>
                                <div class="col-md-6">
                                    <label for="first_firm_monthly_fee" class="form-label">Aylık Ücret (₺)</label>
                                    <input type="number" step="0.01" class="form-control" id="first_firm_monthly_fee" name="first_firm_monthly_fee">
                                </div>
                            </div>

                            <div class="alert alert-info mt-4">
                                <i class="bi bi-lightbulb me-2"></i>
                                Bu adımı atlarsanız, daha sonra <strong>Firmalar → Yeni Firma</strong> menüsünden ekleyebilirsiniz.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Preferences --}}
                <div class="wizard-step" data-step="3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-lg-5">
                            <h4 class="mb-4"><i class="bi bi-sliders text-primary me-2"></i>Tercihleriniz</h4>
                            <p class="text-muted mb-4">Kullanım tercihlerinizi belirleyin.</p>

                            <div class="mb-4">
                                <label class="form-label">Tema Modu</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="theme_mode" id="theme_light" value="light" checked>
                                        <label class="form-check-label" for="theme_light">
                                            <i class="bi bi-sun me-1"></i>Açık
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="theme_mode" id="theme_dark" value="dark">
                                        <label class="form-check-label" for="theme_dark">
                                            <i class="bi bi-moon me-1"></i>Koyu
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="theme_mode" id="theme_auto" value="auto">
                                        <label class="form-check-label" for="theme_auto">
                                            <i class="bi bi-phone me-1"></i>Otomatik
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="invoice_due_days" class="form-label">Varsayılan Fatura Vade Günü</label>
                                <select class="form-select" id="invoice_due_days" name="invoice_due_days">
                                    <option value="7">7 gün</option>
                                    <option value="15">15 gün</option>
                                    <option value="30" selected>30 gün</option>
                                    <option value="45">45 gün</option>
                                    <option value="60">60 gün</option>
                                </select>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="enable_notifications" name="enable_notifications" value="1" checked>
                                <label class="form-check-label" for="enable_notifications">
                                    E-posta bildirimlerini etkinleştir
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 4: Complete --}}
                <div class="wizard-step" data-step="4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-5 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white mb-4" 
                                 style="width: 100px; height: 100px;">
                                <i class="bi bi-check-lg" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="fw-bold mb-3">Her Şey Hazır!</h3>
                            <p class="text-muted mb-4">
                                Kurulum tamamlandı. Artık CastBook'u kullanmaya başlayabilirsiniz.
                            </p>

                            <div class="row g-3 text-start mb-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <i class="bi bi-building text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Firmalar</strong>
                                            <small class="d-block text-muted">Müşterilerinizi yönetin</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <i class="bi bi-receipt text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Faturalar</strong>
                                            <small class="d-block text-muted">Faturaları takip edin</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <i class="bi bi-bar-chart text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Raporlar</strong>
                                            <small class="d-block text-muted">Analiz ve raporlama</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <i class="bi bi-question-circle text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Yardım</strong>
                                            <small class="d-block text-muted">Kullanıcı kılavuzu</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Navigation Buttons --}}
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-light btn-lg" id="prevBtn" style="display: none;">
                        <i class="bi bi-arrow-left me-1"></i>Geri
                    </button>
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-lg" id="skipBtn">
                            Atla
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" id="nextBtn">
                            İleri<i class="bi bi-arrow-right ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn" style="display: none;">
                            <i class="bi bi-check-lg me-1"></i>Başla
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
    
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .step.active .step-circle,
    .step.completed .step-circle {
        background: var(--bs-primary);
        color: white;
    }
    
    .step.completed .step-circle {
        background: var(--bs-success);
    }
    
    .step-label {
        font-size: 0.85rem;
        color: #6c757d;
    }
    
    .step.active .step-label {
        color: var(--bs-primary);
        font-weight: 600;
    }
    
    .step-line {
        width: 60px;
        height: 3px;
        background: #e9ecef;
        margin: 0 0.5rem;
        margin-bottom: 1.5rem;
    }
    
    .wizard-step {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    
    .wizard-step.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 4;
    
    const steps = document.querySelectorAll('.wizard-step');
    const stepIndicators = document.querySelectorAll('#wizardSteps .step');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const skipBtn = document.getElementById('skipBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    function showStep(step) {
        steps.forEach(s => s.classList.remove('active'));
        document.querySelector(`.wizard-step[data-step="${step}"]`).classList.add('active');
        
        stepIndicators.forEach((indicator, index) => {
            indicator.classList.remove('active', 'completed');
            if (index + 1 < step) {
                indicator.classList.add('completed');
            } else if (index + 1 === step) {
                indicator.classList.add('active');
            }
        });
        
        // Button visibility
        prevBtn.style.display = step > 1 ? 'block' : 'none';
        
        if (step === totalSteps) {
            nextBtn.style.display = 'none';
            skipBtn.style.display = 'none';
            submitBtn.style.display = 'block';
        } else {
            nextBtn.style.display = 'block';
            skipBtn.style.display = step === 2 ? 'block' : 'none';
            submitBtn.style.display = 'none';
        }
        
        currentStep = step;
    }
    
    nextBtn.addEventListener('click', function() {
        if (currentStep < totalSteps) {
            showStep(currentStep + 1);
        }
    });
    
    prevBtn.addEventListener('click', function() {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    });
    
    skipBtn.addEventListener('click', function() {
        // Clear optional fields
        document.getElementById('first_firm_name').value = '';
        document.getElementById('first_firm_tax_no').value = '';
        document.getElementById('first_firm_monthly_fee').value = '';
        showStep(currentStep + 1);
    });
    
    showStep(1);
});
</script>
@endsection
