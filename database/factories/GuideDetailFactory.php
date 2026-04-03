<?php

namespace Database\Factories;

use App\Models\GuideDetail;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuideDetail>
 */
class GuideDetailFactory extends Factory
{
    protected $model = GuideDetail::class;

    public function definition(): array
    {
        return [
            'listing_id'       => Listing::factory()->guide(),
            'languages'        => $this->faker->randomElements(
                ['az', 'ru', 'en', 'tr', 'de', 'fr', 'ar', 'zh'],
                $this->faker->numberBetween(2, 4)
            ),
            'experience_years' => $this->faker->numberBetween(1, 25),
            'certifications'   => $this->faker->randomElements([
                'Licensed Tour Guide - Ministry of Culture Azerbaijan',
                'First Aid Certified',
                'WFTGA Certified Guide',
                'National Park Authorized Guide',
                'UNESCO Heritage Site Certified',
            ], $this->faker->numberBetween(1, 3)),
            'bio_extra'        => 'Experienced local guide with deep passion for Azerbaijani culture, history and nature. '
                . 'I have spent years exploring every corner of this beautiful country and love sharing its hidden gems with visitors. '
                . 'Available for private, group, and custom tours throughout Azerbaijan.',
        ];
    }

    public function multilingual(): static
    {
        return $this->state([
            'languages' => ['az', 'ru', 'en', 'tr'],
        ]);
    }

    public function senior(): static
    {
        return $this->state([
            'experience_years' => $this->faker->numberBetween(10, 25),
            'certifications'   => [
                'Licensed Tour Guide - Ministry of Culture Azerbaijan',
                'First Aid Certified',
                'WFTGA Certified Guide',
                'UNESCO Heritage Site Certified',
            ],
        ]);
    }
}
