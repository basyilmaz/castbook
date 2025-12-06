<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('firm_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['firm_id', 'valid_from']);
            $table->index(['firm_id', 'valid_from', 'valid_to']);
        });

        $now = Carbon::now();
        DB::table('firms')->select('id', 'monthly_fee', 'contract_start_at')->orderBy('id')->chunkById(200, function ($firms) use ($now) {
            foreach ($firms as $firm) {
                if (is_null($firm->monthly_fee)) {
                    continue;
                }

                $start = $firm->contract_start_at
                    ? Carbon::parse($firm->contract_start_at)
                    : $now->copy()->startOfMonth();

                DB::table('firm_price_histories')->insert([
                    'firm_id' => $firm->id,
                    'amount' => $firm->monthly_fee,
                    'valid_from' => $start->format('Y-m-d'),
                    'valid_to' => null,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firm_price_histories');
    }
};