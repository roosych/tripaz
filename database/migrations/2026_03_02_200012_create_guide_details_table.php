<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_details', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->primary();
            $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            $table->json('languages');
            $table->tinyInteger('experience_years')->unsigned()->nullable();
            $table->json('certifications')->nullable();
            $table->text('bio_extra')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_details');
    }
};
