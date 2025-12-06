<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceExtraValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'extra_field_id',
        'value',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function field()
    {
        return $this->belongsTo(InvoiceExtraField::class, 'extra_field_id');
    }
}
