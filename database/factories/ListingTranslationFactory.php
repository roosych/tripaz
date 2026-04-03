<?php

namespace Database\Factories;

use App\Enums\Locale;
use App\Models\Listing;
use App\Models\ListingTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ListingTranslation>
 */
class ListingTranslationFactory extends Factory
{
    protected $model = ListingTranslation::class;

    public function definition(): array
    {
        $titles = [
            'az' => ['Bakı Lüks Oteli', 'Qəbələ Dağ Evi', 'Şəki Tarixi Turu', 'Xəzər Dalğıcılığı', 'Bakı Milli Restoran'],
            'ru' => ['Роскошный Отель Баку', 'Горный Дом Габала', 'Исторический Тур Шеки', 'Дайвинг Каспий', 'Национальный Ресторан Баку'],
            'en' => ['Luxury Hotel Baku', 'Mountain House Gabala', 'Historical Tour Sheki', 'Caspian Diving', 'National Restaurant Baku'],
        ];

        $locale = $this->faker->randomElement(Locale::cases())->value;
        $title  = $this->faker->randomElement($titles[$locale]);
        $description = $this->faker->paragraph(3);

        return [
            'listing_id'      => Listing::factory(),
            'locale'          => $locale,
            'title'           => $title,
            'description'     => $description,
            'address'         => $this->faker->address(),
            'seo_title'       => $title . ' | Tripaz',
            'seo_description' => Str::limit($description, 155),
        ];
    }

    public function az(): static
    {
        return $this->state(['locale' => Locale::Az->value]);
    }

    public function ru(): static
    {
        return $this->state(['locale' => Locale::Ru->value]);
    }

    public function en(): static
    {
        return $this->state(['locale' => Locale::En->value]);
    }
}
