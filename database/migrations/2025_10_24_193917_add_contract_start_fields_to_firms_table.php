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
            if (! Schema::hasColumn('firms', 'contract_start_at')) {
                $table->date('contract_start_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('firms', 'initial_debt_synced_at')) {
                $table->timestamp('initial_debt_synced_at')->nullable()->after('contract_start_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('firms', function (Blueprint $table) {
            if (Schema::hasColumn('firms', 'initial_debt_synced_at')) {
                $table->dropColumn('initial_debt_synced_at');
            }

            if (Schema::hasColumn('firms', 'contract_start_at')) {
                $table->dropColumn('contract_start_at');
            }
        });
    }
};
