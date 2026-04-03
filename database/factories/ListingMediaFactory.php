<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ListingMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingMedia>
 */
class ListingMediaFactory extends Factory
{
    protected $model = ListingMedia::class;

    private array $youtubeIds = [
        'gvMnNVMuqKg', 'Yzh_gYsE_hg', 'k3_Mjzf38Dc', 'kJxSuvw5lF4',
        'xvFZjo5PgG0', 'ScMzIvxBSi4', '2LoJuEaE6DQ', 'BrKfKe0vH6k',
        '9DKXMG2oZhU', 'VOb04jPz_rI', 'QTg6M1AE5dc', 'J1BEzFJrPBQ',
        'Fhx5YT-58Cs', 'NsUJbgP5rZg',
    ];

    public function definition(): array
    {
        $listingId = null; // resolved by Listing factory when used standalone
        $seed      = $this->faker->numberBetween(100, 9999);
        $sortOrder = $this->faker->numberBetween(0, 10);

        return [
            'listing_id'        => Listing::factory(),
            'path'              => "listings/placeholder/image-{$sortOrder}.jpg",
            'disk'              => 'public',
            'mime_type'         => 'image/jpeg',
            'size'              => $this->faker->numberBetween(100000, 3000000),
            'collection'        => 'gallery',
            'sort_order'        => $sortOrder,
            'conversions'       => [
                'thumb'  => "listings/placeholder/thumb-{$sortOrder}.jpg",
                'medium' => "listings/placeholder/medium-{$sortOrder}.jpg",
            ],
            'custom_properties' => [
                'url' => "https://picsum.photos/seed/{$seed}/800/600",
                'alt' => 'Listing image',
            ],
        ];
    }

    /**
     * Mark this media item as the primary one with a YouTube URL embedded.
     */
    public function primary(string $youtubeId = null): static
    {
        $youtubeId ??= $this->faker->randomElement($this->youtubeIds);

        return $this->state(function (array $attributes) use ($youtubeId) {
            $seed = $this->faker->numberBetween(100, 9999);

            return [
                'sort_order'        => 0,
                'custom_properties' => [
                    'url'         => "https://picsum.photos/seed/{$seed}/800/600",
                    'youtube_url' => "https://www.youtube.com/watch?v={$youtubeId}",
                    'alt'         => 'Primary listing image',
                ],
            ];
        });
    }

    public function video(): static
    {
        $youtubeId = $this->faker->randomElement($this->youtubeIds);

        return $this->state([
            'mime_type'         => 'video/youtube',
            'custom_properties' => [
                'youtube_url' => "https://www.youtube.com/watch?v={$youtubeId}",
            ],
        ]);
    }
}
