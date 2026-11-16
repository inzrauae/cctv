<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /** Starter sub-categories, added only to categories that exist (edit them in Admin → Shop categories). */
    private const STARTERS = [
        'cctv-cameras' => ['IP Cameras', 'Analog (HD) Cameras', 'Wi-Fi Cameras', 'PTZ Cameras'],
        'dvr-nvr-recorders' => ['DVR (Analog)', 'NVR (IP)'],
        'cctv-packages' => ['IP Camera Packages', 'Analog Camera Packages'],
    ];

    /** A shop category can have sub-categories (e.g. CCTV Cameras → IP / Analog); a product can pick one. */
    public function up(): void
    {
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['category_id', 'slug']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });

        $now = now();
        foreach (self::STARTERS as $categorySlug => $names) {
            $categoryId = DB::table('categories')->where('slug', $categorySlug)->value('id');
            if (! $categoryId) {
                continue;
            }
            foreach ($names as $i => $name) {
                DB::table('subcategories')->insert([
                    'category_id' => $categoryId, 'name' => $name, 'slug' => Str::slug($name),
                    'sort_order' => $i, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subcategory_id');
        });
        Schema::dropIfExists('subcategories');
    }
};
