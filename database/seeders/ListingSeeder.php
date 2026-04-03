<?php

namespace Database\Seeders;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    /**
     * Status distribution weights:
     *   published      — 80%
     *   pending_review — 13%
     *   draft          — 7%
     */
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
        // Hosts created by UserSeeder (indices 1–8)
        $hostEmails = collect(range(1, 8))->map(fn ($i) => "owner{$i}@tripaz.az");
        $hosts      = User::whereIn('email', $hostEmails)->get();

        if ($hosts->isEmpty()) {
            $this->command->error('No host users found. Run UserSeeder first.');
            return;
        }

        $types = [
            'hotel'      => 50,
            'home'       => 50,
            'tour'       => 50,
            'activity'   => 50,
            'guide'      => 50,
            'restaurant' => 50,
        ];

        foreach ($types as $type => $count) {
            $this->command->info("Seeding {$count} {$type} listings…");

            $state = match ($type) {
                'hotel'      => fn () => Listing::factory()->hotel(),
                'home'       => fn () => Listing::factory()->home(),
                'tour'       => fn () => Listing::factory()->tour(),
                'activity'   => fn () => Listing::factory()->activity(),
                'guide'      => fn () => Listing::factory()->guide(),
                'restaurant' => fn () => Listing::factory()->restaurant(),
            };

            for ($i = 0; $i < $count; $i++) {
                $host   = $hosts->random();
                $status = $this->statusWeights[array_rand($this->statusWeights)];

                /** @var Listing $listing */
                $listing = $state()->create([
                    'user_id'      => $host->id,
                    'status'       => $status,
                    'boost_weight' => rand(0, 10),
                    'is_verified'  => rand(0, 9) < 7, // 70% verified
                ]);

                // Attach 2–5 amenities matching this listing type
                $amenities = Amenity::where('listing_type', $type)
                    ->inRandomOrder()
                    ->limit(rand(2, 5))
                    ->pluck('id');

                if ($amenities->isNotEmpty()) {
                    $listing->amenities()->syncWithoutDetaching($amenities);
                }

                // Attach 1–3 categories matching this listing type
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
