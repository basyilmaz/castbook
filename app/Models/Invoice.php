<?php

namespace App\Models;

use App\Models\Firm;
use App\Models\Payment;
use App\Models\InvoiceExtraValue;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Invoice extends Model
{
    use HasFactory;
    use \App\Traits\Auditable;

    protected $fillable = [
        'firm_id',
        'date',
        'due_date',
        'amount',
        'description',
        'official_number',
        'status',
        'paid_at',
        // KDV alanları
        'vat_rate',
        'vat_included',
        'subtotal',
        'vat_amount',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_included' => 'boolean',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'sourceable');
    }

    public function extraValues()
    {
        return $this->hasMany(InvoiceExtraValue::class);
    }

    public function lineItems()
    {
        return $this->hasMany(InvoiceLineItem::class)->orderBy('sort_order');
    }

    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial']);
    }

    public function getIsOverdueAttribute(): bool
    {
        $referenceDate = $this->due_date ?? $this->date;

        return $this->status !== 'paid' && $referenceDate instanceof Carbon
            ? $referenceDate->isPast()
            : false;
    }

    public function getAmountPaidAttribute(): float
    {
        if ($this->relationLoaded('payments_sum_amount')) {
            return (float) ($this->getAttribute('payments_sum_amount') ?? 0);
        }

        return (float) $this->payments()->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount - $this->amount_paid);
    }

    public function refreshPaymentStatus(): void
    {
        $paidTotal = $this->payments()->sum('amount');
        $remaining = (float) $this->amount - (float) $paidTotal;

        if ($paidTotal <= 0) {
            $this->update([
                'status' => 'unpaid',
                'paid_at' => null,
            ]);

            return;
        }

        if ($remaining <= 0.01) {
            $latestPaymentDate = $this->payments()->latest('date')->value('date');

            $this->update([
                'status' => 'paid',
                'paid_at' => $latestPaymentDate
                    ? Carbon::parse($latestPaymentDate)->endOfDay()
                    : now(),
            ]);

            return;
        }

        $this->update([
            'status' => 'partial',
            'paid_at' => null,
        ]);
    }

    /**
     * KDV tutarlarını hesapla ve ayarla
     * 
     * @param float $amount Toplam tutar (KDV dahil veya hariç)
     * @param float $vatRate KDV oranı (ör: 20.00)
     * @param bool $vatIncluded Tutar KDV dahil mi?
     */
    public function calculateVat(float $amount, float $vatRate, bool $vatIncluded = true): void
    {
        $this->vat_rate = $vatRate;
        $this->vat_included = $vatIncluded;

        if ($vatIncluded) {
            // KDV dahil: amount toplam, subtotal ve vat_amount hesaplanır
            $this->amount = $amount;
            $this->subtotal = round($amount / (1 + $vatRate / 100), 2);
            $this->vat_amount = round($amount - $this->subtotal, 2);
        } else {
            // KDV hariç: amount net tutar, toplam = net + kdv
            $this->subtotal = $amount;
            $this->vat_amount = round($amount * $vatRate / 100, 2);
            $this->amount = round($amount + $this->vat_amount, 2);
        }
    }

    /**
     * Formatlı KDV oranı (ör: "%20")
     */
    public function getFormattedVatRateAttribute(): string
    {
        $rate = (float) ($this->vat_rate ?? 0);
        return '%' . number_format($rate, 0);
    }

    /**
     * KDV tipi etiketi
     */
    public function getVatTypeLabel(): string
    {
        return $this->vat_included ? 'KDV Dahil' : 'KDV Hariç';
    }
}
