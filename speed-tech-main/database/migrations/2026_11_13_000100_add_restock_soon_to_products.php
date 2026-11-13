<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** "Stock coming soon": shown to customers while an out-of-stock product is being restocked. */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('restock_soon')->default(false)->after('stock_qty');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('restock_soon');
        });
    }
};
