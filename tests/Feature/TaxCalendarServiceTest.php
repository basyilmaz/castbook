<?php

namespace Tests\Feature;

use App\Services\TaxCalendarService;
use App\Models\TaxCalendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaxCalendarService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TaxCalendarService::class);
    }

    public function test_can_generate_calendar_for_year(): void
    {
        $year = 2025;
        
        $result = $this->service->generateForYear($year);
        
        // Kayıt oluşturulduğunu kontrol et
        $this->assertGreaterThan(0, $result['created']);
        $this->assertEquals($year, $result['year']);
        
        // Veritabanında kayıt olduğunu kontrol et
        $this->assertDatabaseHas('tax_calendars', [
            'year' => $year,
        ]);
    }

    public function test_duplicate_generation_skips_existing(): void
    {
        $year = 2025;
        
        // İlk oluşturma
        $result1 = $this->service->generateForYear($year);
        $this->assertGreaterThan(0, $result1['created']);
        
        // İkinci oluşturma - hepsi atlanmalı
        $result2 = $this->service->generateForYear($year);
        $this->assertEquals(0, $result2['created']);
        $this->assertEquals($result1['created'], $result2['skipped']);
    }

    public function test_monthly_declarations_are_generated(): void
    {
        $year = 2025;
        $this->service->generateForYear($year);
        
        // KDV beyannameleri (12 ay)
        $kdvCount = TaxCalendar::where('year', $year)
            ->where('code', 'KDV')
            ->count();
        $this->assertEquals(12, $kdvCount);
        
        // Muhtasar beyannameleri (12 ay)
        $muhtasarCount = TaxCalendar::where('year', $year)
            ->where('code', 'MUHTASAR')
            ->count();
        $this->assertEquals(12, $muhtasarCount);
    }

    public function test_quarterly_declarations_are_generated(): void
    {
        $year = 2025;
        $this->service->generateForYear($year);
        
        // Geçici vergi beyannameleri (4 çeyrek * 2 tür = 8)
        $geciciCount = TaxCalendar::where('year', $year)
            ->where('code', 'GECICI_VERGI')
            ->count();
        $this->assertEquals(8, $geciciCount);
    }

    public function test_yearly_declarations_are_generated(): void
    {
        $year = 2025;
        $this->service->generateForYear($year);
        
        // Kurumlar vergisi (1 adet)
        $kurumlarCount = TaxCalendar::where('year', $year)
            ->where('code', 'KURUMLAR')
            ->count();
        $this->assertEquals(1, $kurumlarCount);
        
        // Gelir vergisi (2 taksit)
        $gelirCount = TaxCalendar::where('year', $year)
            ->where('code', 'GELIR')
            ->count();
        $this->assertEquals(2, $gelirCount);
    }

    public function test_due_date_adjusts_for_weekend(): void
    {
        $year = 2025;
        $this->service->generateForYear($year);
        
        // Tüm tarihleri kontrol et - hiçbiri Cumartesi/Pazar olmamalı
        $entries = TaxCalendar::where('year', $year)->get();
        
        foreach ($entries as $entry) {
            $dayOfWeek = $entry->due_date->dayOfWeek; // 0=Pazar, 6=Cumartesi
            $this->assertNotEquals(0, $dayOfWeek, "Pazar günü tespit edildi: {$entry->due_date}");
            $this->assertNotEquals(6, $dayOfWeek, "Cumartesi günü tespit edildi: {$entry->due_date}");
        }
    }

    public function test_delete_year_removes_all_entries(): void
    {
        $year = 2025;
        
        // Oluştur
        $this->service->generateForYear($year);
        $this->assertGreaterThan(0, TaxCalendar::where('year', $year)->count());
        
        // Sil
        $deleted = $this->service->deleteYear($year);
        $this->assertGreaterThan(0, $deleted);
        $this->assertEquals(0, TaxCalendar::where('year', $year)->count());
    }

    public function test_get_missing_years_returns_current_and_next(): void
    {
        // Hiç veri yokken hem bu yıl hem gelecek yıl eksik olmalı
        $missing = $this->service->getMissingYears();
        
        $currentYear = now()->year;
        $this->assertContains($currentYear, $missing);
        $this->assertContains($currentYear + 1, $missing);
    }

    public function test_available_years_returns_generated_years(): void
    {
        // Önce boş olmalı
        $available = $this->service->getAvailableYears();
        $this->assertEmpty($available);
        
        // 2025 oluştur
        $this->service->generateForYear(2025);
        $available = $this->service->getAvailableYears();
        $this->assertContains(2025, $available);
    }
}
