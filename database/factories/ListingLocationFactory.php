<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ListingLocation;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ListingLocation>
 */
class ListingLocationFactory extends Factory
{
    protected $model = ListingLocation::class;

    /**
     * Azerbaijan bounding box:
     *   lat 38.40 – 41.90
     *   lng 44.80 – 50.60
     */
    public function definition(): array
    {
        $cities = [
            ['lat' => 40.4093, 'lng' => 49.8671], // Baku
            ['lat' => 40.6828, 'lng' => 46.3606], // Ganja
            ['lat' => 41.1993, 'lng' => 47.1706], // Sheki
            ['lat' => 41.3628, 'lng' => 48.5233], // Quba
            ['lat' => 40.9967, 'lng' => 47.8415], // Gabala
            ['lat' => 38.7539, 'lng' => 48.8522], // Lankaran
            ['lat' => 40.6319, 'lng' => 48.6373], // Shamakhi
            ['lat' => 39.2092, 'lng' => 45.4122], // Nakhchivan
            ['lat' => 40.7704, 'lng' => 47.0566], // Mingachevir
            ['lat' => 40.5897, 'lng' => 46.3244], // Goygol
        ];

        $city = $this->faker->randomElement($cities);

        return [
            'listing_id'  => Listing::factory(),
            'region_id'   => Region::inRandomOrder()->value('id'),
            'latitude'    => round($city['lat'] + $this->faker->randomFloat(4, -0.05, 0.05), 8),
            'longitude'   => round($city['lng'] + $this->faker->randomFloat(4, -0.05, 0.05), 8),
            'postal_code' => 'AZ' . $this->faker->numerify('####'),
            'place_id'    => 'ChIJ' . Str::random(20),
        ];
    }
}
