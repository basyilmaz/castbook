<?php

namespace Database\Seeders;

use App\Models\TaxForm;
use Illuminate\Database\Seeder;

class TaxFormSeeder extends Seeder
{
    public function run(): void
    {
        $forms = [
            // Aylık Beyannameler (Tüm firma türleri)
            [
                'code' => 'KDV-1',
                'name' => 'KDV Beyannamesi',
                'description' => 'Katma Değer Vergisi Beyannamesi - Aylık',
                'frequency' => 'monthly',
                'default_due_day' => 26,
                'is_active' => true,
                'applicable_to' => ['individual', 'limited', 'joint_stock'],
                'auto_assign' => true,
            ],
            [
                'code' => 'Muhtasar',
                'name' => 'Muhtasar ve Prim Hizmet Beyannamesi',
                'description' => 'Stopaj ve SGK primleri beyannamesi',
                'frequency' => 'monthly',
                'default_due_day' => 26,
                'is_active' => true,
                'applicable_to' => ['individual', 'limited', 'joint_stock'],
                'auto_assign' => true,
            ],
            [
                'code' => 'BA-BS',
                'name' => 'BA-BS Bildirimleri',
                'description' => 'Mal/Hizmet alım-satım bildirimleri (e-Fatura/e-Arşiv)',
                'frequency' => 'monthly',
                'default_due_day' => 31,
                'is_active' => true,
                'applicable_to' => ['individual', 'limited', 'joint_stock'],
                'auto_assign' => true,
            ],
            
            // 3 Aylık Beyannameler (Tüm firma türleri)
            [
                'code' => 'Geçici Vergi',
                'name' => 'Geçici Vergi Beyannamesi',
                'description' => 'Üç aylık dönemler için geçici vergi beyannamesi',
                'frequency' => 'quarterly',
                'default_due_day' => 31,
                'is_active' => true,
                'applicable_to' => ['individual', 'limited', 'joint_stock'],
                'auto_assign' => true,
            ],
            
            // Yıllık Beyannameler - Şahıs Firması
            [
                'code' => 'Gelir',
                'name' => 'Yıllık Gelir Vergisi Beyannamesi',
                'description' => 'Şahıs firmaları için yıllık gelir vergisi beyannamesi',
                'frequency' => 'annual',
                'default_due_day' => 2, // 2 Nisan
                'is_active' => true,
                'applicable_to' => ['individual'],
                'auto_assign' => true,
            ],
            
            // Yıllık Beyannameler - Limited/Anonim Şirket
            [
                'code' => 'Kurumlar',
                'name' => 'Kurumlar Vergisi Beyannamesi',
                'description' => 'Limited ve Anonim şirketler için kurumlar vergisi beyannamesi',
                'frequency' => 'annual',
                'default_due_day' => 30, // 30 Nisan
                'is_active' => true,
                'applicable_to' => ['limited', 'joint_stock'],
                'auto_assign' => true,
            ],
            
            // Opsiyonel Beyannameler (Manuel atama)
            [
                'code' => 'ÖTV',
                'name' => 'Özel Tüketim Vergisi',
                'description' => 'ÖTV kapsamındaki mallar için',
                'frequency' => 'monthly',
                'default_due_day' => 26,
                'is_active' => true,
                'applicable_to' => ['individual', 'limited', 'joint_stock'],
                'auto_assign' => false,
            ],
            [
                'code' => 'Damga Vergisi',
                'name' => 'Damga Vergisi Beyannamesi',
                'description' => 'Damga vergisine tabi işlemler için',
                'frequency' => 'monthly',
                'default_due_day' => 26,
                'is_active' => true,
                'applicable_to' => ['individual', 'limited', 'joint_stock'],
                'auto_assign' => false,
            ],
        ];

        foreach ($forms as $form) {
            TaxForm::updateOrCreate(
                ['code' => $form['code']],
                $form
            );
        }

        $this->command->info('✓ ' . count($forms) . ' vergi formu oluşturuldu/güncellendi.');
    }
}
