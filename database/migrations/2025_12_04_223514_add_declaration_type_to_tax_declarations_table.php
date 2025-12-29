<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tax_declarations', function (Blueprint $table) {
            $table->string('declaration_type')->default('normal')->after('period_label');
            $table->integer('sequence_number')->default(1)->after('declaration_type');
            $table->string('reference_number')->nullable()->after('paid_at');
            
            // Unique constraint'i güncelle
            $table->dropUnique('tax_decl_unique');
            $table->unique([
                'firm_id',
                'tax_form_id',
                'period_start',
                'period_end',
                'declaration_type',
                'sequence_number'
            ], 'unique_tax_declaration');
        });
    }

    public function down(): void
    {
        Schema::table('tax_declarations', function (Blueprint $table) {
            $table->dropUnique('unique_tax_declaration');
            
            $table->dropColumn(['declaration_type', 'sequence_number', 'reference_number']);
            
            $table->unique(['firm_id', 'tax_form_id', 'period_start', 'period_end']);
        });
    }
};
