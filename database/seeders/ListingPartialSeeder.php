<?php

namespace Database\Seeders;

use App\Enums\ListingStatus;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListingPartialSeeder extends Seeder
{
    private array $statusWeights = [
        ListingStatus::Published,
        ListingStatus::Published,
        ListingStatus::Published,
        ListingStatus::Published,
        ListingStatus::Published,
        ListingStatus::Published,
        ListingStatus::Published,
        ListingStatus::Published,
        ListingStatus::PendingReview,
        ListingStatus::PendingReview,
        ListingStatus::Draft,
    ];

    public function run(): void
    {
        $hostEmails = collect(range(1, 8))->map(fn ($i) => "owner{$i}@tripaz.az");
        $hosts      = User::whereIn('email', $hostEmails)->get();

        if ($hosts->isEmpty()) {
            $this->command->error('No host users found. Run UserSeeder first.');
            return;
        }

        $types = [
            'restaurant' => 30,
            'activity'   => 30,
            'guide'      => 30,
        ];

        foreach ($types as $type => $count) {
            $this->command->info("Seeding {$count} {$type} listings…");

            $state = match ($type) {
                'activity'   => fn () => Listing::factory()->activity(),
                'guide'      => fn () => Listing::factory()->guide(),
                'restaurant' => fn () => Listing::factory()->restaurant(),
            };

            for ($i = 0; $i < $count; $i++) {
                $host   = $hosts->random();
                $status = $this->statusWeights[array_rand($this->statusWeights)];

                $listing = $state()->create([
                    'user_id'      => $host->id,
                    'status'       => $status,
                    'boost_weight' => rand(0, 10),
                    'is_verified'  => rand(0, 9) < 7,
                ]);

                $amenities = Amenity::where('listing_type', $type)
                    ->inRandomOrder()
                    ->limit(rand(2, 5))
                    ->pluck('id');

                if ($amenities->isNotEmpty()) {
                    $listing->amenities()->syncWithoutDetaching($amenities);
                }

                $categories = Category::where('listing_type', $type)
                    ->inRandomOrder()
                    ->limit(rand(1, 3))
                    ->pluck('id');

                if ($categories->isNotEmpty()) {
                    $listing->categories()->syncWithoutDetaching($categories);
                }
            }

            $this->command->info("Done: {$count} {$type} listings created.");
        }
    }
}
