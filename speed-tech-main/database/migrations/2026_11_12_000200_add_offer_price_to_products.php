<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Offers: `price` is the regular price; `offer_price` (optional, lower) is what
 * customers pay while the offer runs, until `offer_ends_at` (optional, inclusive).
 * Replaces the old "compare-at price" (struck-through higher price).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('offer_price', 12, 2)->nullable()->after('price');
            $table->date('offer_ends_at')->nullable()->after('offer_price');
        });

        // Old data: a higher compare-at price meant "was X, now price". Keep that as an offer.
        DB::table('products')->whereNotNull('price')->whereNotNull('compare_at_price')
            ->whereColumn('compare_at_price', '>', 'price')
            ->update(['offer_price' => DB::raw('price'), 'price' => DB::raw('compare_at_price')]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('compare_at_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('compare_at_price', 12, 2)->nullable()->after('price');
        });

        DB::table('products')->whereNotNull('price')->whereNotNull('offer_price')
            ->whereColumn('offer_price', '<', 'price')
            ->update(['compare_at_price' => DB::raw('price'), 'price' => DB::raw('offer_price')]);

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['offer_price', 'offer_ends_at']);
        });
    }
};
