<?php

namespace App\Support;

/**
 * Beyanname durumları için merkezi tanımlar
 * Tüm view'larda ve controller'larda bu class kullanılmalıdır
 */
class DeclarationStatus
{
    /**
     * Tüm beyanname durumları
     */
    public const STATUSES = [
        'pending' => [
            'label' => 'Bekliyor',
            'class' => 'warning text-dark',
            'icon' => 'bi-hourglass-split',
            'color' => '#ffc107',
        ],
        'filed' => [
            'label' => 'Dosyalandı',
            'class' => 'primary',
            'icon' => 'bi-check-circle',
            'color' => '#0d6efd',
        ],
        'paid' => [
            'label' => 'Ödendi',
            'class' => 'success',
            'icon' => 'bi-check-circle-fill',
            'color' => '#198754',
        ],
        'not_required' => [
            'label' => 'Gerekli Değil',
            'class' => 'secondary',
            'icon' => 'bi-dash-circle',
            'color' => '#6c757d',
        ],
    ];

    /**
     * Durum etiketini döndür
     */
    public static function label(string $status): string
    {
        return self::STATUSES[$status]['label'] ?? $status;
    }

    /**
     * Durum CSS class'ını döndür
     */
    public static function class(string $status): string
    {
        return self::STATUSES[$status]['class'] ?? 'secondary';
    }

    /**
     * Durum ikonunu döndür
     */
    public static function icon(string $status): string
    {
        return self::STATUSES[$status]['icon'] ?? 'bi-question-circle';
    }

    /**
     * Durum rengini döndür
     */
    public static function color(string $status): string
    {
        return self::STATUSES[$status]['color'] ?? '#6c757d';
    }

    /**
     * Tüm durumları select için array olarak döndür
     */
    public static function options(): array
    {
        return collect(self::STATUSES)->mapWithKeys(function ($data, $key) {
            return [$key => $data['label']];
        })->toArray();
    }

    /**
     * Tüm durumları config array olarak döndür
     */
    public static function all(): array
    {
        return self::STATUSES;
    }
}
