<?php

namespace App\Models;

use App\Models\FirmPriceHistory;
use App\Models\Invoice;
use App\Models\TaxDeclaration;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Firm extends Model
{
    use HasFactory;
    use SoftDeletes;
    use \App\Traits\Auditable;

    protected $fillable = [
        'name',
        'company_type',
        'tax_no',
        'contact_person',
        'contact_email',
        'contact_phone',
        'monthly_fee',
        'status',
        'notes',
        'contract_start_at',
        'initial_debt_synced_at',
    ];

    protected $casts = [
        'company_type' => \App\Enums\CompanyType::class,
        'monthly_fee' => 'decimal:2',
        'contract_start_at' => 'date',
        'initial_debt_synced_at' => 'datetime',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function priceHistories()
    {
        return $this->hasMany(FirmPriceHistory::class)->orderBy('valid_from');
    }

    public function taxDeclarations()
    {
        return $this->hasMany(TaxDeclaration::class);
    }

    public function taxForms()
    {
        return $this->belongsToMany(TaxForm::class, 'firm_tax_forms')
            ->withPivot('custom_due_day', 'is_active')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getBalanceAttribute(): float
    {
        $debit = $this->transactions()->where('type', 'debit')->sum('amount');
        $credit = $this->transactions()->where('type', 'credit')->sum('amount');

        return (float) ($debit - $credit);
    }

    public function priceForDate(Carbon $date): float
    {
        $history = $this->priceHistories()
            ->activeOn($date)
            ->orderByDesc('valid_from')
            ->first();

        return (float) ($history?->amount ?? $this->monthly_fee);
    }
}
