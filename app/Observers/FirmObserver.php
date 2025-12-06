<?php

namespace App\Observers;

use App\Models\Firm;
use App\Models\TaxForm;
use Illuminate\Support\Facades\Log;

class FirmObserver
{
    /**
     * Handle the Firm "created" event.
     */
    public function created(Firm $firm): void
    {
        $this->autoAssignTaxForms($firm);
    }

    /**
     * Handle the Firm "updated" event.
     */
    public function updated(Firm $firm): void
    {
        // Firma türü değiştiyse vergi formlarını güncelle
        if ($firm->isDirty('company_type')) {
            $this->autoAssignTaxForms($firm, true);
        }
    }

    /**
     * Firma türüne göre otomatik vergi formları ata
     */
    private function autoAssignTaxForms(Firm $firm, bool $isUpdate = false): void
    {
        // Otomatik atanacak formları bul
        $forms = TaxForm::where('auto_assign', true)
            ->where('is_active', true)
            ->get()
            ->filter(function ($form) use ($firm) {
                $applicableTo = $form->applicable_to ?? [];
                return in_array($firm->company_type?->value, $applicableTo);
            });

        if ($forms->isEmpty()) {
            return;
        }

        // Formları ata
        $formIds = $forms->pluck('id')->toArray();
        
        if ($isUpdate) {
            // Güncelleme: Mevcut formları koru, yenileri ekle
            $firm->taxForms()->syncWithoutDetaching($formIds);
        } else {
            // Yeni firma: Direkt ata
            $firm->taxForms()->attach($formIds);
        }

        Log::info('Tax forms auto-assigned', [
            'firm_id' => $firm->id,
            'firm_name' => $firm->name,
            'company_type' => $firm->company_type?->value,
            'forms_count' => count($formIds),
            'is_update' => $isUpdate,
        ]);
    }
}
