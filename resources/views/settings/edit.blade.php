@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-semibold mb-0">Sistem Ayarları</h4>
            <small class="text-muted">Şirket bilgileri, fatura ayarları ve beyanname yönetimi</small>
        </div>
        <div class="d-flex gap-2">
            @if($isAdmin ?? false)
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-people me-1"></i>Kullanıcılar
            </a>
            @endif
            <a href="{{ route('settings.notifications') }}" class="btn btn-outline-primary">
                <i class="bi bi-bell me-1"></i>Bildirim Ayarları
            </a>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" 
                    type="button" role="tab" aria-controls="general" aria-selected="true">
                Genel Ayarlar
            </button>
        </li>
        @if($isAdmin ?? false)
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="invoice-fields-tab" data-bs-toggle="tab" data-bs-target="#invoice-fields" 
                    type="button" role="tab" aria-controls="invoice-fields" aria-selected="false">
                Fatura Ekstra Alanları
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tax-management-tab" data-bs-toggle="tab" data-bs-target="#tax-management" 
                    type="button" role="tab" aria-controls="tax-management" aria-selected="false">
                Beyanname Yönetimi
            </button>
        </li>
        @endif
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="settingsTabContent">
        {{-- General Settings Tab --}}
        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
            @include('settings.tabs.general')
        </div>

        {{-- Invoice Extra Fields Tab --}}
        @if($isAdmin ?? false)
        <div class="tab-pane fade" id="invoice-fields" role="tabpanel" aria-labelledby="invoice-fields-tab">
            @include('settings.tabs.invoice-fields')
        </div>

        {{-- Tax Management Tab --}}
        <div class="tab-pane fade" id="tax-management" role="tabpanel" aria-labelledby="tax-management-tab">
            @include('settings.tabs.tax-management')
        </div>
        @endif
    </div>
</div>

{{-- Tab State Management --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Restore active tab from localStorage
    const activeTab = localStorage.getItem('settingsActiveTab');
    if (activeTab) {
        const tabTrigger = document.querySelector(`button[data-bs-target="${activeTab}"]`);
        if (tabTrigger) {
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    }

    // Save active tab to localStorage
    document.querySelectorAll('#settingsTabs button[data-bs-toggle="tab"]').forEach(button => {
        button.addEventListener('shown.bs.tab', function (event) {
            localStorage.setItem('settingsActiveTab', event.target.dataset.bsTarget);
        });
    });
});
</script>
@endsection
