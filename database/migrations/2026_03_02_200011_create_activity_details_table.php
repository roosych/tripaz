<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_details', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->primary();
            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            $table->unsignedInteger('duration_minutes');
            $table->string('activity_type', 100)->nullable();
            $table->enum('difficulty', ['easy', 'moderate', 'challenging'])->default('easy');
            $table->tinyInteger('max_participants')->unsigned()->nullable();
            $table->tinyInteger('min_age')->unsigned()->nullable();
            $table->boolean('equipment_provided')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_details');
    }
};
