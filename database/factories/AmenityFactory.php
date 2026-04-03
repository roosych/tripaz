<?php

namespace Database\Factories;

use App\Enums\ListingType;
use App\Models\Amenity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Amenity>
 */
class AmenityFactory extends Factory
{
    protected $model = Amenity::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'listing_type' => $this->faker->randomElement(ListingType::cases()),
            'name_az'      => $name,
            'name_ru'      => $name,
            'name_en'      => $name,
            'icon'         => 'heroicon-o-check-circle',
            'group'        => 'general',
        ];
    }

    public function forType(
        ?ListingType $type,
        string $nameAz,
        string $nameRu,
        string $nameEn,
        string $icon,
        string $group,
    ): static {
        return $this->state([
            'listing_type' => $type,
            'name_az'      => $nameAz,
            'name_ru'      => $nameRu,
            'name_en'      => $nameEn,
            'icon'         => $icon,
            'group'        => $group,
        ]);
    }
}
