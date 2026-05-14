<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('price_from', 10, 2)->nullable()->after('boost_weight');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('price_from');
        });
    }
};
