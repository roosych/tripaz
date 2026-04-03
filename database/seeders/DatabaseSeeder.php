<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Execution order matters:
     *   1. RoleSeeder       — creates roles and permissions (needed by UserSeeder)
     *   2. RegionSeeder     — creates Azerbaijan + 10 cities (needed by ListingSeeder locations)
     *   3. CategorySeeder   — creates all listing categories (needed by ListingSeeder)
     *   4. AmenitySeeder    — creates all amenities (needed by ListingSeeder)
     *
     * Local-only (APP_ENV=local):
     *   5. UserSeeder       — admin, 8 hosts, 20 regular users (hardcoded password "password")
     *   6. ListingSeeder    — 300 listings (50 per type) with full related data
     *   7. ReviewSeeder     — 5–15 reviews per published listing, recalculates ratings
     */
    public function run(): void
    {
        // Reference data — safe to run in any environment
        $this->call([
            RoleSeeder::class,
            RegionSeeder::class,
            CategorySeeder::class,
            AmenitySeeder::class,
            LookupOptionSeeder::class,
        ]);

        // Test/demo data — local development only
        if (app()->isLocal()) {
            $this->call([
                UserSeeder::class,
                ListingSeeder::class,
                //ReviewSeeder::class,
            ]);
        }
    }
}
