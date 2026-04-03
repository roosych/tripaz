<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['hotel', 'home', 'tour', 'activity', 'guide', 'restaurant']);
            $table->enum('status', ['draft', 'pending_review', 'published', 'rejected', 'suspended'])->default('draft');
            $table->tinyInteger('boost_weight')->unsigned()->default(1);
            $table->decimal('avg_rating', 3, 2)->nullable();
            $table->unsignedInteger('review_count')->default(0);
            $table->string('slug')->unique();
            $table->string('featured_image', 500)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
