<?php

namespace App\Models;

use App\Models\Firm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'type',
        'amount',
        'date',
        'description',
        'sourceable_type',
        'sourceable_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function sourceable()
    {
        return $this->morphTo();
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function getSignedAmountAttribute(): float
    {
        $sign = $this->type === 'credit' ? -1 : 1;

        return (float) ($sign * $this->amount);
    }
}
