<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_details', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->primary();
            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            $table->tinyInteger('stars')->unsigned()->nullable();
            $table->smallInteger('total_rooms')->unsigned()->nullable();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->json('policies')->nullable();
            $table->json('extra_services')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_details');
    }
};
