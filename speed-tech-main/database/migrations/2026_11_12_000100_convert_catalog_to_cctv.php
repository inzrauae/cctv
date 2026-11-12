<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Last Solution sells CCTV equipment from one flat list of shop categories.
 * Removes the old laptop spare-part finder (brands → models → years → part
 * types) and the AI product-writer draft flag.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('part_category_id');
            $table->dropIndex(['ai_draft']);
            $table->dropColumn('ai_draft');
        });

        Schema::dropIfExists('model_generation_product');
        Schema::dropIfExists('model_generations');
        Schema::dropIfExists('laptop_models');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('part_categories');
    }

    public function down(): void
    {
        Schema::create('part_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku_prefix', 6)->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('laptop_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['brand_id', 'slug']);
        });
        Schema::create('model_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laptop_model_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->unsignedSmallInteger('year_from')->nullable();
            $table->unsignedSmallInteger('year_to')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('model_generation_product', function (Blueprint $table) {
            $table->foreignId('model_generation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['model_generation_id', 'product_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('part_category_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->boolean('ai_draft')->default(false)->index()->after('is_active');
        });
    }
};
