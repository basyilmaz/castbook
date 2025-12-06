<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tax_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_form_id')->constrained('tax_forms')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('period_label', 20);
            $table->date('due_date');
            $table->string('status')->default('pending');
            $table->dateTime('filed_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['firm_id', 'tax_form_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_declarations');
    }
};
