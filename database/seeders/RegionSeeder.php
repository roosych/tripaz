<?php

namespace Database\Seeders;

use App\Enums\RegionType;
use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Country
        $azerbaijan = Region::firstOrCreate(
            ['slug' => 'azerbaijan'],
            [
                'parent_id' => null,
                'name'      => ['az' => 'Azərbaycan', 'ru' => 'Азербайджан', 'en' => 'Azerbaijan'],
                'type'      => RegionType::Country,
            ]
        );

        // 2. Cities / regions (children of Azerbaijan)
        $cities = [
            [
                'name' => ['az' => 'Bakı',       'ru' => 'Баку',         'en' => 'Baku'],
                'slug' => 'baku',
                'type' => RegionType::City,
            ],
            [
                'name' => ['az' => 'Gəncə',      'ru' => 'Гянджа',       'en' => 'Ganja'],
                'slug' => 'ganja',
                'type' => RegionType::City,
            ],
            [
                'name' => ['az' => 'Şəki',       'ru' => 'Шеки',         'en' => 'Sheki'],
                'slug' => 'sheki',
                'type' => RegionType::City,
            ],
            [
                'name' => ['az' => 'Quba',       'ru' => 'Куба',         'en' => 'Quba'],
                'slug' => 'quba',
                'type' => RegionType::City,
            ],
            [
                'name' => ['az' => 'Qəbələ',     'ru' => 'Габала',       'en' => 'Gabala'],
                'slug' => 'gabala',
                'type' => RegionType::City,
            ],
            [
                'name' => ['az' => 'Lənkəran',   'ru' => 'Ленкорань',    'en' => 'Lankaran'],
                'slug' => 'lankaran',
                'type' => RegionType::City,
            ],
            [
                'name' => ['az' => 'Şamaxı',     'ru' => 'Шамахы',       'en' => 'Shamakhi'],
                'slug' => 'shamakhi',
                'type' => RegionType::District,
            ],
            [
                'name' => ['az' => 'Naxçıvan',   'ru' => 'Нахчыван',     'en' => 'Nakhchivan'],
                'slug' => 'nakhchivan',
                'type' => RegionType::Region,
            ],
            [
                'name' => ['az' => 'Mingəçevir', 'ru' => 'Мингячевир',   'en' => 'Mingachevir'],
                'slug' => 'mingachevir',
                'type' => RegionType::City,
            ],
            [
                'name' => ['az' => 'Göygöl',     'ru' => 'Гейгёль',      'en' => 'Goygol'],
                'slug' => 'goygol',
                'type' => RegionType::District,
            ],
        ];

        foreach ($cities as $city) {
            Region::firstOrCreate(
                ['slug' => $city['slug']],
                array_merge($city, ['parent_id' => $azerbaijan->id])
            );
        }
    }
}
