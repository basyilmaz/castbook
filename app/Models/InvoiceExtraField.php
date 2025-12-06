<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceExtraField extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'name',
        'label',
        'type',
        'options',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function values()
    {
        return $this->hasMany(InvoiceExtraValue::class, 'extra_field_id');
    }

    public function isSelect(): bool
    {
        return $this->type === 'select';
    }

    public function optionsList(): array
    {
        if (! $this->options) {
            return [];
        }

        return collect(explode(',', $this->options))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->toArray();
    }
}
