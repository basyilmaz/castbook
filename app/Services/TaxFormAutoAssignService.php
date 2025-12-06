<?php

namespace App\Services;

use App\Models\Firm;
use App\Models\FirmTaxForm;
use App\Models\TaxForm;

/**
 * Yeni firma eklendiğinde şirket türüne göre vergi formlarını otomatik atar
 */
class TaxFormAutoAssignService
{
    /**
     * Şirket türüne göre varsayılan form kodları
     */
    protected array $templates = [
        'individual' => [ // Şahıs Şirketi
            'KDV',
            'MUHTASAR',
            'GELIR',
        ],
        'limited' => [ // Limited Şirket
            'KDV',
            'MUHTASAR',
            'DAMGA',
            'BABS', // Ba-Bs
            'GECICI',
            'KURUMLAR',
        ],
        'joint_stock' => [ // Anonim Şirket
            'KDV',
            'MUHTASAR',
            'DAMGA',
            'BABS',
            'GECICI',
            'KURUMLAR',
        ],
    ];

    /**
     * Firma için varsayılan formları otomatik ata
     */
    public function assignDefaultForms(Firm $firm): array
    {
        $companyType = $firm->company_type ?? 'limited';
        $templateCodes = $this->templates[$companyType] ?? $this->templates['limited'];

        $assigned = [];
        $skipped = [];

        foreach ($templateCodes as $code) {
            // Form'u bul (code veya code benzeri)
            $taxForm = TaxForm::where('code', $code)
                ->orWhere('code', 'LIKE', $code . '%')
                ->where('is_active', true)
                ->first();

            if (!$taxForm) {
                $skipped[] = $code;
                continue;
            }

            // Zaten atanmış mı?
            $exists = FirmTaxForm::where('firm_id', $firm->id)
                ->where('tax_form_id', $taxForm->id)
                ->exists();

            if ($exists) {
                $skipped[] = $code;
                continue;
            }

            // Yeni atama oluştur
            FirmTaxForm::create([
                'firm_id' => $firm->id,
                'tax_form_id' => $taxForm->id,
                'custom_due_day' => null, // Varsayılan kullan
                'is_active' => true,
            ]);

            $assigned[] = $taxForm->code;
        }

        return [
            'assigned' => $assigned,
            'skipped' => $skipped,
            'company_type' => $companyType,
        ];
    }

    /**
     * Tüm auto_assign=true formlarını firmaya ata
     */
    public function assignAutoForms(Firm $firm): array
    {
        $autoForms = TaxForm::where('is_active', true)
            ->where('auto_assign', true)
            ->get();

        $assigned = [];

        foreach ($autoForms as $taxForm) {
            // Şirket türü uyumlu mu?
            if ($taxForm->applicable_to) {
                $applicableTypes = is_array($taxForm->applicable_to) 
                    ? $taxForm->applicable_to 
                    : json_decode($taxForm->applicable_to, true);
                    
                if (!in_array($firm->company_type, $applicableTypes)) {
                    continue;
                }
            }

            // Zaten atanmış mı?
            $exists = FirmTaxForm::where('firm_id', $firm->id)
                ->where('tax_form_id', $taxForm->id)
                ->exists();

            if ($exists) {
                continue;
            }

            FirmTaxForm::create([
                'firm_id' => $firm->id,
                'tax_form_id' => $taxForm->id,
                'custom_due_day' => null,
                'is_active' => true,
            ]);

            $assigned[] = $taxForm->code;
        }

        return $assigned;
    }

    /**
     * Şirket türü için şablon kodlarını getir
     */
    public function getTemplateForType(string $companyType): array
    {
        return $this->templates[$companyType] ?? $this->templates['limited'];
    }

    /**
     * Tüm şablonları getir
     */
    public function getAllTemplates(): array
    {
        return $this->templates;
    }
}
