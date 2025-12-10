<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            // Otomasyon ayarları
            $table->boolean('auto_invoice_enabled')->default(true)->after('notes');
            $table->boolean('tax_tracking_enabled')->default(true)->after('auto_invoice_enabled');
            
            // Varsayılan KDV ayarları
            $table->decimal('default_vat_rate', 5, 2)->default(20.00)->after('tax_tracking_enabled');
            $table->boolean('default_vat_included')->default(true)->after('default_vat_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            $table->dropColumn([
                'auto_invoice_enabled',
                'tax_tracking_enabled',
                'default_vat_rate',
                'default_vat_included',
            ]);
        });
    }
};
