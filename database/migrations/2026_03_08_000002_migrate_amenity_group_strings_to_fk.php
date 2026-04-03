<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add the FK column (nullable so existing rows are not broken yet)
        Schema::table('amenities', function (Blueprint $table) {
            $table->foreignId('amenity_group_id')
                  ->nullable()
                  ->after('listing_type')
                  ->constrained('amenity_groups')
                  ->nullOnDelete();
        });

        // 2. Migrate each distinct old group string → new amenity_groups row
        $distinctGroups = DB::table('amenities')
            ->whereNotNull('group')
            ->where('group', '!=', '')
            ->distinct()
            ->pluck('group');

        foreach ($distinctGroups as $groupName) {
            $nameJson = json_encode(['az' => $groupName, 'ru' => $groupName, 'en' => $groupName]);

            $groupId = DB::table('amenity_groups')->insertGetId([
                'name'       => $nameJson,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('amenities')
                ->where('group', $groupName)
                ->update(['amenity_group_id' => $groupId]);
        }

        // 3. Drop the old plain-text column
        Schema::table('amenities', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }

    public function down(): void
    {
        Schema::table('amenities', function (Blueprint $table) {
            $table->string('group', 100)->nullable()->after('icon');
        });

        // Restore group strings from the FK (best effort)
        $amenities = DB::table('amenities')
            ->join('amenity_groups', 'amenities.amenity_group_id', '=', 'amenity_groups.id')
            ->select('amenities.id', 'amenity_groups.name')
            ->get();

        foreach ($amenities as $row) {
            $name = json_decode($row->name, true)['az'] ?? $row->name;
            DB::table('amenities')->where('id', $row->id)->update(['group' => $name]);
        }

        Schema::table('amenities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('amenity_group_id');
        });
    }
};
