<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            $table->enum('company_type', ['individual', 'limited', 'joint_stock'])
                ->default('individual')
                ->after('name')
                ->comment('Şahıs Firması, Limited Şirket, Anonim Şirket');
        });
        
        Schema::table('tax_forms', function (Blueprint $table) {
            $table->json('applicable_to')
                ->nullable()
                ->after('is_active')
                ->comment('Hangi firma türlerine uygulanabilir');
                
            $table->boolean('auto_assign')
                ->default(false)
                ->after('applicable_to')
                ->comment('Yeni firmalara otomatik atansın mı');
        });
    }

    public function down(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            $table->dropColumn('company_type');
        });
        
        Schema::table('tax_forms', function (Blueprint $table) {
            $table->dropColumn(['applicable_to', 'auto_assign']);
        });
    }
};
