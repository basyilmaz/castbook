<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, mixed $value): Setting
    {
        return static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function getPaymentMethods(): array
    {
        $raw = static::getValue('payment_methods');

        if (empty($raw)) {
            return ['Nakit', 'Banka'];
        }

        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            $methods = $decoded;
        } else {
            $methods = preg_split('/[\r\n,]+/', (string) $raw);
        }

        $normalized = collect($methods)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return ! empty($normalized) ? $normalized : ['Nakit', 'Banka'];
    }

    public static function setPaymentMethods(array $methods): void
    {
        $normalized = collect($methods)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        static::setValue('payment_methods', json_encode($normalized, JSON_UNESCAPED_UNICODE));
    }

    public static function getInvoiceNotificationRecipients(): array
    {
        $raw = static::getValue('invoice_notify_recipients');

        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            $emails = $decoded;
        } else {
            $emails = preg_split('/[\r\n,]+/', (string) $raw);
        }

        return collect($emails)
            ->map(fn ($item) => strtolower(trim((string) $item)))
            ->filter(fn ($item) => filter_var($item, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->toArray();
    }

    public static function setInvoiceNotificationRecipients(array $emails): void
    {
        $normalized = collect($emails)
            ->map(fn ($item) => strtolower(trim((string) $item)))
            ->filter(fn ($item) => filter_var($item, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->toArray();

        static::setValue('invoice_notify_recipients', json_encode($normalized, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Genel bildirim alıcılarını al (ödeme ve beyanname hatırlatmaları için)
     */
    public static function getNotificationRecipients(): array
    {
        $raw = static::getValue('notification_recipients');

        if (empty($raw)) {
            // Fallback: Fatura bildirim alıcılarını kullan
            return static::getInvoiceNotificationRecipients();
        }

        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            $emails = $decoded;
        } else {
            $emails = preg_split('/[\r\n,]+/', (string) $raw);
        }

        return collect($emails)
            ->map(fn ($item) => strtolower(trim((string) $item)))
            ->filter(fn ($item) => filter_var($item, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->toArray();
    }
}
