<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL'de oluşturulan check constraint'i kaldır
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE tax_forms DROP CONSTRAINT IF EXISTS tax_forms_frequency_check');
        }
    }

    public function down(): void
    {
        // Geri almayı desteklemiyoruz
    }
};
