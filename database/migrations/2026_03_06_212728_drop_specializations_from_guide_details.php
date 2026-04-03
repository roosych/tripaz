<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('guide_details', 'specializations')) {
            Schema::table('guide_details', function (Blueprint $table) {
                $table->dropColumn('specializations');
            });
        }
    }

    public function down(): void
    {
        Schema::table('guide_details', function (Blueprint $table) {
            $table->json('specializations')->nullable()->after('experience_years');
        });
    }
};
