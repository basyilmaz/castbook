<?php

namespace App\Enums;

enum DeclarationType: string
{
    case NORMAL = 'normal';
    case SUPPLEMENTARY = 'supplementary';
    case CORRECTION = 'correction';
    case REFUND = 'refund';
    case PROVISIONAL = 'provisional';
    case FINAL = 'final';

    public function label(): string
    {
        return match($this) {
            self::NORMAL => 'Normal Beyanname',
            self::SUPPLEMENTARY => 'Ek Beyanname',
            self::CORRECTION => 'Düzeltme',
            self::REFUND => 'İade',
            self::PROVISIONAL => 'Geçici Vergi',
            self::FINAL => 'Kesin Beyanname',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::NORMAL => 'Standart dönemsel beyanname',
            self::SUPPLEMENTARY => 'Eksik veya unutulan bilgiler için',
            self::CORRECTION => 'Hatalı beyanname düzeltmesi',
            self::REFUND => 'KDV iadesi talebi',
            self::PROVISIONAL => 'Yıl içi geçici vergi beyannamesi',
            self::FINAL => 'Yıl sonu kesin beyanname',
        };
    }
}
