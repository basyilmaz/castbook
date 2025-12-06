<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_calendars', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->integer('day');
            $table->date('due_date')->index();
            $table->string('code', 50);           // KDV, MUHTASAR, GECICI_VERGI vb.
            $table->string('name');               // Tam adı
            $table->text('description')->nullable();
            $table->string('period_label')->nullable(); // "Kasım 2025" gibi
            $table->string('frequency', 20)->default('monthly'); // monthly, quarterly, yearly
            $table->json('applicable_to')->nullable(); // ["limited", "anonim", "sahis"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['year', 'month']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_calendars');
    }
};
