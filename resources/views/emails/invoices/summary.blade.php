@php
    use App\Support\Format;
@endphp

<x-mail::message>
# {{ $period->format('m/Y') }} Fatura Otomasyonu Özeti

Toplam **{{ $createdCount }}** adet fatura oluşturuldu.
Toplam tutar: **{{ Format::money($totalAmount) }}**

@if (! empty($invoices))
<x-mail::table>
| Fatura | Firma | Tarih | Tutar |
| :--- | :--- | :--- | ---: |
@foreach ($invoices as $invoice)
| #{{ $invoice['invoice_id'] }} | {{ $invoice['firm_name'] }} | {{ optional($invoice['date'])->format('d.m.Y') }} | {{ Format::money($invoice['amount']) }} |
@endforeach
</x-mail::table>
@else
> Bu dönemde faturası otomatik oluşturulan firma bulunmuyor.
@endif

Başarımsız denetim ve tahsilat süreçlerinizde başarılar dileriz.

Teşekkürler,<br>
{{ config('app.name') }}
<!-- Toplam <strong>{{ $createdCount }}</strong> adet fatura oluşturuldu. -->
<!-- Toplam tutar: <strong>{{ Format::money($totalAmount) }}</strong> -->
</x-mail::message>
