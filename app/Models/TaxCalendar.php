<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TaxCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'day',
        'due_date',
        'code',
        'name',
        'description',
        'period_label',
        'frequency',
        'applicable_to',
        'is_active',
    ];

    protected $casts = [
        'due_date' => 'date',
        'applicable_to' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Aktif kayıtlar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Belirli bir yıl için
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Belirli bir ay için
     */
    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Bugün ve sonrası
     */
    public function scopeUpcoming($query, ?Carbon $from = null)
    {
        $date = $from ?? Carbon::today();
        return $query->where('due_date', '>=', $date);
    }

    /**
     * Belirli gün sayısı içindeki
     */
    public function scopeWithinDays($query, int $days, ?Carbon $from = null)
    {
        $start = $from ?? Carbon::today();
        $end = $start->copy()->addDays($days);
        return $query->whereBetween('due_date', [$start, $end]);
    }

    /**
     * Bugün son günü olanlar
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', Carbon::today());
    }

    /**
     * Belirli kod için
     */
    public function scopeForCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Şirket türüne uygulanabilir
     */
    public function scopeApplicableTo($query, string $companyType)
    {
        return $query->where(function ($q) use ($companyType) {
            $q->whereNull('applicable_to')
              ->orWhereJsonContains('applicable_to', $companyType);
        });
    }

    /**
     * Gecikmiş mi?
     */
    public function isOverdue(): bool
    {
        return $this->due_date->lt(Carbon::today());
    }

    /**
     * Bugün mü?
     */
    public function isToday(): bool
    {
        return $this->due_date->isToday();
    }

    /**
     * Kalan gün sayısı
     */
    public function daysUntilDue(): int
    {
        return (int) Carbon::today()->diffInDays($this->due_date, false);
    }

    /**
     * Renk class'ı (badge için)
     */
    public function getBadgeClassAttribute(): string
    {
        $days = $this->daysUntilDue();

        if ($days < 0) {
            return 'danger'; // Gecikmiş
        } elseif ($days === 0) {
            return 'danger'; // Bugün
        } elseif ($days <= 3) {
            return 'warning'; // Yaklaşıyor
        } else {
            return 'info'; // Normal
        }
    }

    /**
     * Beyanname kodu için ikon
     */
    public function getIconAttribute(): string
    {
        return match ($this->code) {
            'KDV' => 'bi-receipt',
            'MUHTASAR' => 'bi-file-earmark-text',
            'GECICI_VERGI' => 'bi-calendar-check',
            'DAMGA' => 'bi-stamp',
            'BA_BS' => 'bi-file-earmark-spreadsheet',
            'KURUMLAR' => 'bi-building',
            'GELIR' => 'bi-person',
            default => 'bi-file-earmark',
        };
    }

    /**
     * Kısa kod açıklaması
     */
    public static function codeLabels(): array
    {
        return [
            'KDV' => 'Katma Değer Vergisi',
            'MUHTASAR' => 'Muhtasar ve Prim Hizmet',
            'GECICI_VERGI' => 'Geçici Vergi',
            'DAMGA' => 'Damga Vergisi',
            'BA_BS' => 'Ba-Bs Formları',
            'KURUMLAR' => 'Kurumlar Vergisi',
            'GELIR' => 'Gelir Vergisi',
            'KKDF' => 'KKDF',
            'NOTER' => 'Noterler Makbuz Bildirimi',
            'BANKA_SIGORTA' => 'Banka ve Sigorta Muameleleri',
            'OTV' => 'Özel Tüketim Vergisi',
            'OZEL_ILETISIM' => 'Özel İletişim Vergisi',
            'SERBEST_MESLEK' => 'Serbest Meslek Kazancı',
        ];
    }
}
