<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_details', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->primary();
            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            $table->decimal('duration_hours', 5, 1);
            $table->enum('difficulty', ['easy', 'moderate', 'challenging', 'expert'])->default('easy');
            $table->tinyInteger('max_participants')->unsigned()->nullable();
            $table->tinyInteger('min_age')->unsigned()->nullable();
            $table->string('meeting_point', 500)->nullable();
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->json('itinerary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_details');
    }
};
