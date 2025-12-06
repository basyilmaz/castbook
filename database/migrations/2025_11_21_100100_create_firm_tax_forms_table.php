<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('firm_tax_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_form_id')->constrained('tax_forms')->cascadeOnDelete();
            $table->unsignedTinyInteger('custom_due_day')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['firm_id', 'tax_form_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firm_tax_forms');
    }
};
