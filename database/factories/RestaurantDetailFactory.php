<?php

namespace Database\Factories;

use App\Enums\PriceRange;
use App\Models\Listing;
use App\Models\RestaurantDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantDetail>
 */
class RestaurantDetailFactory extends Factory
{
    protected $model = RestaurantDetail::class;

    public function definition(): array
    {
        return [
            'listing_id'       => Listing::factory()->restaurant(),
            'cuisine_types'    => $this->faker->randomElements([
                'azerbaijani', 'international', 'caucasian', 'turkish',
                'mediterranean', 'russian', 'seafood', 'vegetarian', 'halal',
            ], $this->faker->numberBetween(1, 3)),
            'price_range'      => $this->faker->randomElement(PriceRange::cases()),
            'seating_capacity' => $this->faker->numberBetween(20, 350),
            'has_outdoor'      => $this->faker->boolean(55),
            'has_delivery'     => $this->faker->boolean(50),
            'has_takeaway'     => $this->faker->boolean(40),
            'opening_hours'    => [
                'monday'    => ['open' => '10:00', 'close' => '23:00'],
                'tuesday'   => ['open' => '10:00', 'close' => '23:00'],
                'wednesday' => ['open' => '10:00', 'close' => '23:00'],
                'thursday'  => ['open' => '10:00', 'close' => '23:00'],
                'friday'    => ['open' => '10:00', 'close' => '00:00'],
                'saturday'  => ['open' => '10:00', 'close' => '00:00'],
                'sunday'    => ['open' => '11:00', 'close' => '22:00'],
            ],
            'menu_url' => $this->faker->optional(0.3)->url(),
        ];
    }

    public function fineDining(): static
    {
        return $this->state([
            'price_range'      => PriceRange::FineDining,
            'seating_capacity' => $this->faker->numberBetween(30, 80),
            'has_outdoor'      => $this->faker->boolean(40),
            'has_delivery'     => false,
            'has_takeaway'     => false,
        ]);
    }

    public function budget(): static
    {
        return $this->state([
            'price_range'   => PriceRange::Budget,
            'has_delivery'  => true,
            'has_takeaway'  => true,
        ]);
    }
}
