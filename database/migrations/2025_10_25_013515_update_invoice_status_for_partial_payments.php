<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite and PostgreSQL don't support MySQL ENUM syntax
        // Skip this migration for non-MySQL databases
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('unpaid','partial','paid','cancelled') NOT NULL DEFAULT 'unpaid'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite and PostgreSQL don't support MySQL ENUM syntax
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('unpaid','paid','cancelled') NOT NULL DEFAULT 'unpaid'");
    }
};
