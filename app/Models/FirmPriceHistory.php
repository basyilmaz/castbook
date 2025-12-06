<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class FirmPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'amount',
        'valid_from',
        'valid_to',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActiveOn($query, Carbon $date)
    {
        $date = $date->copy()->startOfDay();

        return $query->where('valid_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $date);
            });
    }
}
