@php
    use App\Support\Format;
@endphp

@component('mail::message')
# {{ $firm->name }} Hesap Ekstresi

**Dönem:** {{ $summary['start']->format('d.m.Y') }} &mdash; {{ $summary['end']->format('d.m.Y') }}

**Başlangıç Bakiyesi:** {{ Format::money($summary['initial_balance']) }}  
**Toplam Borç:** {{ Format::money($summary['total_debit']) }}  
**Toplam Tahsilat:** {{ Format::money($summary['total_credit']) }}  
**Kapanış Bakiyesi:** {{ Format::money($summary['closing_balance']) }}

Ekli PDF dosyasında ilgili döneme ait tüm hareketleri bulabilirsiniz.

Teşekkürler,  
{{ $settings['company_name'] ?? config('app.name') }}
@endcomponent
