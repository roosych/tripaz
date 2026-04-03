<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_details', function (Blueprint $table) {
            $table->dropColumn('seating_capacity');
        });

        Schema::table('hotel_details', function (Blueprint $table) {
            $table->dropColumn('extra_services');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_details', function (Blueprint $table) {
            $table->smallInteger('seating_capacity')->unsigned()->nullable()->after('price_range');
        });

        Schema::table('hotel_details', function (Blueprint $table) {
            $table->json('extra_services')->nullable()->after('policies');
        });
    }
};
