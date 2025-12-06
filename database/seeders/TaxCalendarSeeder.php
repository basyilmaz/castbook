<?php

namespace Database\Seeders;

use App\Models\TaxCalendar;
use Illuminate\Database\Seeder;

class TaxCalendarSeeder extends Seeder
{
    /**
     * GİB Resmi Vergi Takvimi - 2025 ve 2026
     * Kaynak: https://gib.gov.tr/vergi-takvimi
     */
    public function run(): void
    {
        // Mevcut verileri temizle (yeniden çalıştırılabilir olması için)
        TaxCalendar::truncate();

        $this->seed2025();
        $this->seed2026();

        $this->command->info('✓ GİB Vergi Takvimi verileri yüklendi: ' . TaxCalendar::count() . ' kayıt');
    }

    private function seed2025(): void
    {
        $entries = [
            // OCAK 2025
            ['2025', '01', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Aralık 2024'],
            ['2025', '01', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Aralık 2024'],
            ['2025', '01', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Aralık 2024'],
            ['2025', '01', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Aralık 2024'],
            ['2025', '01', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Aralık 2024'],
            ['2025', '01', '27', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Aralık 2024'],
            ['2025', '01', '31', 'GECICI_VERGI', 'Gelir Vergisi Geçici Vergi Beyannamesi (4. Dönem)', '2024 Q4'],
            ['2025', '01', '31', 'GECICI_VERGI', 'Kurumlar Vergisi Geçici Vergi Beyannamesi (4. Dönem)', '2024 Q4'],

            // ŞUBAT 2025
            ['2025', '02', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Ocak 2025'],
            ['2025', '02', '17', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Ocak 2025'],
            ['2025', '02', '17', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Ocak 2025'],
            ['2025', '02', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Ocak 2025'],
            ['2025', '02', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Ocak 2025'],
            ['2025', '02', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Ocak 2025'],

            // MART 2025
            ['2025', '03', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Şubat 2025'],
            ['2025', '03', '17', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Şubat 2025'],
            ['2025', '03', '17', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Şubat 2025'],
            ['2025', '03', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Şubat 2025'],
            ['2025', '03', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Şubat 2025'],
            ['2025', '03', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Şubat 2025'],
            ['2025', '03', '31', 'GELIR', 'Yıllık Gelir Vergisi Beyannamesi (1. Taksit)', '2024'],

            // NİSAN 2025
            ['2025', '04', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Mart 2025'],
            ['2025', '04', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Mart 2025'],
            ['2025', '04', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Mart 2025'],
            ['2025', '04', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Mart 2025'],
            ['2025', '04', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Mart 2025'],
            ['2025', '04', '25', 'KURUMLAR', 'Kurumlar Vergisi Beyannamesi', '2024'],
            ['2025', '04', '28', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Mart 2025'],

            // MAYIS 2025
            ['2025', '05', '12', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Nisan 2025'],
            ['2025', '05', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Nisan 2025'],
            ['2025', '05', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Nisan 2025'],
            ['2025', '05', '17', 'GECICI_VERGI', 'Gelir Vergisi Geçici Vergi Beyannamesi (1. Dönem)', '2025 Q1'],
            ['2025', '05', '17', 'GECICI_VERGI', 'Kurumlar Vergisi Geçici Vergi Beyannamesi (1. Dönem)', '2025 Q1'],
            ['2025', '05', '19', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Nisan 2025'],
            ['2025', '05', '19', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Nisan 2025'],
            ['2025', '05', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Nisan 2025'],

            // HAZİRAN 2025
            ['2025', '06', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Mayıs 2025'],
            ['2025', '06', '16', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Mayıs 2025'],
            ['2025', '06', '16', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Mayıs 2025'],
            ['2025', '06', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Mayıs 2025'],
            ['2025', '06', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Mayıs 2025'],
            ['2025', '06', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Mayıs 2025'],

            // TEMMUZ 2025
            ['2025', '07', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Haziran 2025'],
            ['2025', '07', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Haziran 2025'],
            ['2025', '07', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Haziran 2025'],
            ['2025', '07', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Haziran 2025'],
            ['2025', '07', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Haziran 2025'],
            ['2025', '07', '28', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Haziran 2025'],
            ['2025', '07', '31', 'GELIR', 'Yıllık Gelir Vergisi Beyannamesi (2. Taksit)', '2024'],

            // AĞUSTOS 2025
            ['2025', '08', '11', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Temmuz 2025'],
            ['2025', '08', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Temmuz 2025'],
            ['2025', '08', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Temmuz 2025'],
            ['2025', '08', '17', 'GECICI_VERGI', 'Gelir Vergisi Geçici Vergi Beyannamesi (2. Dönem)', '2025 Q2'],
            ['2025', '08', '17', 'GECICI_VERGI', 'Kurumlar Vergisi Geçici Vergi Beyannamesi (2. Dönem)', '2025 Q2'],
            ['2025', '08', '18', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Temmuz 2025'],
            ['2025', '08', '18', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Temmuz 2025'],
            ['2025', '08', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Temmuz 2025'],

            // EYLÜL 2025
            ['2025', '09', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Ağustos 2025'],
            ['2025', '09', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Ağustos 2025'],
            ['2025', '09', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Ağustos 2025'],
            ['2025', '09', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Ağustos 2025'],
            ['2025', '09', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Ağustos 2025'],
            ['2025', '09', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Ağustos 2025'],

            // EKİM 2025
            ['2025', '10', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Eylül 2025'],
            ['2025', '10', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Eylül 2025'],
            ['2025', '10', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Eylül 2025'],
            ['2025', '10', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Eylül 2025'],
            ['2025', '10', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Eylül 2025'],
            ['2025', '10', '27', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Eylül 2025'],

            // KASIM 2025
            ['2025', '11', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Ekim 2025'],
            ['2025', '11', '17', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Ekim 2025'],
            ['2025', '11', '17', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Ekim 2025'],
            ['2025', '11', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Ekim 2025'],
            ['2025', '11', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Ekim 2025'],
            ['2025', '11', '17', 'GECICI_VERGI', 'Gelir Vergisi Geçici Vergi Beyannamesi (3. Dönem)', '2025 Q3'],
            ['2025', '11', '17', 'GECICI_VERGI', 'Kurumlar Vergisi Geçici Vergi Beyannamesi (3. Dönem)', '2025 Q3'],
            ['2025', '11', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Ekim 2025'],

            // ARALIK 2025
            ['2025', '12', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Kasım 2025'],
            ['2025', '12', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Kasım 2025'],
            ['2025', '12', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Kasım 2025'],
            ['2025', '12', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Kasım 2025'],
            ['2025', '12', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Kasım 2025'],
            ['2025', '12', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Kasım 2025'],
        ];

        $this->insertEntries($entries);
    }

    private function seed2026(): void
    {
        $entries = [
            // OCAK 2026
            ['2026', '01', '12', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Aralık 2025'],
            ['2026', '01', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Aralık 2025'],
            ['2026', '01', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Aralık 2025'],
            ['2026', '01', '19', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Aralık 2025'],
            ['2026', '01', '19', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Aralık 2025'],
            ['2026', '01', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Aralık 2025'],
            ['2026', '01', '02', 'GECICI_VERGI', 'Gelir Vergisi Geçici Vergi Beyannamesi (4. Dönem)', '2025 Q4'],
            ['2026', '01', '02', 'GECICI_VERGI', 'Kurumlar Vergisi Geçici Vergi Beyannamesi (4. Dönem)', '2025 Q4'],

            // ŞUBAT 2026
            ['2026', '02', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Ocak 2026'],
            ['2026', '02', '16', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Ocak 2026'],
            ['2026', '02', '16', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Ocak 2026'],
            ['2026', '02', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Ocak 2026'],
            ['2026', '02', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Ocak 2026'],
            ['2026', '02', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Ocak 2026'],

            // MART 2026
            ['2026', '03', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Şubat 2026'],
            ['2026', '03', '16', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Şubat 2026'],
            ['2026', '03', '16', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Şubat 2026'],
            ['2026', '03', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Şubat 2026'],
            ['2026', '03', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Şubat 2026'],
            ['2026', '03', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Şubat 2026'],
            ['2026', '03', '31', 'GELIR', 'Yıllık Gelir Vergisi Beyannamesi (1. Taksit)', '2025'],

            // NİSAN 2026
            ['2026', '04', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Mart 2026'],
            ['2026', '04', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Mart 2026'],
            ['2026', '04', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Mart 2026'],
            ['2026', '04', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Mart 2026'],
            ['2026', '04', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Mart 2026'],
            ['2026', '04', '27', 'KURUMLAR', 'Kurumlar Vergisi Beyannamesi', '2025'],
            ['2026', '04', '27', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Mart 2026'],

            // MAYIS 2026
            ['2026', '05', '11', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Nisan 2026'],
            ['2026', '05', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Nisan 2026'],
            ['2026', '05', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Nisan 2026'],
            ['2026', '05', '17', 'GECICI_VERGI', 'Gelir Vergisi Geçici Vergi Beyannamesi (1. Dönem)', '2026 Q1'],
            ['2026', '05', '17', 'GECICI_VERGI', 'Kurumlar Vergisi Geçici Vergi Beyannamesi (1. Dönem)', '2026 Q1'],
            ['2026', '05', '18', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Nisan 2026'],
            ['2026', '05', '18', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Nisan 2026'],
            ['2026', '05', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Nisan 2026'],

            // HAZİRAN 2026
            ['2026', '06', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Mayıs 2026'],
            ['2026', '06', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Mayıs 2026'],
            ['2026', '06', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Mayıs 2026'],
            ['2026', '06', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Mayıs 2026'],
            ['2026', '06', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Mayıs 2026'],
            ['2026', '06', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Mayıs 2026'],

            // TEMMUZ 2026
            ['2026', '07', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Haziran 2026'],
            ['2026', '07', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Haziran 2026'],
            ['2026', '07', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Haziran 2026'],
            ['2026', '07', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Haziran 2026'],
            ['2026', '07', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Haziran 2026'],
            ['2026', '07', '27', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Haziran 2026'],
            ['2026', '07', '31', 'GELIR', 'Yıllık Gelir Vergisi Beyannamesi (2. Taksit)', '2025'],

            // AĞUSTOS 2026
            ['2026', '08', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Temmuz 2026'],
            ['2026', '08', '17', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Temmuz 2026'],
            ['2026', '08', '17', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Temmuz 2026'],
            ['2026', '08', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Temmuz 2026'],
            ['2026', '08', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Temmuz 2026'],
            ['2026', '08', '17', 'GECICI_VERGI', 'Gelir Vergisi Geçici Vergi Beyannamesi (2. Dönem)', '2026 Q2'],
            ['2026', '08', '17', 'GECICI_VERGI', 'Kurumlar Vergisi Geçici Vergi Beyannamesi (2. Dönem)', '2026 Q2'],
            ['2026', '08', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Temmuz 2026'],

            // EYLÜL 2026
            ['2026', '09', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Ağustos 2026'],
            ['2026', '09', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Ağustos 2026'],
            ['2026', '09', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Ağustos 2026'],
            ['2026', '09', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Ağustos 2026'],
            ['2026', '09', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Ağustos 2026'],
            ['2026', '09', '28', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Ağustos 2026'],

            // EKİM 2026
            ['2026', '10', '12', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Eylül 2026'],
            ['2026', '10', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Eylül 2026'],
            ['2026', '10', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Eylül 2026'],
            ['2026', '10', '19', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Eylül 2026'],
            ['2026', '10', '19', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Eylül 2026'],
            ['2026', '10', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Eylül 2026'],

            // KASIM 2026
            ['2026', '11', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Ekim 2026'],
            ['2026', '11', '16', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Ekim 2026'],
            ['2026', '11', '16', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Ekim 2026'],
            ['2026', '11', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Ekim 2026'],
            ['2026', '11', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Ekim 2026'],
            ['2026', '11', '17', 'GECICI_VERGI', 'Gelir Vergisi Geçici Vergi Beyannamesi (3. Dönem)', '2026 Q3'],
            ['2026', '11', '17', 'GECICI_VERGI', 'Kurumlar Vergisi Geçici Vergi Beyannamesi (3. Dönem)', '2026 Q3'],
            ['2026', '11', '26', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Ekim 2026'],

            // ARALIK 2026
            ['2026', '12', '10', 'KKDF', 'KKDF Kesintisi Bildirimi', 'Kasım 2026'],
            ['2026', '12', '15', 'BA_BS', 'Mal ve Hizmet Alımlarına İlişkin Bildirim Formu (Form Ba)', 'Kasım 2026'],
            ['2026', '12', '15', 'BA_BS', 'Mal ve Hizmet Satışlarına İlişkin Bildirim Formu (Form Bs)', 'Kasım 2026'],
            ['2026', '12', '17', 'DAMGA', 'Damga Vergisi Beyannamesi', 'Kasım 2026'],
            ['2026', '12', '17', 'MUHTASAR', 'Muhtasar ve Prim Hizmet Beyannamesi', 'Kasım 2026'],
            ['2026', '12', '28', 'KDV', 'Katma Değer Vergisi Beyannamesi', 'Kasım 2026'],
        ];

        $this->insertEntries($entries);
    }

    private function insertEntries(array $entries): void
    {
        foreach ($entries as $entry) {
            [$year, $month, $day, $code, $name, $periodLabel] = $entry;

            $dueDate = sprintf('%s-%02d-%02d', $year, $month, $day);

            // Frekans belirleme
            $frequency = match ($code) {
                'GECICI_VERGI' => 'quarterly',
                'KURUMLAR', 'GELIR' => 'yearly',
                default => 'monthly',
            };

            TaxCalendar::create([
                'year' => (int) $year,
                'month' => (int) $month,
                'day' => (int) $day,
                'due_date' => $dueDate,
                'code' => $code,
                'name' => $name,
                'description' => $name . ' - ' . $periodLabel . ' dönemi son günü',
                'period_label' => $periodLabel,
                'frequency' => $frequency,
                'applicable_to' => null, // Tüm şirket türleri
                'is_active' => true,
            ]);
        }
    }
}
