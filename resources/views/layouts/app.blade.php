<!DOCTYPE html>
<html lang="tr" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- SEO Meta Tags --}}
    <title>{{ isset($pageTitle) ? $pageTitle . ' - ' : '' }}{{ config('app.name', 'CastBook') }}</title>
    <meta name="description" content="{{ $pageDescription ?? 'CastBook - Profesyonel muhasebe takip ve firma yönetim sistemi. Faturaları, tahsilatları ve beyannameleri kolayca takip edin.' }}">
    <meta name="keywords" content="muhasebe, fatura takip, firma yönetimi, tahsilat, beyanname, mali müşavir, castbook">
    <meta name="author" content="CastBook">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    
    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ isset($pageTitle) ? $pageTitle . ' - ' : '' }}{{ config('app.name', 'CastBook') }}">
    <meta property="og:description" content="{{ $pageDescription ?? 'CastBook - Profesyonel muhasebe takip ve firma yönetim sistemi.' }}">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:site_name" content="CastBook">
    
    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ isset($pageTitle) ? $pageTitle . ' - ' : '' }}{{ config('app.name', 'CastBook') }}">
    <meta name="twitter:description" content="{{ $pageDescription ?? 'CastBook - Profesyonel muhasebe takip ve firma yönetim sistemi.' }}">
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    
    {{-- Theme Color --}}
    <meta name="theme-color" content="#2563eb">
    <meta name="msapplication-TileColor" content="#2563eb">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        document.documentElement.classList.remove('no-js');
        
        // Token yönetimi (LocalStorage tabanlı)
        (function() {
            const TOKEN_KEY = 'auth_token';
            const URL_PARAM = '_auth'; // _token yerine _auth kullan (CSRF ile çakışmasın)
            
            // URL'den token al ve localStorage'a kaydet
            const urlParams = new URLSearchParams(window.location.search);
            const urlToken = urlParams.get(URL_PARAM);
            
            if (urlToken) {
                localStorage.setItem(TOKEN_KEY, urlToken);
                // URL'den token'ı temizle
                urlParams.delete(URL_PARAM);
                const cleanUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '') + window.location.hash;
                window.history.replaceState({}, document.title, cleanUrl || '/');
            } else {
                // URL'de token yok ama localStorage'da var - hard refresh durumu
                const storedToken = localStorage.getItem(TOKEN_KEY);
                if (storedToken && !window.location.pathname.match(/^\/($|login|password)/)) {
                    // Login ve public sayfalar hariç, token ile sayfayı yeniden yükle
                    const separator = window.location.search ? '&' : '?';
                    const newUrl = window.location.pathname + window.location.search + separator + URL_PARAM + '=' + storedToken + window.location.hash;
                    window.location.replace(newUrl);
                    return; // Sayfa yeniden yükleniyor, devam etme
                }
            }
            
            // Sayfa yüklendikten sonra tüm linklere token ekle
            document.addEventListener('DOMContentLoaded', function() {
                const token = localStorage.getItem(TOKEN_KEY);
                if (!token) return;
                
                // Global axios interceptor - tüm AJAX isteklerine token ekle
                // Axios async yüklenebilir, birkaç kez kontrol et
                function setupAxiosInterceptor() {
                    if (typeof window.axios !== 'undefined' && window.axios.interceptors) {
                        window.axios.interceptors.request.use(function(config) {
                            const t = localStorage.getItem(TOKEN_KEY);
                            if (t) {
                                config.params = config.params || {};
                                config.params._auth = t;
                            }
                            return config;
                        });
                        return true;
                    }
                    return false;
                }
                
                // İlk deneme
                if (!setupAxiosInterceptor()) {
                    // Axios henüz yüklenmemiş, bekle ve tekrar dene
                    let attempts = 0;
                    const maxAttempts = 10;
                    const interval = setInterval(function() {
                        attempts++;
                        if (setupAxiosInterceptor() || attempts >= maxAttempts) {
                            clearInterval(interval);
                        }
                    }, 100);
                }
                
                // Tüm internal linklere token ekle
                function addTokenToLinks() {
                    document.querySelectorAll('a[href]').forEach(function(link) {
                        const href = link.getAttribute('href');
                        if (!href) return;
                        
                        // Dış linkler, asset'ler, logout, #hash linkler hariç
                        if (href.startsWith('http') && !href.includes(window.location.host)) return;
                        if (href.startsWith('#') || href.startsWith('javascript:')) return;
                        if (href.includes('logout')) return;
                        if (/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|pdf)(\?|$)/.test(href)) return;
                        if (href.includes(URL_PARAM + '=')) return;
                        
                        // Token ekle
                        const separator = href.includes('?') ? '&' : '?';
                        link.setAttribute('href', href + separator + URL_PARAM + '=' + token);
                    });
                }
                
                // Form'lara hidden token field ekle
                document.querySelectorAll('form').forEach(function(form) {
                    const action = form.getAttribute('action') || '';
                    if (action.includes('logout')) return;
                    
                    // Zaten auth token varsa ekleme
                    if (form.querySelector('input[name="' + URL_PARAM + '"]')) return;
                    
                    const tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = URL_PARAM;
                    tokenInput.value = token;
                    form.appendChild(tokenInput);
                });
                
                addTokenToLinks();
                
                // Dinamik içerik için MutationObserver
                const observer = new MutationObserver(addTokenToLinks);
                observer.observe(document.body, { childList: true, subtree: true });
            });
            
            // Logout olunca localStorage'ı temizle
            window.clearAuthToken = function() {
                localStorage.removeItem(TOKEN_KEY);
            };
            
            // Global erişim için
            window.getAuthToken = function() {
                return localStorage.getItem(TOKEN_KEY);
            };
        })();
    </script>
</head>
@php
    $appName = config('app.name', 'CastBook');
    $companyName = ($layoutCompanyName ?? $appName) ?: $appName;
    $companyInitial = $layoutCompanyInitial ?? mb_strtoupper(mb_substr($companyName, 0, 1));
    $logoUrl = $layoutLogoUrl ?? null;
    $themeMode = $layoutThemeMode ?? 'auto';
    $isAdmin = $layoutIsAdmin ?? false;
    $menuTitle = ($layoutMenuTitle ?? $companyName) ?: $companyName;
    $menuSubtitle = $layoutMenuSubtitle ?? null;
@endphp
<body class="min-vh-100" data-theme-mode="{{ $themeMode }}">
    {{-- ÜST SATIR: Logo + Sağ taraf butonları --}}
    <nav class="navbar navbar-dark app-navbar py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-3" href="{{ route('dashboard') }}">
                <span class="brand-logo-wrapper">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo" class="brand-logo-img">
                    @else
                        <span class="brand-logo-placeholder">{{ $companyInitial }}</span>
                    @endif
                </span>
                <span class="brand-text d-flex flex-column">
                    <span class="brand-title">{{ $menuTitle }}</span>
                    @if(! empty($menuSubtitle))
                        <span class="brand-subtitle d-none d-md-block">{{ $menuSubtitle }}</span>
                    @endif
                </span>
            </a>
            
            <div class="d-flex align-items-center gap-2">
                {{-- Arama Butonu --}}
                <button type="button" class="btn btn-link text-white p-2 d-none d-lg-block" 
                        data-bs-toggle="modal" data-bs-target="#searchModal"
                        title="Ara (Ctrl+K)">
                    <i class="bi bi-search fs-5"></i>
                </button>
                
                {{-- Bildirim Zili --}}
                <div class="dropdown" id="notificationDropdown">
                    <button class="btn btn-link text-white position-relative p-2" 
                            type="button" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false"
                            id="notificationBell">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" 
                              id="notificationBadge">
                            0
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg" 
                         style="width: 360px; max-height: 450px; overflow-y: auto;">
                        <div class="dropdown-header d-flex justify-content-between align-items-center py-2">
                            <span class="fw-semibold">Bildirimler</span>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" 
                                    id="markAllReadBtn">
                                Tümünü Okundu İşaretle
                            </button>
                        </div>
                        <div id="notificationList">
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                                <small>Yükleniyor...</small>
                            </div>
                        </div>
                        <div class="dropdown-divider mb-0"></div>
                        <div class="text-center py-2">
                            <small class="text-muted">Son 15 bildirim gösteriliyor</small>
                        </div>
                    </div>
                </div>
                
                {{-- Çıkış Butonu --}}
                <form method="POST" action="{{ route('logout') }}" class="d-inline" onsubmit="if(window.clearAuthToken) clearAuthToken();">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>Çıkış
                    </button>
                </form>
                
                {{-- Mobil Hamburger --}}
                <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav"
                    aria-controls="menuNav" aria-expanded="false" aria-label="Menüyü aç/kapa">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </nav>
    
    {{-- ALT SATIR: Menü Linkleri (kendi satırında, üstte hiçbir şey yok) --}}
    <nav class="navbar navbar-expand-lg navbar-dark py-0" style="background: linear-gradient(135deg, #1e40af, #3b82f6);">
        <div class="container">
            <div class="collapse navbar-collapse" id="menuNav">
                <ul class="navbar-nav">
                    {{-- Ana Sayfa --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i>Genel Bakış
                        </a>
                    </li>
                    
                    {{-- Firmalar --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('firms.*') ? 'active' : '' }}"
                           href="{{ route('firms.index') }}">
                            <i class="bi bi-building me-1"></i>Firmalar
                        </a>
                    </li>
                    
                    {{-- Finansal Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('invoices.*') || request()->routeIs('payments.*') ? 'active' : '' }}" 
                           href="#" 
                           role="button" 
                           data-bs-toggle="dropdown" 
                           aria-expanded="false">
                            <i class="bi bi-wallet2 me-1"></i>Finansal
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}" 
                                   href="{{ route('invoices.index') }}">
                                    <i class="bi bi-receipt me-2"></i>Faturalar
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('payments.*') ? 'active' : '' }}" 
                                   href="{{ route('payments.index') }}">
                                    <i class="bi bi-cash-stack me-2"></i>Tahsilatlar
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('invoices.create') }}">
                                    <i class="bi bi-plus-circle me-2 text-primary"></i>Yeni Fatura
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('payments.create') }}">
                                    <i class="bi bi-plus-circle me-2 text-success"></i>Tahsilat Kaydet
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    {{-- Vergi Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('tax-declarations.*') || request()->routeIs('tax-calendar.*') ? 'active' : '' }}" 
                           href="#" 
                           role="button" 
                           data-bs-toggle="dropdown" 
                           aria-expanded="false">
                            <i class="bi bi-file-earmark-text me-1"></i>Vergi
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('tax-declarations.*') ? 'active' : '' }}" 
                                   href="{{ route('tax-declarations.index') }}">
                                    <i class="bi bi-file-earmark-medical me-2"></i>Beyannameler
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('tax-calendar.*') ? 'active' : '' }}" 
                                   href="{{ route('tax-calendar.index') }}">
                                    <i class="bi bi-calendar-event me-2"></i>GİB Takvimi
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('tax-declarations.index', ['status' => 'pending']) }}">
                                    <i class="bi bi-hourglass-split me-2 text-warning"></i>Bekleyen Beyannameler
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    {{-- Raporlar --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('reports.*') ? 'active' : '' }}" 
                           href="#" 
                           role="button" 
                           data-bs-toggle="dropdown" 
                           aria-expanded="false">
                            <i class="bi bi-bar-chart me-1"></i>Raporlar
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('reports.balance') ? 'active' : '' }}" 
                                   href="{{ route('reports.balance') }}">
                                    <i class="bi bi-wallet me-2"></i>Bakiye Raporu
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('reports.collections') ? 'active' : '' }}" 
                                   href="{{ route('reports.collections') }}">
                                    <i class="bi bi-cash-coin me-2"></i>Tahsilat Raporu
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('reports.invoices') ? 'active' : '' }}" 
                                   href="{{ route('reports.invoices') }}">
                                    <i class="bi bi-receipt me-2"></i>Fatura Raporu
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('reports.overdues') ? 'active' : '' }}" 
                                   href="{{ route('reports.overdues') }}">
                                    <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Gecikmiş Ödemeler
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    {{-- Yönetim (Admin) --}}
                    @if($isAdmin)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('users.*') || request()->routeIs('audit-logs.*') || request()->routeIs('settings.*') ? 'active' : '' }}" 
                           href="#" 
                           role="button" 
                           data-bs-toggle="dropdown" 
                           aria-expanded="false">
                            <i class="bi bi-gear me-1"></i>Yönetim
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('settings.*') ? 'active' : '' }}" 
                                   href="{{ route('settings.edit') }}">
                                    <i class="bi bi-sliders me-2"></i>Ayarlar
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('users.*') ? 'active' : '' }}" 
                                   href="{{ route('users.index') }}">
                                    <i class="bi bi-people me-2"></i>Kullanıcılar
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" 
                                   href="{{ route('audit-logs.index') }}">
                                    <i class="bi bi-journal-text me-2"></i>Aktivite Logu
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('help') }}">
                                    <i class="bi bi-question-circle me-2"></i>Yardım
                                </a>
                            </li>
                        </ul>
                    </li>
                    @else
                    {{-- Normal Kullanıcı için Ayarlar --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                           href="{{ route('settings.edit') }}">
                            <i class="bi bi-gear me-1"></i>Ayarlar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('help') ? 'active' : '' }}"
                           href="{{ route('help') }}">
                            <i class="bi bi-question-circle"></i>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
    <main class="container py-4">

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-auto py-3 text-center text-muted" style="background: rgba(0,0,0,0.02);">
        <div class="container">
            <small>
                {{ config('app.name', 'CastBook') }} v{{ config('app.version', '2.0.0') }}
                &copy; {{ date('Y') }}
            </small>
        </div>
    </footer>

    {{-- Toast Bildirimleri --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
        @if(session('status'))
        <div class="toast show align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
        @if(session('warning'))
        <div class="toast show align-items-center text-bg-warning border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
        @if($errors->any())
        <div class="toast show align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="8000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-x-circle me-2"></i>{{ $errors->first() }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
    </div>

    {{-- Arama Modal - Dark Tema --}}
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true" data-bs-backdrop="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg text-white" style="background: #1e3a8a;">
                <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #1e3a8a, #2563eb);">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-transparent border-0 text-white-50">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control bg-transparent border-0 shadow-none fs-4 text-white" 
                               id="globalSearchInput"
                               placeholder="Firma, fatura veya beyanname ara..."
                               autocomplete="off"
                               style="color: white !important;"
                               autofocus>
                        <button type="button" class="btn-close btn-close-white me-2" data-bs-dismiss="modal" aria-label="Kapat"></button>
                    </div>
                </div>
                <div class="modal-body pt-0" style="background: #1e3a8a;">
                    <hr class="border-light opacity-25 my-2">
                    <div id="searchResults" style="max-height: 400px; overflow-y: auto; background: #1e3a8a;">
                        <div class="text-center text-white-50 py-4">
                            <i class="bi bi-lightbulb fs-4 d-block mb-2"></i>
                            <small>Aramaya başlamak için en az 2 karakter yazın</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center" style="background: #1e3a8a;">
                    <small class="text-white-50">
                        <kbd class="bg-dark">↑↓</kbd> ile gezin, <kbd class="bg-dark">Enter</kbd> ile seçin, <kbd class="bg-dark">Esc</kbd> ile kapatın
                    </small>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    
    <script>
        // Bootstrap yüklenince çalıştır
        document.addEventListener('DOMContentLoaded', function() {
            
            // Bootstrap kontrolü ile Toast'ları başlat
            function initToasts() {
                if (typeof bootstrap === 'undefined' || typeof bootstrap.Toast === 'undefined') {
                    setTimeout(initToasts, 100);
                    return;
                }
                var toasts = document.querySelectorAll('.toast');
                toasts.forEach(function(toast) {
                    var bsToast = new bootstrap.Toast(toast);
                });
            }
            initToasts();
            
            // --- NAVBAR DROPDOWN'LARI FIXED STRATEGY İLE YENİDEN BAŞLAT ---
            // Bu, dropdown menülerin her zaman en üstte görünmesini sağlar
            function initFixedDropdowns() {
                // Bootstrap yüklü mü kontrol et
                if (typeof bootstrap === 'undefined' || typeof bootstrap.Dropdown === 'undefined') {
                    // Bootstrap henüz yüklenmemiş, 100ms sonra tekrar dene
                    setTimeout(initFixedDropdowns, 100);
                    return;
                }
                
                document.querySelectorAll('.app-navbar .nav-item.dropdown .dropdown-toggle').forEach(function(dropdownToggle) {
                    // Mevcut dropdown instance'ı varsa dispose et
                    var existingDropdown = bootstrap.Dropdown.getInstance(dropdownToggle);
                    if (existingDropdown) {
                        existingDropdown.dispose();
                    }
                    // Yeni dropdown'u fixed strategy ile oluştur
                    new bootstrap.Dropdown(dropdownToggle, {
                        popperConfig: {
                            strategy: 'fixed',
                            modifiers: [
                                {
                                    name: 'preventOverflow',
                                    options: {
                                        boundary: 'viewport'
                                    }
                                }
                            ]
                        }
                    });
                });
            }
            
            // Bootstrap yüklenince dropdown'ları başlat
            initFixedDropdowns();

            // Global Arama - Ctrl+K kısayolu ve AJAX arama
            const searchInput = document.getElementById('globalSearchInput');
            const searchResults = document.getElementById('searchResults');
            const searchModal = document.getElementById('searchModal');
            
            if (!searchInput || !searchResults) return;
            
            let searchTimeout = null;
            let searchModalInstance = null;
            
            // Bootstrap yüklenince modal'ı başlat
            function initSearchModal() {
                if (typeof bootstrap === 'undefined' || typeof bootstrap.Modal === 'undefined') {
                    setTimeout(initSearchModal, 100);
                    return;
                }
                searchModalInstance = searchModal ? new bootstrap.Modal(searchModal) : null;
            }
            initSearchModal();
            
            // Ctrl+K kısayolu - Modal'ı aç
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    if (searchModalInstance) {
                        searchModalInstance.show();
                    }
                }
            });
            
            // Modal açıldığında input'a focus
            if (searchModal) {
                searchModal.addEventListener('shown.bs.modal', function() {
                    searchInput.focus();
                    searchInput.select();
                });
            }
            
            // Arama input'u - debounce
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    searchResults.classList.remove('show');
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            });
            
            // Modal'da sonuçlar her zaman görünür, class toggle'a gerek yok
            
            // AJAX arama fonksiyonu
            function performSearch(query) {
                fetch(`{{ route('search') }}?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    renderResults(data.results);
                })
                .catch(error => {
                    console.error('Arama hatası:', error);
                });
            }
            
            // Sonuçları render et
            function renderResults(results) {
                if (!results || results.length === 0) {
                    searchResults.innerHTML = `
                        <div class="px-3 py-2 text-white-50 text-center">
                            <i class="bi bi-search me-1"></i>Sonuç bulunamadı
                        </div>
                    `;
                    return;
                }
                
                let html = '';
                let currentType = '';
                
                results.forEach(item => {
                    // Kategori başlığı
                    if (item.type !== currentType) {
                        currentType = item.type;
                        html += `<h6 class="px-3 py-2 mb-0 text-white-50 small text-uppercase">${item.type_label}</h6>`;
                    }
                    
                    html += `
                        <a href="${item.url}" class="d-flex align-items-center gap-2 py-2 px-3 text-white text-decoration-none search-result-item" style="border-radius: 0.5rem;">
                            <i class="bi ${item.icon} text-white-50"></i>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-medium text-truncate">${item.title}</div>
                                <small class="text-white-50 text-truncate d-block">${item.subtitle}</small>
                            </div>
                            <span class="badge bg-${item.badge_class} ms-auto">${item.badge}</span>
                        </a>
                    `;
                });
                
                searchResults.innerHTML = html;
            }
        });
    </script>
    
    {{-- Bildirim Zili JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBell = document.getElementById('notificationBell');
            const notificationBadge = document.getElementById('notificationBadge');
            const notificationList = document.getElementById('notificationList');
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            
            // Bildirimleri yükle
            function loadNotifications() {
                fetch('{{ route('notifications.index') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    renderNotifications(data.notifications);
                    updateBadge(data.unread_count);
                })
                .catch(error => {
                    console.error('Bildirim hatası:', error);
                });
            }
            
            // Badge güncelle
            function updateBadge(count) {
                if (count > 0) {
                    notificationBadge.textContent = count > 99 ? '99+' : count;
                    notificationBadge.classList.remove('d-none');
                } else {
                    notificationBadge.classList.add('d-none');
                }
            }
            
            // Bildirimleri render et
            function renderNotifications(notifications) {
                if (!notifications || notifications.length === 0) {
                    notificationList.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                            <small>Bildirim bulunmuyor</small>
                        </div>
                    `;
                    return;
                }
                
                let html = '';
                notifications.forEach(n => {
                    const unreadClass = n.read ? '' : 'bg-light';
                    html += `
                        <a href="${n.link || '#'}" 
                           class="dropdown-item d-flex gap-3 py-3 ${unreadClass}"
                           data-notification-id="${n.id}">
                            <div class="flex-shrink-0">
                                <i class="bi ${n.icon_class} fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">${n.title}</div>
                                <small class="text-muted d-block">${n.message}</small>
                                <small class="text-muted">${n.time_ago}</small>
                            </div>
                        </a>
                    `;
                });
                
                notificationList.innerHTML = html;
            }
            
            // Tümünü okundu işaretle
            markAllReadBtn?.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateBadge(0);
                        loadNotifications();
                    }
                });
            });
            
            // Dropdown açıldığında bildirimleri yükle
            notificationBell?.addEventListener('click', function() {
                loadNotifications();
            });
            
            // İlk yüklemede badge'i güncelle
            loadNotifications();
            
            // Her 60 saniyede bir kontrol et
            setInterval(loadNotifications, 60000);
            
            // --- DROPDOWN AÇILDIĞINDA ARAMA KUTUSUNU GİZLE ---
            const searchContainerEl = document.getElementById('globalSearchContainer');
            if (searchContainerEl) {
                // Tüm navbar dropdown'larını dinle
                document.querySelectorAll('.app-navbar .nav-item.dropdown').forEach(dropdown => {
                    dropdown.addEventListener('show.bs.dropdown', function() {
                        // Dropdown açılırken arama kutusunu gizle
                        searchContainerEl.style.visibility = 'hidden';
                    });
                    dropdown.addEventListener('hide.bs.dropdown', function() {
                        // Dropdown kapanırken arama kutusunu göster
                        searchContainerEl.style.visibility = 'visible';
                    });
                });
            }
        });
    </script>

    {{-- Mobil Bottom Navigation --}}
    <nav class="mobile-bottom-nav d-lg-none">
        <a href="{{ route('dashboard') }}" class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-house"></i>
            <span>Ana Sayfa</span>
        </a>
        <a href="{{ route('invoices.index') }}" class="mobile-nav-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i>
            <span>Faturalar</span>
        </a>
        <a href="{{ route('tax-declarations.index') }}" class="mobile-nav-item {{ request()->routeIs('tax-declarations.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i>
            <span>Beyanname</span>
        </a>
        <a href="{{ route('payments.index') }}" class="mobile-nav-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i>
            <span>Tahsilat</span>
        </a>
        <button type="button" class="mobile-nav-item" data-bs-toggle="offcanvas" data-bs-target="#mobileMoreMenu">
            <i class="bi bi-grid-3x3-gap"></i>
            <span>Menü</span>
        </button>
    </nav>
    
    {{-- Mobil Daha Fazla Menüsü (Offcanvas) --}}
    <div class="offcanvas offcanvas-bottom d-lg-none" tabindex="-1" id="mobileMoreMenu" aria-labelledby="mobileMoreMenuLabel" style="height: auto; max-height: 70vh;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="mobileMoreMenuLabel">
                <i class="bi bi-grid-3x3-gap me-2"></i>Menü
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
        </div>
        <div class="offcanvas-body">
            <div class="row g-3">
                {{-- Firmalar --}}
                <div class="col-4">
                    <a href="{{ route('firms.index') }}" class="mobile-menu-tile">
                        <div class="mobile-menu-icon bg-primary bg-opacity-10">
                            <i class="bi bi-building text-primary"></i>
                        </div>
                        <span>Firmalar</span>
                    </a>
                </div>
                
                {{-- Raporlar --}}
                <div class="col-4">
                    <a href="{{ route('reports.balance') }}" class="mobile-menu-tile">
                        <div class="mobile-menu-icon bg-info bg-opacity-10">
                            <i class="bi bi-bar-chart text-info"></i>
                        </div>
                        <span>Raporlar</span>
                    </a>
                </div>
                
                {{-- GİB Takvimi --}}
                <div class="col-4">
                    <a href="{{ route('tax-calendar.index') }}" class="mobile-menu-tile">
                        <div class="mobile-menu-icon bg-indigo bg-opacity-10" style="background-color: rgba(102, 16, 242, 0.1);">
                            <i class="bi bi-calendar-event text-indigo" style="color: #6610f2;"></i>
                        </div>
                        <span>GİB Takvimi</span>
                    </a>
                </div>
                
                {{-- Yeni Fatura --}}
                <div class="col-4">
                    <a href="{{ route('invoices.create') }}" class="mobile-menu-tile">
                        <div class="mobile-menu-icon bg-success bg-opacity-10">
                            <i class="bi bi-plus-circle text-success"></i>
                        </div>
                        <span>Yeni Fatura</span>
                    </a>
                </div>
                
                {{-- Tahsilat Kaydet --}}
                <div class="col-4">
                    <a href="{{ route('payments.create') }}" class="mobile-menu-tile">
                        <div class="mobile-menu-icon bg-success bg-opacity-10">
                            <i class="bi bi-cash-coin text-success"></i>
                        </div>
                        <span>Tahsilat</span>
                    </a>
                </div>
                
                {{-- Yeni Firma --}}
                <div class="col-4">
                    <a href="{{ route('firms.create') }}" class="mobile-menu-tile">
                        <div class="mobile-menu-icon bg-primary bg-opacity-10">
                            <i class="bi bi-building-add text-primary"></i>
                        </div>
                        <span>Yeni Firma</span>
                    </a>
                </div>
                
                {{-- Ayarlar --}}
                <div class="col-4">
                    <a href="{{ route('settings.edit') }}" class="mobile-menu-tile">
                        <div class="mobile-menu-icon bg-secondary bg-opacity-10">
                            <i class="bi bi-gear text-secondary"></i>
                        </div>
                        <span>Ayarlar</span>
                    </a>
                </div>
                
                {{-- Yardım --}}
                <div class="col-4">
                    <a href="{{ route('help') }}" class="mobile-menu-tile">
                        <div class="mobile-menu-icon bg-warning bg-opacity-10">
                            <i class="bi bi-question-circle text-warning"></i>
                        </div>
                        <span>Yardım</span>
                    </a>
                </div>
                
                @if($isAdmin ?? false)
                {{-- Kullanıcılar --}}
                <div class="col-4">
                    <a href="{{ route('users.index') }}" class="mobile-menu-tile">
                        <div class="mobile-menu-icon bg-dark bg-opacity-10">
                            <i class="bi bi-people text-dark"></i>
                        </div>
                        <span>Kullanıcılar</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <style>
    .mobile-menu-tile {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        border-radius: 0.75rem;
        text-decoration: none;
        color: #212529;
        transition: background-color 0.2s;
    }
    
    .mobile-menu-tile:hover {
        background-color: #f8f9fa;
        color: #212529;
    }
    
    .mobile-menu-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    
    .mobile-menu-tile span {
        font-size: 0.75rem;
        font-weight: 500;
        text-align: center;
    }
    </style>
</body>
</html>
