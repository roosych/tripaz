<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('amenities', function (Blueprint $table) {
            $table->json('name')->nullable()->after('listing_type');
        });

        DB::statement("UPDATE amenities SET name = JSON_OBJECT('az', COALESCE(name_az,''), 'ru', COALESCE(name_ru,''), 'en', COALESCE(name_en,''))");

        Schema::table('amenities', function (Blueprint $table) {
            $table->dropColumn(['name_az', 'name_ru', 'name_en']);
        });
    }

    public function down(): void
    {
        Schema::table('amenities', function (Blueprint $table) {
            $table->string('name_az')->default('');
            $table->string('name_ru')->default('');
            $table->string('name_en')->default('');
        });

        DB::statement("UPDATE amenities SET
            name_az = COALESCE(JSON_UNQUOTE(JSON_EXTRACT(name, '$.az')),''),
            name_ru = COALESCE(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ru')),''),
            name_en = COALESCE(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')),'')");

        Schema::table('amenities', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
