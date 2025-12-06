<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'sort_order',
        'item_type',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($item) {
            // Amount otomatik hesapla
            $item->amount = $item->quantity * $item->unit_price;
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
