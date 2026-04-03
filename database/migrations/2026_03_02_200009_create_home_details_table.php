<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_details', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->primary();
            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            $table->enum('property_type', ['apartment', 'house', 'villa', 'studio', 'cottage']);
            $table->tinyInteger('bedrooms')->unsigned()->default(1);
            $table->tinyInteger('bathrooms')->unsigned()->default(1);
            $table->tinyInteger('max_guests')->unsigned()->default(2);
            $table->decimal('total_area', 8, 2)->nullable();
            $table->smallInteger('floor')->nullable();
            $table->boolean('has_wifi')->default(false);
            $table->boolean('has_parking')->default(false);
            $table->boolean('has_kitchen')->default(true);
            $table->json('house_rules')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_details');
    }
};
