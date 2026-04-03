<?php

namespace Database\Factories;

use App\Models\HotelDetail;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HotelDetail>
 */
class HotelDetailFactory extends Factory
{
    protected $model = HotelDetail::class;

    public function definition(): array
    {
        return [
            'listing_id'     => Listing::factory()->hotel(),
            'stars'          => $this->faker->numberBetween(2, 5),
            'total_rooms'    => $this->faker->numberBetween(20, 500),
            'check_in_time'  => $this->faker->randomElement(['12:00', '13:00', '14:00', '15:00']),
            'check_out_time' => $this->faker->randomElement(['10:00', '11:00', '12:00']),
            'policies'       => [
                'cancellation' => 'Free cancellation up to 24 hours before check-in',
                'pets'         => $this->faker->boolean(30) ? 'Pets allowed' : 'No pets',
                'smoking'      => 'Non-smoking property',
                'children'     => 'Children of all ages welcome',
            ],
            'extra_services' => [
                'airport_transfer' => $this->faker->boolean(60),
                'laundry'          => $this->faker->boolean(70),
                'room_service'     => $this->faker->boolean(80),
                'concierge'        => $this->faker->boolean(60),
                'price_per_night'  => $this->faker->numberBetween(50, 500),
            ],
        ];
    }

    public function luxury(): static
    {
        return $this->state([
            'stars'       => 5,
            'total_rooms' => $this->faker->numberBetween(100, 500),
            'extra_services' => [
                'airport_transfer' => true,
                'laundry'          => true,
                'room_service'     => true,
                'concierge'        => true,
                'spa'              => true,
                'price_per_night'  => $this->faker->numberBetween(200, 500),
            ],
        ]);
    }

    public function budget(): static
    {
        return $this->state([
            'stars'       => $this->faker->numberBetween(2, 3),
            'total_rooms' => $this->faker->numberBetween(10, 60),
            'extra_services' => [
                'airport_transfer' => false,
                'laundry'          => $this->faker->boolean(40),
                'room_service'     => false,
                'price_per_night'  => $this->faker->numberBetween(20, 80),
            ],
        ]);
    }
}
