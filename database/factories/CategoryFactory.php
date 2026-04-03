<?php

namespace Database\Factories;

use App\Enums\ListingType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'listing_type' => $this->faker->randomElement(ListingType::cases()),
            'parent_id'    => null,
            'name_az'      => $name,
            'name_ru'      => $name,
            'name_en'      => $name,
            'slug'         => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'icon'         => 'heroicon-o-tag',
            'sort_order'   => $this->faker->numberBetween(0, 100),
        ];
    }

    public function forType(ListingType $type, string $nameAz, string $nameRu, string $nameEn, int $sort = 0): static
    {
        return $this->state([
            'listing_type' => $type,
            'name_az'      => $nameAz,
            'name_ru'      => $nameRu,
            'name_en'      => $nameEn,
            'slug'         => Str::slug($nameEn),
            'sort_order'   => $sort,
        ]);
    }
}
