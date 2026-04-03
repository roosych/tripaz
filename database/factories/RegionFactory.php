<?php

namespace Database\Factories;

use App\Enums\RegionType;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition(): array
    {
        $nameAz = $this->faker->randomElement([
            'Bakı', 'Gəncə', 'Şəki', 'Quba', 'Qəbələ',
            'Lənkəran', 'Şamaxı', 'Naxçıvan', 'Mingəçevir', 'Göygöl',
        ]);

        return [
            'parent_id' => null,
            'name_az'   => $nameAz,
            'name_ru'   => $nameAz,
            'name_en'   => $nameAz,
            'slug'      => Str::slug($nameAz) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'type'      => RegionType::City,
        ];
    }

    public function country(): static
    {
        return $this->state([
            'name_az' => 'Azərbaycan',
            'name_ru' => 'Азербайджан',
            'name_en' => 'Azerbaijan',
            'slug'    => 'azerbaijan',
            'type'    => RegionType::Country,
        ]);
    }

    public function city(Region $parent): static
    {
        return $this->state([
            'parent_id' => $parent->id,
            'type'      => RegionType::City,
        ]);
    }
}
