<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable(); // Kullanıcı silinse bile isim kalsın
            $table->string('action', 50); // create, update, delete, login, logout, export, etc.
            $table->string('model_type')->nullable(); // App\Models\Invoice vb.
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('description'); // "Fatura #123 oluşturuldu"
            $table->json('old_values')->nullable(); // Güncelleme öncesi değerler
            $table->json('new_values')->nullable(); // Güncelleme sonrası değerler
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
