<?php

namespace Database\Factories;

use App\Enums\PropertyType;
use App\Models\HomeDetail;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomeDetail>
 */
class HomeDetailFactory extends Factory
{
    protected $model = HomeDetail::class;

    public function definition(): array
    {
        return [
            'listing_id'    => Listing::factory()->home(),
            'property_type' => $this->faker->randomElement(PropertyType::cases()),
            'bedrooms'      => $this->faker->numberBetween(1, 5),
            'bathrooms'     => $this->faker->numberBetween(1, 3),
            'max_guests'    => $this->faker->numberBetween(2, 10),
            'total_area'    => $this->faker->randomFloat(2, 35, 350),
            'floor'         => $this->faker->numberBetween(0, 20),
            'house_rules'   => [
                'check_in'        => '14:00',
                'check_out'       => '12:00',
                'no_parties'      => true,
                'no_smoking'      => true,
                'pets'            => $this->faker->boolean(30),
                'price_per_night' => $this->faker->numberBetween(30, 300),
            ],
        ];
    }

    public function villa(): static
    {
        return $this->state([
            'property_type' => PropertyType::Villa,
            'bedrooms'      => $this->faker->numberBetween(3, 6),
            'bathrooms'     => $this->faker->numberBetween(2, 4),
            'max_guests'    => $this->faker->numberBetween(6, 14),
            'total_area'    => $this->faker->randomFloat(2, 150, 600),
        ]);
    }

    public function studio(): static
    {
        return $this->state([
            'property_type' => PropertyType::Studio,
            'bedrooms'      => 0,
            'bathrooms'     => 1,
            'max_guests'    => $this->faker->numberBetween(1, 2),
            'total_area'    => $this->faker->randomFloat(2, 25, 55),
            'floor'         => $this->faker->numberBetween(1, 20),
        ]);
    }
}
