<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_extra_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('extra_field_id')->constrained('invoice_extra_fields')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'extra_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_extra_values');
    }
};
