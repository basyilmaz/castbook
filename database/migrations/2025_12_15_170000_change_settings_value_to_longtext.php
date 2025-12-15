<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PostgreSQL için TEXT alanı zaten yeterince büyük (1GB'a kadar)
        // MySQL için LONGTEXT'e çevir
        if (config('database.default') === 'mysql') {
            DB::statement('ALTER TABLE settings MODIFY value LONGTEXT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Geri almak gerekmez
    }
};
