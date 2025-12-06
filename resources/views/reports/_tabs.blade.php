@php
    $tabs = [
        'reports.balance' => 'Müşteri Bakiyeleri',
        'reports.collections' => 'Aylık Tahsilat',
        'reports.overdues' => 'Geciken Ödemeler',
        'reports.invoices' => 'Fatura Durumu',
    ];
@endphp

<ul class="nav nav-pills mb-3">
    @foreach ($tabs as $route => $label)
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}"
               href="{{ route($route) }}">
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>
