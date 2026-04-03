<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_categories', function (Blueprint $table) {
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['listing_id', 'category_id']);
        });

        Schema::create('listing_amenities', function (Blueprint $table) {
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained('amenities')->cascadeOnDelete();
            $table->primary(['listing_id', 'amenity_id']);
        });

        Schema::create('listing_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('tag', 100);
            $table->string('locale', 5)->default('az');
            $table->timestamps();

            $table->index(['listing_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_tags');
        Schema::dropIfExists('listing_amenities');
        Schema::dropIfExists('listing_categories');
    }
};
