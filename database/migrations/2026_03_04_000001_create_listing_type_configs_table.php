<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_type_configs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->boolean('booking_enabled')->default(false);
            $table->timestamps();
        });

        // Seed all 6 enum values with booking disabled by default
        $now = now();
        DB::table('listing_type_configs')->insert([
            ['type' => 'hotel',      'booking_enabled' => false, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'home',       'booking_enabled' => false, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'tour',       'booking_enabled' => false, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'activity',   'booking_enabled' => false, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'guide',      'booking_enabled' => false, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'restaurant', 'booking_enabled' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_type_configs');
    }
};
