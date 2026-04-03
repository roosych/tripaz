<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\TourDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TourDetail>
 */
class TourDetailFactory extends Factory
{
    protected $model = TourDetail::class;

    public function definition(): array
    {
        return [
            'listing_id'       => Listing::factory()->tour(),
            'duration_hours'   => $this->faker->randomElement([2, 3, 4, 6, 8, 12, 24, 48]),
            'max_participants' => $this->faker->numberBetween(5, 30),
            'meeting_point'    => $this->faker->randomElement([
                'Fountain Square, Baku',
                'Heydar Aliyev Center, Baku',
                'Icheri Sheher (Old City) main gate, Baku',
                'Sheki Khan Palace entrance',
                'Gobustan Museum parking lot',
                'Quba city center, main square',
                'Gabala Adrenalin Park entrance',
                'Lankaran city park',
                'Shamakhi central mosque',
                'Mingachevir bridge',
            ]),
            'includes'         => $this->faker->randomElements([
                'Transport from/to Baku',
                'Professional licensed guide',
                'Entrance fees to all sites',
                'Lunch at local restaurant',
                'Bottled water',
                'Travel insurance',
                'Hotel pickup',
            ], $this->faker->numberBetween(3, 5)),
            'excludes'         => $this->faker->randomElements([
                'Personal expenses',
                'Tips for guide',
                'Alcoholic beverages',
                'Extra meals',
                'Optional activities',
            ], $this->faker->numberBetween(2, 4)),
            'itinerary'        => [
                ['time' => '08:00', 'activity' => 'Hotel pickup or meeting point'],
                ['time' => '09:00', 'activity' => 'First site visit and orientation'],
                ['time' => '11:30', 'activity' => 'Second location exploration'],
                ['time' => '13:00', 'activity' => 'Traditional Azerbaijani lunch'],
                ['time' => '14:30', 'activity' => 'Afternoon site visits'],
                ['time' => '16:30', 'activity' => 'Free time for shopping'],
                ['time' => '17:30', 'activity' => 'Return journey'],
                ['time' => '19:00', 'activity' => 'Drop-off at hotel / meeting point'],
            ],
        ];
    }

    public function easy(): static
    {
        return $this->state([]);
    }

    public function challenging(): static
    {
        return $this->state([]);
    }

    public function fullDay(): static
    {
        return $this->state([
            'duration_hours' => 8,
        ]);
    }
}
