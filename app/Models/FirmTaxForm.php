<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirmTaxForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'tax_form_id',
        'custom_due_day',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function taxForm()
    {
        return $this->belongsTo(TaxForm::class);
    }
}
