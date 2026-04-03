<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('path', 500);
            $table->string('disk', 50)->default('public');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('collection', 100)->default('gallery');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('conversions')->nullable();
            $table->json('custom_properties')->nullable();
            $table->timestamps();

            $table->index(['listing_id', 'collection']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_media');
    }
};
