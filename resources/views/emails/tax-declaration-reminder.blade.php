<x-mail::message>
@if($type === 'overdue')
# 🚨 Gecikmiş Beyannameler

Aşağıdaki beyannameler gecikmiş durumda. Acil işlem gereklidir.
@else
# 🗓️ Yaklaşan Beyanname Bildirimi

Aşağıdaki beyannamelerin son ödeme tarihine **{{ $daysUntilDue }} gün** kalmıştır.
@endif

@if(!empty($declarations) && count($declarations) > 0)
<x-mail::table>
| Firma | Beyanname | Dönem | Son Gün | Durum |
| :--- | :--- | :---: | :---: | :---: |
@foreach ($declarations as $dec)
| {{ $dec->firm->name ?? 'Belirtilmemiş' }} | {{ $dec->taxForm->name ?? $dec->taxForm->code ?? 'Belirtilmemiş' }} | {{ $dec->period_label }} | {{ optional($dec->due_date)->format('d.m.Y') }} | {{ $dec->status === 'pending' ? 'Bekliyor' : ($dec->status === 'filed' ? 'Dosyalandı' : $dec->status) }} |
@endforeach
</x-mail::table>
@endif

@if($type === 'overdue')
> ⚠️ **Dikkat:** Geciken beyannameler için ceza uygulanabilir. Lütfen müşterilerinizi acilen bilgilendirin.
@else
> Beyannamelerin zamanında dosyalanması için müşterilerinizi bilgilendirmenizi öneririz.
@endif

<x-mail::button :url="config('app.url').'/tax-declarations'" color="primary">
Beyannameleri Görüntüle
</x-mail::button>

Saygılarımızla,<br>
{{ config('app.name') }}
</x-mail::message>
