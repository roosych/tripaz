<?php

namespace Database\Seeders;

use App\Enums\ListingType;
use App\Models\Amenity;
use App\Models\AmenityGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Amenity::truncate();
        AmenityGroup::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ------------------------------------------------------------------
        // 1. Create amenity groups (name is translatable JSON)
        // ------------------------------------------------------------------

        $groupDefs = [
            'connectivity'  => ['az' => 'İnternet',          'ru' => 'Интернет',               'en' => 'Internet'],
            'transport'     => ['az' => 'Nəqliyyat',         'ru' => 'Транспорт',              'en' => 'Transport'],
            'facilities'    => ['az' => 'Otaq İmkanları',    'ru' => 'Удобства',               'en' => 'Facilities'],
            'wellness'      => ['az' => 'Sağlamlıq',         'ru' => 'Здоровье и Wellness',    'en' => 'Wellness'],
            'dining'        => ['az' => 'Yemək',             'ru' => 'Питание',                'en' => 'Dining'],
            'services'      => ['az' => 'Xidmətlər',         'ru' => 'Услуги',                 'en' => 'Services'],
            'appliances'    => ['az' => 'Texnika',           'ru' => 'Техника',                'en' => 'Appliances'],
            'comfort'       => ['az' => 'Rahatlıq',          'ru' => 'Комфорт',                'en' => 'Comfort'],
            'entertainment' => ['az' => 'Əyləncə',           'ru' => 'Развлечения',            'en' => 'Entertainment'],
            'outdoor'       => ['az' => 'Açıq Hava',         'ru' => 'На Улице',               'en' => 'Outdoor'],
            'safety'        => ['az' => 'Təhlükəsizlik',     'ru' => 'Безопасность',           'en' => 'Safety'],
            'gear'          => ['az' => 'Avadanlıq',         'ru' => 'Снаряжение',             'en' => 'Gear'],
            'skills'        => ['az' => 'Bacarıqlar',        'ru' => 'Навыки',                 'en' => 'Skills'],
            'credentials'   => ['az' => 'Sertifikatlar',     'ru' => 'Сертификаты',            'en' => 'Credentials'],
            'food'          => ['az' => 'Qida',              'ru' => 'Питание (тип)',          'en' => 'Food'],
        ];

        $sortOrder = 1;
        $groups = [];
        foreach ($groupDefs as $key => $name) {
            $groups[$key] = AmenityGroup::create([
                'name'       => $name,
                'sort_order' => $sortOrder++,
            ]);
        }

        // ------------------------------------------------------------------
        // 2. Create amenities referencing groups via amenity_group_id
        // ------------------------------------------------------------------

        $amenities = [
            // ---- Hotels ----
            ['type' => ListingType::Hotel, 'name' => ['az' => 'Pulsuz Wi-Fi',            'ru' => 'Бесплатный Wi-Fi',                  'en' => 'Free Wi-Fi'],             'icon' => 'heroicon-o-wifi',                'group' => 'connectivity'],
            ['type' => ListingType::Hotel, 'name' => ['az' => 'Avtomobil Parkı',         'ru' => 'Парковка',                          'en' => 'Parking'],                'icon' => 'heroicon-o-truck',               'group' => 'transport'],
            ['type' => ListingType::Hotel, 'name' => ['az' => 'Üzgüçülük Hovuzu',        'ru' => 'Бассейн',                           'en' => 'Swimming Pool'],           'icon' => 'heroicon-o-sun',                 'group' => 'facilities'],
            ['type' => ListingType::Hotel, 'name' => ['az' => 'Spa',                     'ru' => 'Спа',                               'en' => 'Spa'],                    'icon' => 'heroicon-o-sparkles',            'group' => 'wellness'],
            ['type' => ListingType::Hotel, 'name' => ['az' => 'Restoran',                'ru' => 'Ресторан',                          'en' => 'Restaurant'],             'icon' => 'heroicon-o-cake',                'group' => 'dining'],
            ['type' => ListingType::Hotel, 'name' => ['az' => 'Bar',                     'ru' => 'Бар',                               'en' => 'Bar'],                    'icon' => 'heroicon-o-beaker',              'group' => 'dining'],
            ['type' => ListingType::Hotel, 'name' => ['az' => 'Fitnes Zalı',             'ru' => 'Фитнес-Зал',                        'en' => 'Gym'],                    'icon' => 'heroicon-o-fire',                'group' => 'wellness'],
            ['type' => ListingType::Hotel, 'name' => ['az' => 'Hava Limanı Transferi',   'ru' => 'Трансфер из Аэропорта',             'en' => 'Airport Transfer'],       'icon' => 'heroicon-o-paper-airplane',      'group' => 'transport'],
            ['type' => ListingType::Hotel, 'name' => ['az' => '24 Saat Resepsiya',       'ru' => 'Круглосуточная Стойка Регистрации', 'en' => '24h Reception'],          'icon' => 'heroicon-o-clock',               'group' => 'services'],

            // ---- Homes ----
            ['type' => ListingType::Home,  'name' => ['az' => 'Wi-Fi',                   'ru' => 'Wi-Fi',                             'en' => 'Wi-Fi'],                  'icon' => 'heroicon-o-wifi',                'group' => 'connectivity'],
            ['type' => ListingType::Home,  'name' => ['az' => 'Mətbəx',                  'ru' => 'Кухня',                             'en' => 'Kitchen'],                'icon' => 'heroicon-o-cake',                'group' => 'facilities'],
            ['type' => ListingType::Home,  'name' => ['az' => 'Çamaşır Maşını',          'ru' => 'Стиральная Машина',                 'en' => 'Washing Machine'],        'icon' => 'heroicon-o-arrow-path',          'group' => 'appliances'],
            ['type' => ListingType::Home,  'name' => ['az' => 'Kondisioner',             'ru' => 'Кондиционер',                       'en' => 'Air Conditioning'],       'icon' => 'heroicon-o-cloud',               'group' => 'comfort'],
            ['type' => ListingType::Home,  'name' => ['az' => 'Televizor',               'ru' => 'Телевизор',                         'en' => 'TV'],                     'icon' => 'heroicon-o-tv',                  'group' => 'entertainment'],
            ['type' => ListingType::Home,  'name' => ['az' => 'Balkon',                  'ru' => 'Балкон',                            'en' => 'Balcony'],                'icon' => 'heroicon-o-home',                'group' => 'outdoor'],
            ['type' => ListingType::Home,  'name' => ['az' => 'Bağça',                   'ru' => 'Сад',                               'en' => 'Garden'],                 'icon' => 'heroicon-o-leaf',                'group' => 'outdoor'],
            ['type' => ListingType::Home,  'name' => ['az' => 'Barbekyu',                'ru' => 'Барбекю',                           'en' => 'BBQ'],                    'icon' => 'heroicon-o-fire',                'group' => 'outdoor'],

            // ---- Tours ----
            ['type' => ListingType::Tour,  'name' => ['az' => 'Bələdçi Daxildir',        'ru' => 'Гид Включён',                       'en' => 'Guide Included'],         'icon' => 'heroicon-o-user',                'group' => 'services'],
            ['type' => ListingType::Tour,  'name' => ['az' => 'Nəqliyyat',               'ru' => 'Транспорт',                         'en' => 'Transport'],              'icon' => 'heroicon-o-truck',               'group' => 'transport'],
            ['type' => ListingType::Tour,  'name' => ['az' => 'Yeməklər Daxildir',       'ru' => 'Питание Включено',                  'en' => 'Meals Included'],         'icon' => 'heroicon-o-cake',                'group' => 'dining'],
            ['type' => ListingType::Tour,  'name' => ['az' => 'Sigorta',                 'ru' => 'Страховка',                         'en' => 'Insurance'],              'icon' => 'heroicon-o-shield-check',        'group' => 'safety'],
            ['type' => ListingType::Tour,  'name' => ['az' => 'Avadanlıq',               'ru' => 'Оборудование',                      'en' => 'Equipment'],              'icon' => 'heroicon-o-wrench',              'group' => 'gear'],

            // ---- Activities ----
            ['type' => ListingType::Activity, 'name' => ['az' => 'Avadanlıq Verilir',       'ru' => 'Оборудование Предоставляется', 'en' => 'Equipment Provided'],     'icon' => 'heroicon-o-wrench-screwdriver',  'group' => 'gear'],
            ['type' => ListingType::Activity, 'name' => ['az' => 'Müəllim',                 'ru' => 'Инструктор',                   'en' => 'Instructor'],             'icon' => 'heroicon-o-user',                'group' => 'services'],
            ['type' => ListingType::Activity, 'name' => ['az' => 'Təhlükəsizlik Avadanlığı','ru' => 'Защитное Снаряжение',          'en' => 'Safety Gear'],            'icon' => 'heroicon-o-shield-check',        'group' => 'safety'],
            ['type' => ListingType::Activity, 'name' => ['az' => 'Soyunma Otaqları',         'ru' => 'Раздевалки',                  'en' => 'Changing Rooms'],         'icon' => 'heroicon-o-home-modern',         'group' => 'facilities'],

            // ---- Guides ----
            ['type' => ListingType::Guide, 'name' => ['az' => 'Çoxdillilik',             'ru' => 'Многоязычность',                    'en' => 'Multilingual'],           'icon' => 'heroicon-o-language',            'group' => 'skills'],
            ['type' => ListingType::Guide, 'name' => ['az' => 'Lisenziyalı',             'ru' => 'Лицензированный',                   'en' => 'Licensed'],               'icon' => 'heroicon-o-check-badge',         'group' => 'credentials'],
            ['type' => ListingType::Guide, 'name' => ['az' => 'Avtomobil Mövcuddur',     'ru' => 'Наличие Автомобиля',                'en' => 'Vehicle Available'],      'icon' => 'heroicon-o-truck',               'group' => 'transport'],
            ['type' => ListingType::Guide, 'name' => ['az' => 'Fotoqrafiya',             'ru' => 'Фотография',                        'en' => 'Photography'],            'icon' => 'heroicon-o-camera',              'group' => 'skills'],

            // ---- Restaurants ----
            ['type' => ListingType::Restaurant, 'name' => ['az' => 'Açıq Oturma Yeri',  'ru' => 'Уличные Места',                     'en' => 'Outdoor Seating'],        'icon' => 'heroicon-o-sun',                 'group' => 'facilities'],
            ['type' => ListingType::Restaurant, 'name' => ['az' => 'Çatdırılma',         'ru' => 'Доставка',                          'en' => 'Delivery'],               'icon' => 'heroicon-o-truck',               'group' => 'services'],
            ['type' => ListingType::Restaurant, 'name' => ['az' => 'Götürmə',            'ru' => 'Навынос',                           'en' => 'Takeaway'],               'icon' => 'heroicon-o-shopping-bag',        'group' => 'services'],
            ['type' => ListingType::Restaurant, 'name' => ['az' => 'Rezervasiya',        'ru' => 'Бронирование',                      'en' => 'Reservation'],            'icon' => 'heroicon-o-calendar',            'group' => 'services'],
            ['type' => ListingType::Restaurant, 'name' => ['az' => 'Halal',              'ru' => 'Халяль',                            'en' => 'Halal'],                  'icon' => 'heroicon-o-check-circle',        'group' => 'food'],
            ['type' => ListingType::Restaurant, 'name' => ['az' => 'Canlı Musiqi',       'ru' => 'Живая Музыка',                      'en' => 'Live Music'],             'icon' => 'heroicon-o-musical-note',        'group' => 'entertainment'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create([
                'listing_type'    => $amenity['type'],
                'name'            => $amenity['name'],
                'icon'            => $amenity['icon'],
                'amenity_group_id' => $groups[$amenity['group']]->id,
            ]);
        }
    }
}
