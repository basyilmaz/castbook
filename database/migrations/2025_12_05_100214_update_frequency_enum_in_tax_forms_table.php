<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN, so we need to recreate the table
        DB::statement('DROP TABLE IF EXISTS tax_forms_backup');
        DB::statement('CREATE TABLE tax_forms_backup AS SELECT * FROM tax_forms');
        
        Schema::dropIfExists('tax_forms');
        
        Schema::create('tax_forms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('frequency', ['monthly', 'quarterly', 'annual'])->default('monthly');
            $table->integer('default_due_day')->default(26);
            $table->boolean('is_active')->default(true);
            $table->json('applicable_to')->nullable();
            $table->boolean('auto_assign')->default(false);
            $table->timestamps();
        });
        
        // Restore data
        DB::statement('INSERT INTO tax_forms SELECT * FROM tax_forms_backup');
        DB::statement('DROP TABLE tax_forms_backup');
    }

    public function down(): void
    {
        // Revert back to monthly only
        DB::statement('DROP TABLE IF EXISTS tax_forms_backup');
        DB::statement('CREATE TABLE tax_forms_backup AS SELECT * FROM tax_forms');
        
        Schema::dropIfExists('tax_forms');
        
        Schema::create('tax_forms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('frequency', ['monthly'])->default('monthly');
            $table->integer('default_due_day')->default(26);
            $table->boolean('is_active')->default(true);
            $table->json('applicable_to')->nullable();
            $table->boolean('auto_assign')->default(false);
            $table->timestamps();
        });
        
        DB::statement('INSERT INTO tax_forms SELECT * FROM tax_forms_backup WHERE frequency = "monthly"');
        DB::statement('DROP TABLE tax_forms_backup');
    }
};
