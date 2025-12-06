<?php

use App\Models\Invoice;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // Mevcut faturaları line items'a dönüştür
        Invoice::chunk(100, function ($invoices) {
            foreach ($invoices as $invoice) {
                // Eğer zaten line item varsa atla
                if ($invoice->lineItems()->exists()) {
                    continue;
                }

                // Tek satır olarak ekle
                $invoice->lineItems()->create([
                    'description' => $invoice->description ?? 'Aylık muhasebe ücreti',
                    'quantity' => 1,
                    'unit_price' => $invoice->amount,
                    'amount' => $invoice->amount,
                    'sort_order' => 0,
                    'item_type' => 'monthly_fee',
                ]);
            }
        });
    }

    public function down(): void
    {
        // Rollback: Line items'ları sil
        \DB::table('invoice_line_items')->truncate();
    }
};
