<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Customer reviews shown on the site: imported from Google Takeout or added in the admin. */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('author_name', 120);
            $table->string('author_photo_url', 500)->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('text')->nullable();
            $table->text('reply')->nullable();
            $table->dateTime('reviewed_at')->nullable()->index();
            $table->string('source', 20)->default('manual');            // google | manual
            $table->string('external_id', 191)->nullable()->unique();   // Google review id: re-imports update, never duplicate
            $table->boolean('is_visible')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
