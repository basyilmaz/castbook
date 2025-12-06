<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TaxForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'frequency',
        'default_due_day',
        'is_active',
        'applicable_to',
        'auto_assign',
        'gib_code', // GİB takvim kodu eşleştirmesi
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'applicable_to' => 'array',
        'auto_assign' => 'boolean',
    ];

    /**
     * TaxForm kodu -> GİB TaxCalendar kodu eşleştirmesi
     */
    public static function gibCodeMapping(): array
    {
        return [
            'KDV1' => 'KDV',
            'KDV2' => 'KDV',
            'KDV' => 'KDV',
            'MUHTASAR' => 'MUHTASAR',
            'DAMGA' => 'DAMGA',
            'BA' => 'BA_BS',
            'BS' => 'BA_BS',
            'BA-BS' => 'BA_BS',
            'BABS' => 'BA_BS',
            'GECICI' => 'GECICI_VERGI',
            'GECICI_VERGI' => 'GECICI_VERGI',
            'KURUMLAR' => 'KURUMLAR',
            'GELIR' => 'GELIR',
            'KKDF' => 'KKDF',
        ];
    }

    /**
     * Bu form için GİB takvim kodunu getir
     */
    public function getGibCodeAttribute(): ?string
    {
        // Önce manuel atanmış kodu kontrol et
        if (!empty($this->attributes['gib_code'])) {
            return $this->attributes['gib_code'];
        }

        // Otomatik eşleştirme dene
        $mapping = self::gibCodeMapping();
        $code = strtoupper($this->code ?? '');

        return $mapping[$code] ?? null;
    }

    /**
     * Belirli bir dönem için GİB resmi son tarihini getir
     * 
     * Frekansa göre arama ayı:
     * - Aylık: 1 ay sonra (Kasım dönemi → Aralık'ta ara)
     * - Çeyreklik: 2 ay sonra (Q3 Eylül → Kasım'da ara)
     * - Yıllık: Özel (Kurumlar → Nisan, Gelir → Mart/Temmuz)
     */
    public function getOfficialDueDate(Carbon $periodEnd): ?Carbon
    {
        $gibCode = $this->gib_code;
        if (!$gibCode) {
            return null;
        }

        // Frekansa göre kaç ay sonra aranacak?
        $monthsToAdd = match ($this->frequency) {
            'monthly' => 1,
            'quarterly' => 2,
            'yearly' => 4, // Yıllık için özel mantık gerekebilir
            default => 1,
        };

        // Beyanname döneminden sonraki ilgili ayda son tarih aranıyor
        $searchMonth = $periodEnd->copy()->addMonths($monthsToAdd);

        $calendarEntry = TaxCalendar::query()
            ->where('code', $gibCode)
            ->where('year', $searchMonth->year)
            ->where('month', $searchMonth->month)
            ->first();

        return $calendarEntry?->due_date;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function declarations()
    {
        return $this->hasMany(TaxDeclaration::class);
    }

    public function firms()
    {
        return $this->belongsToMany(Firm::class, 'firm_tax_forms')
            ->withPivot('custom_due_day', 'is_active')
            ->withTimestamps();
    }
}
