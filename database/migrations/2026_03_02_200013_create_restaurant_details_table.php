<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_details', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->primary();
            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            $table->json('cuisine_types');
            $table->enum('price_range', ['budget', 'mid', 'upscale', 'fine_dining'])->default('mid');
            $table->smallInteger('seating_capacity')->unsigned()->nullable();
            $table->boolean('has_outdoor')->default(false);
            $table->boolean('has_delivery')->default(false);
            $table->boolean('has_takeaway')->default(false);
            $table->json('opening_hours')->nullable();
            $table->string('menu_url', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_details');
    }
};
