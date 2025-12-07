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
        // Eğer tablo eksik kolonlarla oluşturulduysa, yeniden oluştur
        if (Schema::hasTable('auth_tokens')) {
            if (!Schema::hasColumn('auth_tokens', 'user_id')) {
                Schema::dropIfExists('auth_tokens');
                
                Schema::create('auth_tokens', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained()->onDelete('cascade');
                    $table->string('token', 64)->unique();
                    $table->timestamp('expires_at');
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->timestamps();
                    
                    $table->index(['token', 'expires_at']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Orijinal migration geri alınırsa bu da geri alınmalı
    }
};
