<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Beyanname statülerini sadeleştir:
     * - filed, paid, not_required → submitted
     * - pending → pending (değişmez)
     */
    public function up(): void
    {
        // Eski statüleri yeni statüye dönüştür
        DB::table('tax_declarations')
            ->whereIn('status', ['filed', 'paid', 'not_required'])
            ->update(['status' => 'submitted']);
    }

    /**
     * Geri al (varsayılan olarak filed'a döndür)
     */
    public function down(): void
    {
        DB::table('tax_declarations')
            ->where('status', 'submitted')
            ->update(['status' => 'filed']);
    }
};
