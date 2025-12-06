@php
    use App\Support\Format;
@endphp

<x-mail::message>
@if($type === 'overdue')
# ⚠️ Gecikmiş Ödeme Bildirimi

**{{ $firmName }}** firmasına ait aşağıdaki faturalar vadesi geçmiş durumda.
@else
# 📅 Yaklaşan Ödeme Hatırlatması

**{{ $firmName }}** firmasına ait aşağıdaki faturaların vadesi yaklaşıyor.
@endif

@if(!empty($invoices) && count($invoices) > 0)
<x-mail::table>
| Fatura No | Fatura Tarihi | Vade Tarihi | Tutar |
| :--- | :---: | :---: | ---: |
@foreach ($invoices as $invoice)
| {{ $invoice->official_number ?? '#'.$invoice->id }} | {{ optional($invoice->date)->format('d.m.Y') }} | {{ optional($invoice->due_date)->format('d.m.Y') ?? '-' }} | {{ Format::money($invoice->amount) }} |
@endforeach
</x-mail::table>

**Toplam Tutar:** {{ Format::money($totalAmount) }}
@endif

@if($type === 'overdue')
> Lütfen en kısa sürede ödeme yapılmasını hatırlatınız.
@else
> Vade tarihi yaklaşan faturalar için müşterilerinizi bilgilendirmenizi öneririz.
@endif

<x-mail::button :url="config('app.url').'/invoices'" color="primary">
Faturaları Görüntüle
</x-mail::button>

Saygılarımızla,<br>
{{ config('app.name') }}
</x-mail::message>
