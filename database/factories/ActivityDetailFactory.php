<?php

namespace Database\Factories;

use App\Models\ActivityDetail;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityDetail>
 */
class ActivityDetailFactory extends Factory
{
    protected $model = ActivityDetail::class;

    public function definition(): array
    {
        return [
            'listing_id'         => Listing::factory()->activity(),
            'duration_minutes'   => $this->faker->randomElement([60, 90, 120, 180, 240, 300, 360]),
            'max_participants'   => $this->faker->numberBetween(4, 25),
        ];
    }

    public function forKids(): static
    {
        return $this->state([]);
    }

    public function extreme(): static
    {
        return $this->state([]);
    }
}
