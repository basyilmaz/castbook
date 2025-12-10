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
        Schema::table('invoices', function (Blueprint $table) {
            // KDV alanları - amount alanından sonra ekle
            $table->decimal('vat_rate', 5, 2)->nullable()->default(20.00)->after('amount');
            $table->boolean('vat_included')->default(true)->after('vat_rate');
            $table->decimal('subtotal', 15, 2)->nullable()->after('vat_included');
            $table->decimal('vat_amount', 15, 2)->nullable()->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'vat_rate',
                'vat_included',
                'subtotal',
                'vat_amount',
            ]);
        });
    }
};
