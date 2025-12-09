<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TaxDeclaration extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'tax_form_id',
        'period_start',
        'period_end',
        'period_label',
        'declaration_type',
        'sequence_number',
        'due_date',
        'status',
        'filed_at',
        'paid_at',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'due_date' => 'datetime',
        'filed_at' => 'datetime',
        'paid_at' => 'datetime',
        'declaration_type' => \App\Enums\DeclarationType::class,
    ];

    public function firm()
    {
        return $this->belongsTo(Firm::class);
    }

    public function taxForm()
    {
        return $this->belongsTo(TaxForm::class);
    }

    public function isOverdue(): bool
    {
        $today = Carbon::today();

        return $this->due_date && $today->greaterThan(Carbon::parse($this->due_date))
            && $this->status === 'pending';
    }

    /**
     * GİB resmi son tarihini getir
     */
    public function getOfficialDueDateAttribute(): ?Carbon
    {
        if (!$this->taxForm || !$this->period_end) {
            return null;
        }

        return $this->taxForm->getOfficialDueDate($this->period_end);
    }

    /**
     * Beyanname tarihi GİB resmi tarihiyle eşleşiyor mu?
     */
    public function getMatchesOfficialDateAttribute(): bool
    {
        $official = $this->official_due_date;
        
        if (!$official || !$this->due_date) {
            return false;
        }

        return $this->due_date->isSameDay($official);
    }

    /**
     * Resmi tarihle kaç gün fark var?
     */
    public function getOfficialDateDiffAttribute(): ?int
    {
        $official = $this->official_due_date;
        
        if (!$official || !$this->due_date) {
            return null;
        }

        return (int) $this->due_date->diffInDays($official, false);
    }

    public function getFullLabel(): string
    {
        $label = $this->taxForm->code . ' ' . $this->period_label;
        
        if ($this->declaration_type !== \App\Enums\DeclarationType::NORMAL) {
            $label .= ' - ' . $this->declaration_type->label();
        }
        
        if ($this->sequence_number > 1) {
            $label .= ' (Ek-' . $this->sequence_number . ')';
        }
        
        return $label;
    }
}
