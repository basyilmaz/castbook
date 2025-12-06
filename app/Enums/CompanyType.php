<?php

namespace App\Enums;

enum CompanyType: string
{
    case INDIVIDUAL = 'individual';
    case LIMITED = 'limited';
    case JOINT_STOCK = 'joint_stock';

    public function label(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'Şahıs Firması',
            self::LIMITED => 'Limited Şirket',
            self::JOINT_STOCK => 'Anonim Şirket',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'Gerçek kişi işletmesi - Gelir Vergisi mükellefi',
            self::LIMITED => 'Limited Şirket - Kurumlar Vergisi mükellefi',
            self::JOINT_STOCK => 'Anonim Şirket - Kurumlar Vergisi mükellefi',
        };
    }

    public function taxType(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'Gelir Vergisi',
            self::LIMITED, self::JOINT_STOCK => 'Kurumlar Vergisi',
        };
    }

    public function yearlyTaxForm(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'Gelir',
            self::LIMITED, self::JOINT_STOCK => 'Kurumlar',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $type) => ['value' => $type->value, 'label' => $type->label()],
            self::cases()
        );
    }
}
