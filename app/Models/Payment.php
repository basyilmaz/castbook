<?php

namespace App\Models;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    use \App\Traits\Auditable;

    protected $fillable = [
        'firm_id',
        'invoice_id',
        'amount',
        'method',
        'date',
        'reference',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'sourceable');
    }
}
