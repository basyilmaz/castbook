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
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
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
}
