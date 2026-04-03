<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_details', function (Blueprint $table) {
            $table->dropColumn(['difficulty', 'min_age']);
        });

        Schema::table('activity_details', function (Blueprint $table) {
            $table->dropColumn(['difficulty', 'min_age']);
        });
    }

    public function down(): void
    {
        Schema::table('tour_details', function (Blueprint $table) {
            $table->enum('difficulty', ['easy', 'moderate', 'challenging', 'expert'])->default('easy')->after('duration_hours');
            $table->tinyInteger('min_age')->unsigned()->nullable()->after('max_participants');
        });

        Schema::table('activity_details', function (Blueprint $table) {
            $table->enum('difficulty', ['easy', 'moderate', 'challenging'])->default('easy')->after('activity_type');
            $table->tinyInteger('min_age')->unsigned()->nullable()->after('max_participants');
        });
    }
};
