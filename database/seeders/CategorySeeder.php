<?php

namespace Database\Seeders;

use App\Enums\ListingType;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // ---- Hotels ----
            [
                'type' => ListingType::Hotel,
                'name' => ['az' => 'Butik Otellər',   'ru' => 'Бутик Отели',        'en' => 'Boutique Hotels'],
                'slug' => 'boutique-hotels',
                'icon' => 'heroicon-o-building-office',
                'sort' => 1,
            ],
            [
                'type' => ListingType::Hotel,
                'name' => ['az' => 'Lüks Otellər',    'ru' => 'Люкс Отели',         'en' => 'Luxury Hotels'],
                'slug' => 'luxury-hotels',
                'icon' => 'heroicon-o-star',
                'sort' => 2,
            ],
            [
                'type' => ListingType::Hotel,
                'name' => ['az' => 'Büdcəli Otellər', 'ru' => 'Бюджетные Отели',    'en' => 'Budget Hotels'],
                'slug' => 'budget-hotels',
                'icon' => 'heroicon-o-currency-dollar',
                'sort' => 3,
            ],
            [
                'type' => ListingType::Hotel,
                'name' => ['az' => 'Kurortlar',       'ru' => 'Курорты',             'en' => 'Resorts'],
                'slug' => 'resorts',
                'icon' => 'heroicon-o-sun',
                'sort' => 4,
            ],
            [
                'type' => ListingType::Hotel,
                'name' => ['az' => 'Hoster',          'ru' => 'Хостелы',             'en' => 'Hostels'],
                'slug' => 'hostels',
                'icon' => 'heroicon-o-home-modern',
                'sort' => 5,
            ],

            // ---- Homes ----
            [
                'type' => ListingType::Home,
                'name' => ['az' => 'Mənzillər',       'ru' => 'Квартиры',            'en' => 'Apartments'],
                'slug' => 'apartments',
                'icon' => 'heroicon-o-building-office-2',
                'sort' => 1,
            ],
            [
                'type' => ListingType::Home,
                'name' => ['az' => 'Kənd Evləri',     'ru' => 'Загородные Дома',     'en' => 'Country Houses'],
                'slug' => 'country-houses',
                'icon' => 'heroicon-o-home',
                'sort' => 2,
            ],
            [
                'type' => ListingType::Home,
                'name' => ['az' => 'Villalar',        'ru' => 'Виллы',               'en' => 'Villas'],
                'slug' => 'villas',
                'icon' => 'heroicon-o-building-library',
                'sort' => 3,
            ],
            [
                'type' => ListingType::Home,
                'name' => ['az' => 'Studiyalar',      'ru' => 'Студии',              'en' => 'Studios'],
                'slug' => 'studios',
                'icon' => 'heroicon-o-squares-2x2',
                'sort' => 4,
            ],

            // ---- Tours ----
            [
                'type' => ListingType::Tour,
                'name' => ['az' => 'Tarixi Turlar',   'ru' => 'Исторические Туры',   'en' => 'Historical Tours'],
                'slug' => 'historical-tours',
                'icon' => 'heroicon-o-academic-cap',
                'sort' => 1,
            ],
            [
                'type' => ListingType::Tour,
                'name' => ['az' => 'Təbiət Turları',  'ru' => 'Туры на Природу',     'en' => 'Nature Tours'],
                'slug' => 'nature-tours',
                'icon' => 'heroicon-o-globe-americas',
                'sort' => 2,
            ],
            [
                'type' => ListingType::Tour,
                'name' => ['az' => 'Şəhər Turları',   'ru' => 'Городские Туры',      'en' => 'City Tours'],
                'slug' => 'city-tours',
                'icon' => 'heroicon-o-map',
                'sort' => 3,
            ],
            [
                'type' => ListingType::Tour,
                'name' => ['az' => 'Şərab Turları',   'ru' => 'Винные Туры',         'en' => 'Wine Tours'],
                'slug' => 'wine-tours',
                'icon' => 'heroicon-o-beaker',
                'sort' => 4,
            ],
            [
                'type' => ListingType::Tour,
                'name' => ['az' => 'İpək Yolu Turları', 'ru' => 'Туры по Шёлковому Пути', 'en' => 'Silk Road Tours'],
                'slug' => 'silk-road-tours',
                'icon' => 'heroicon-o-arrow-path',
                'sort' => 5,
            ],

            // ---- Activities ----
            [
                'type' => ListingType::Activity,
                'name' => ['az' => 'Dağ Gəzintisi',  'ru' => 'Пешие Походы',        'en' => 'Hiking'],
                'slug' => 'hiking',
                'icon' => 'heroicon-o-map-pin',
                'sort' => 1,
            ],
            [
                'type' => ListingType::Activity,
                'name' => ['az' => 'Xizək',           'ru' => 'Лыжи',                'en' => 'Skiing'],
                'slug' => 'skiing',
                'icon' => 'heroicon-o-bolt',
                'sort' => 2,
            ],
            [
                'type' => ListingType::Activity,
                'name' => ['az' => 'Su Idmanı',       'ru' => 'Водный Спорт',        'en' => 'Water Sports'],
                'slug' => 'water-sports',
                'icon' => 'heroicon-o-cloud',
                'sort' => 3,
            ],
            [
                'type' => ListingType::Activity,
                'name' => ['az' => 'Velosiped',       'ru' => 'Велосипед',           'en' => 'Cycling'],
                'slug' => 'cycling',
                'icon' => 'heroicon-o-arrow-path-rounded-square',
                'sort' => 4,
            ],
            [
                'type' => ListingType::Activity,
                'name' => ['az' => 'At Minmə',        'ru' => 'Верховая Езда',       'en' => 'Horse Riding'],
                'slug' => 'horse-riding',
                'icon' => 'heroicon-o-flag',
                'sort' => 5,
            ],

            // ---- Guides ----
            [
                'type' => ListingType::Guide,
                'name' => ['az' => 'Şəhər Bələdçisi', 'ru' => 'Городской Гид',      'en' => 'City Guide'],
                'slug' => 'city-guide',
                'icon' => 'heroicon-o-building-storefront',
                'sort' => 1,
            ],
            [
                'type' => ListingType::Guide,
                'name' => ['az' => 'Təbiət Bələdçisi', 'ru' => 'Природный Гид',     'en' => 'Nature Guide'],
                'slug' => 'nature-guide',
                'icon' => 'heroicon-o-leaf',
                'sort' => 2,
            ],
            [
                'type' => ListingType::Guide,
                'name' => ['az' => 'Mədəni Bələdçi',  'ru' => 'Культурный Гид',     'en' => 'Cultural Guide'],
                'slug' => 'cultural-guide',
                'icon' => 'heroicon-o-musical-note',
                'sort' => 3,
            ],
            [
                'type' => ListingType::Guide,
                'name' => ['az' => 'Macəra Bələdçisi', 'ru' => 'Гид Приключений',   'en' => 'Adventure Guide'],
                'slug' => 'adventure-guide',
                'icon' => 'heroicon-o-fire',
                'sort' => 4,
            ],

            // ---- Restaurants ----
            [
                'type' => ListingType::Restaurant,
                'name' => ['az' => 'Azərbaycan Mətbəxi', 'ru' => 'Азербайджанская Кухня', 'en' => 'Azerbaijani Cuisine'],
                'slug' => 'azerbaijani-cuisine',
                'icon' => 'heroicon-o-cake',
                'sort' => 1,
            ],
            [
                'type' => ListingType::Restaurant,
                'name' => ['az' => 'Beynəlxalq Mətbəx', 'ru' => 'Международная Кухня', 'en' => 'International Cuisine'],
                'slug' => 'international-cuisine',
                'icon' => 'heroicon-o-globe-alt',
                'sort' => 2,
            ],
            [
                'type' => ListingType::Restaurant,
                'name' => ['az' => 'Kafeler',          'ru' => 'Кафе',               'en' => 'Cafes'],
                'slug' => 'cafes',
                'icon' => 'heroicon-o-sparkles',
                'sort' => 3,
            ],
            [
                'type' => ListingType::Restaurant,
                'name' => ['az' => 'Fast Food',        'ru' => 'Фаст Фуд',           'en' => 'Fast Food'],
                'slug' => 'fast-food',
                'icon' => 'heroicon-o-clock',
                'sort' => 4,
            ],
            [
                'type' => ListingType::Restaurant,
                'name' => ['az' => 'Elit Restoranlar', 'ru' => 'Элитные Рестораны',  'en' => 'Fine Dining'],
                'slug' => 'fine-dining',
                'icon' => 'heroicon-o-trophy',
                'sort' => 5,
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'listing_type' => $cat['type'],
                    'parent_id'    => null,
                    'name'         => $cat['name'],
                    'slug'         => $cat['slug'],
                    'icon'         => $cat['icon'],
                    'sort_order'   => $cat['sort'],
                ]
            );
        }
    }
}
