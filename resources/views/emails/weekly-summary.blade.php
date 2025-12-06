@component('mail::message')
# Haftalık Özet Raporu

Bu hafta içindeki işlemlerin özeti:

## 📊 Genel Bakış

@component('mail::table')
| Metrik | Değer |
|:-------|------:|
| Toplam Aktif Firma | {{ number_format($stats['total_firms'], 0, ',', '.') }} |
| Bu Hafta Yeni Fatura | {{ number_format($stats['new_invoices_count'], 0, ',', '.') }} adet |
| Yeni Fatura Tutarı | {{ number_format($stats['new_invoices_amount'], 2, ',', '.') }} ₺ |
| Bu Hafta Tahsilat | {{ number_format($stats['payments_count'], 0, ',', '.') }} adet |
| Tahsilat Tutarı | {{ number_format($stats['payments_amount'], 2, ',', '.') }} ₺ |
| Bekleyen Fatura | {{ number_format($stats['pending_invoices_count'], 0, ',', '.') }} adet |
| Bekleyen Tutar | {{ number_format($stats['pending_invoices_amount'], 2, ',', '.') }} ₺ |
@endcomponent

@if(count($upcomingDeclarations) > 0)
## 📅 Yaklaşan Beyannameler (14 gün içinde)

@component('mail::table')
| Firma | Beyanname | Son Gün | Kalan |
|:------|:----------|:--------|------:|
@foreach($upcomingDeclarations as $d)
| {{ $d['firm'] }} | {{ $d['form'] }} | {{ $d['due_date'] }} | {{ $d['days_left'] }} gün |
@endforeach
@endcomponent
@endif

@if(count($overdueInvoices) > 0)
## ⚠️ Gecikmiş Faturalar

@component('mail::table')
| Firma | Fatura | Tutar | Vade |
|:------|:-------|------:|:-----|
@foreach($overdueInvoices as $i)
| {{ $i['firm'] }} | {{ $i['invoice_no'] }} | {{ $i['amount'] }} ₺ | {{ $i['due_date'] }} |
@endforeach
@endcomponent
@endif

@if(count($recentPayments) > 0)
## ✅ Bu Hafta Alınan Tahsilatlar

@component('mail::table')
| Firma | Tutar | Tarih |
|:------|------:|:------|
@foreach($recentPayments as $p)
| {{ $p['firm'] }} | {{ $p['amount'] }} ₺ | {{ $p['date'] }} |
@endforeach
@endcomponent
@endif

@component('mail::button', ['url' => route('dashboard')])
Dashboard'u Görüntüle
@endcomponent

Haftalık özet raporu otomatik olarak gönderilmektedir.

Teşekkürler,<br>
{{ config('app.name') }}
@endcomponent
