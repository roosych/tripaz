<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            // null means "inherit from type config"; true/false means explicit per-listing override
            $table->boolean('booking_enabled')->nullable()->default(null)->after('is_verified');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('booking_enabled');
        });
    }
};
