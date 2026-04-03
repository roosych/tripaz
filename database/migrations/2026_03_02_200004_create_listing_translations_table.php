<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->enum('locale', ['az', 'ru', 'en']);
            $table->string('title');
            $table->text('description');
            $table->string('address', 500)->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();

            $table->unique(['listing_id', 'locale']);
        });

        // Add FULLTEXT index — MySQL only (SQLite used in tests does not support it)
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE listing_translations ADD FULLTEXT fulltext_title_description (title, description)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_translations');
    }
};
