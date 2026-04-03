<?php

namespace Database\Factories;

use App\Enums\ActivityDifficulty;
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\Locale;
use App\Enums\PriceRange;
use App\Enums\PropertyType;
use App\Enums\TourDifficulty;
use App\Models\ActivityDetail;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\GuideDetail;
use App\Models\HomeDetail;
use App\Models\HotelDetail;
use App\Models\Listing;
use App\Models\ListingLocation;
use App\Models\ListingMedia;
use App\Models\ListingTag;
use App\Models\ListingTranslation;
use App\Models\Region;
use App\Models\RestaurantDetail;
use App\Models\TourDetail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    protected $model = Listing::class;

    // -------------------------------------------------------------------------
    // Azerbaijan city coordinate data
    // -------------------------------------------------------------------------

    private array $cityCoords = [
        'baku'         => ['lat' => 40.4093, 'lng' => 49.8671, 'region' => 'Bakı'],
        'ganja'        => ['lat' => 40.6828, 'lng' => 46.3606, 'region' => 'Gəncə'],
        'sheki'        => ['lat' => 41.1993, 'lng' => 47.1706, 'region' => 'Şəki'],
        'quba'         => ['lat' => 41.3628, 'lng' => 48.5233, 'region' => 'Quba'],
        'gabala'       => ['lat' => 40.9967, 'lng' => 47.8415, 'region' => 'Qəbələ'],
        'lankaran'     => ['lat' => 38.7539, 'lng' => 48.8522, 'region' => 'Lənkəran'],
        'shamakhi'     => ['lat' => 40.6319, 'lng' => 48.6373, 'region' => 'Şamaxı'],
        'nakhchivan'   => ['lat' => 39.2092, 'lng' => 45.4122, 'region' => 'Naxçıvan'],
        'mingachevir'  => ['lat' => 40.7704, 'lng' => 47.0566, 'region' => 'Mingəçevir'],
        'goygol'       => ['lat' => 40.5897, 'lng' => 46.3244, 'region' => 'Göygöl'],
    ];

    // -------------------------------------------------------------------------
    // Hotel listing titles (az / ru / en)
    // -------------------------------------------------------------------------

    private array $hotelTitles = [
        ['az' => 'Fairmont Bakı Oteli', 'ru' => 'Отель Fairmont Баку', 'en' => 'Fairmont Baku Hotel'],
        ['az' => 'Qafqaz Riverside Hotel', 'ru' => 'Отель Кафказ Ривесайд', 'en' => 'Qafqaz Riverside Hotel'],
        ['az' => 'Park Inn Bakı', 'ru' => 'Park Inn Баку', 'en' => 'Park Inn by Radisson Baku'],
        ['az' => 'Şah Sarayı Oteli', 'ru' => 'Отель Шах Палас', 'en' => 'Shah Palace Hotel'],
        ['az' => 'Quba Saray Oteli', 'ru' => 'Отель Куба Палас', 'en' => 'Quba Palace Hotel'],
        ['az' => 'Şəki Saray Oteli', 'ru' => 'Отель Шеки Сарай', 'en' => 'Sheki Saray Hotel'],
        ['az' => 'Boulevard Hotel Bakı', 'ru' => 'Boulevard Hotel Баку', 'en' => 'Boulevard Hotel Baku'],
        ['az' => 'JW Marriott Abşeron Bakı', 'ru' => 'JW Marriott Абшерон Баку', 'en' => 'JW Marriott Absheron Baku'],
        ['az' => 'Hyatt Regency Bakı', 'ru' => 'Hyatt Regency Баку', 'en' => 'Hyatt Regency Baku'],
        ['az' => 'Hilton Bakı', 'ru' => 'Хилтон Баку', 'en' => 'Hilton Baku'],
        ['az' => 'Vego Hotel Bakı', 'ru' => 'Отель Вего Баку', 'en' => 'Vego Hotel Baku'],
        ['az' => 'Lezgi Hotel Quba', 'ru' => 'Отель Лезги Куба', 'en' => 'Lezgi Hotel Quba'],
        ['az' => 'Gabala İnn Otel', 'ru' => 'Gabala Inn Отель', 'en' => 'Gabala Inn Hotel'],
        ['az' => 'Nakhchivan Hotel', 'ru' => 'Отель Нахчыван', 'en' => 'Nakhchivan Hotel'],
        ['az' => 'Qoşa Qala Hotel Şəki', 'ru' => 'Отель Гоша Гала Шеки', 'en' => 'Gosha Gala Hotel Sheki'],
        ['az' => 'Mingəçevir Resort Hotel', 'ru' => 'Мингячевир Резорт Отель', 'en' => 'Mingachevir Resort Hotel'],
        ['az' => 'Şirvan Park Hotel', 'ru' => 'Ширван Парк Отель', 'en' => 'Shirvan Park Hotel'],
        ['az' => 'Grand Hotel Gəncə', 'ru' => 'Гранд Отель Гянджа', 'en' => 'Grand Hotel Ganja'],
        ['az' => 'Lankaran Resort Hotel', 'ru' => 'Ленкорань Резорт Отель', 'en' => 'Lankaran Resort Hotel'],
        ['az' => 'Qəbələ Palace Hotel', 'ru' => 'Габала Палас Отель', 'en' => 'Gabala Palace Hotel'],
        ['az' => 'Naftalan Resort Hotel', 'ru' => 'Нафталан Резорт Отель', 'en' => 'Naftalan Resort Hotel'],
        ['az' => 'İçərişəhər Butik Oteli', 'ru' => 'Бутик Отель Ичери Шехер', 'en' => 'Icheri Sheher Boutique Hotel'],
        ['az' => 'Caspian Sea Hotel', 'ru' => 'Каспийский Морской Отель', 'en' => 'Caspian Sea Hotel'],
        ['az' => 'Azerbaycan Hotel Bakı', 'ru' => 'Отель Азербайджан Баку', 'en' => 'Azerbaijan Hotel Baku'],
        ['az' => 'Xəzər Sahili Hotel', 'ru' => 'Отель Хазар Сахили', 'en' => 'Khazar Shore Hotel'],
        ['az' => 'Divan Hotel Bakı', 'ru' => 'Диван Отель Баку', 'en' => 'Divan Hotel Baku'],
        ['az' => 'Boutique Hotel 1794 Bakı', 'ru' => 'Бутик Отель 1794 Баку', 'en' => 'Boutique Hotel 1794 Baku'],
        ['az' => 'Radisson Blu Hotel Bakı', 'ru' => 'Рэдиссон Блю Отель Баку', 'en' => 'Radisson Blu Hotel Baku'],
        ['az' => 'Qafqaz Resort Hotel', 'ru' => 'Кафказ Резорт Отель', 'en' => 'Qafqaz Resort Hotel'],
        ['az' => 'Şuşa City Hotel', 'ru' => 'Шуша Сити Отель', 'en' => 'Shusha City Hotel'],
        ['az' => 'Excelsior Hotel Bakı', 'ru' => 'Эксельсиор Отель Баку', 'en' => 'Excelsior Hotel Baku'],
        ['az' => 'Aprel Hotel Bakı', 'ru' => 'Апрель Отель Баку', 'en' => 'Aprel Hotel Baku'],
        ['az' => 'İstirahət Mərkəzi Qəbələ', 'ru' => 'Центр Отдыха Габала', 'en' => 'Gabala Recreation Center'],
        ['az' => 'Göygöl Resort Hotel', 'ru' => 'Гейгёль Резорт Отель', 'en' => 'Goygol Resort Hotel'],
        ['az' => 'Qəbələ İnn Butik Otel', 'ru' => 'Габала Инн Бутик Отель', 'en' => 'Gabala Inn Boutique Hotel'],
        ['az' => 'Gəncə İnn Hotel', 'ru' => 'Гянджа Инн Отель', 'en' => 'Ganja Inn Hotel'],
        ['az' => 'Nobel Court Hotel Bakı', 'ru' => 'Нобель Корт Отель Баку', 'en' => 'Nobel Court Hotel Baku'],
        ['az' => 'Sultan İnn Butik Otel', 'ru' => 'Султан Инн Бутик Отель', 'en' => 'Sultan Inn Boutique Hotel'],
        ['az' => 'Marriott Courtyard Bakı', 'ru' => 'Марриотт Кортyard Баку', 'en' => 'Marriott Courtyard Baku'],
        ['az' => 'Abşeron Hotel Bakı', 'ru' => 'Отель Абшерон Баку', 'en' => 'Absheron Hotel Baku'],
        ['az' => 'Gülüstan Hotel Şamaxı', 'ru' => 'Отель Гюлюстан Шамахы', 'en' => 'Gulistan Hotel Shamakhi'],
        ['az' => 'Xınalıq Hotel Quba', 'ru' => 'Хыналыг Отель Куба', 'en' => 'Khinaliq Hotel Quba'],
        ['az' => 'Silk Road Lodge Şəki', 'ru' => 'Силк Роуд Лодж Шеки', 'en' => 'Silk Road Lodge Sheki'],
        ['az' => 'Old City Hotel Bakı', 'ru' => 'Олд Сити Отель Баку', 'en' => 'Old City Hotel Baku'],
        ['az' => 'Palms Hotel Bakı', 'ru' => 'Палмс Отель Баку', 'en' => 'Palms Hotel Baku'],
        ['az' => 'Majestic Hotel Bakı', 'ru' => 'Мажестик Отель Баку', 'en' => 'Majestic Hotel Baku'],
        ['az' => 'Caspian Riviera Grand Palace Hotel', 'ru' => 'Каспийская Ривьера Гранд Палас Отель', 'en' => 'Caspian Riviera Grand Palace Hotel'],
        ['az' => 'İpək Yolu Oteli Şəki', 'ru' => 'Отель Шёлковый Путь Шеки', 'en' => 'Silk Road Hotel Sheki'],
        ['az' => 'Kür Sahili Hotel Mingəçevir', 'ru' => 'Отель Кур Сахили Мингячевир', 'en' => 'Kur Shore Hotel Mingachevir'],
        ['az' => 'Qarabağ Hotel Bakı', 'ru' => 'Отель Карабах Баку', 'en' => 'Karabakh Hotel Baku'],
    ];

    // -------------------------------------------------------------------------
    // Home listing titles
    // -------------------------------------------------------------------------

    private array $homeTitles = [
        ['az' => 'Bakı Şəhər Mərkəzində Müasir Mənzil', 'ru' => 'Современная Квартира в Центре Баку', 'en' => 'Modern Apartment in Baku City Center'],
        ['az' => 'Xəzər Mənzərəli Penthouse', 'ru' => 'Пентхаус с Видом на Каспий', 'en' => 'Penthouse with Caspian Sea View'],
        ['az' => 'Qəbələ Dağ Villası', 'ru' => 'Горная Вилла в Габала', 'en' => 'Mountain Villa in Gabala'],
        ['az' => 'Şəki Tarixi Ev', 'ru' => 'Исторический Дом в Шеки', 'en' => 'Historic House in Sheki'],
        ['az' => 'Lənkəran Çay Bağı Evi', 'ru' => 'Чайный Домик в Ленкорани', 'en' => 'Tea Garden House in Lankaran'],
        ['az' => 'Nauç Meşəsindəki Kottec', 'ru' => 'Коттедж у Леса Науч', 'en' => 'Forest Cottage in Nauch'],
        ['az' => 'Gəncə Bağ Evi', 'ru' => 'Дачный Дом в Гяндже', 'en' => 'Country House in Ganja'],
        ['az' => 'Mingəçevir Gölü Villası', 'ru' => 'Вилла у Озера Мингячевир', 'en' => 'Lake Villa in Mingachevir'],
        ['az' => 'Bakı Bulvar Studiyası', 'ru' => 'Студия на Бакинском Бульваре', 'en' => 'Baku Boulevard Studio'],
        ['az' => 'Naxçıvan Tarixi Mənzil', 'ru' => 'Исторические Апартаменты Нахчыван', 'en' => 'Nakhchivan Historical Apartment'],
        ['az' => 'Quba Dağ Kotteci', 'ru' => 'Горный Коттедж в Кубе', 'en' => 'Mountain Cottage in Quba'],
        ['az' => 'Şamaxı Bağ Evi', 'ru' => 'Садовый Дом в Шамахы', 'en' => 'Garden House in Shamakhi'],
        ['az' => 'İçərişəhər Butik Mənzil', 'ru' => 'Бутик Апартаменты в Ичери Шехер', 'en' => 'Boutique Apartment in Old City'],
        ['az' => 'Abşeron Bağ Evi', 'ru' => 'Садовый Дом в Абшероне', 'en' => 'Garden House in Absheron'],
        ['az' => 'Göygöl Meşə Villası', 'ru' => 'Лесная Вилла Гейгёль', 'en' => 'Goygol Forest Villa'],
        ['az' => 'Bakı Xəzər Sahili Mənzil', 'ru' => 'Апартаменты на Берегу Каспия Баку', 'en' => 'Caspian Shore Apartment Baku'],
        ['az' => 'Qəbələ Meşə Kotteci', 'ru' => 'Лесной Коттедж Габала', 'en' => 'Gabala Forest Cottage'],
        ['az' => 'Şəki Karvan Evi', 'ru' => 'Дом Каравана Шеки', 'en' => 'Sheki Caravanserai House'],
        ['az' => 'Lənkəran Subtropik Villa', 'ru' => 'Субтропическая Вилла Ленкорань', 'en' => 'Lankaran Subtropical Villa'],
        ['az' => 'Gəncə Şəhər Mərkəzi Mənzil', 'ru' => 'Апартаменты в Центре Гянджи', 'en' => 'Ganja City Center Apartment'],
        ['az' => 'Bakı İşgüzar Mərkəz Studiyası', 'ru' => 'Студия у Бизнес Центра Баку', 'en' => 'Baku Business Center Studio'],
        ['az' => 'Novxanı Bağ Evi', 'ru' => 'Дача в Новханы', 'en' => 'Novkhani Dacha'],
        ['az' => 'Pirəkəşkül Yay Evi', 'ru' => 'Летний Дом в Пиракешкюль', 'en' => 'Pirekeshkul Summer House'],
        ['az' => 'Zaqatala Dağ Kotteci', 'ru' => 'Горный Коттедж Закаталы', 'en' => 'Zagatala Mountain Cottage'],
        ['az' => 'Xınalıq Autentik Ev', 'ru' => 'Аутентичный Дом Хыналыг', 'en' => 'Khinaliq Authentic House'],
        ['az' => 'Qəbələ Orman Çaleti', 'ru' => 'Лесное Шале Габала', 'en' => 'Gabala Forest Chalet'],
        ['az' => 'Bakı Neft Daşları Mənzil', 'ru' => 'Апартаменты Нефтяные Камни Баку', 'en' => 'Baku Oil Rocks View Apartment'],
        ['az' => 'Biləsuvar Kənd Evi', 'ru' => 'Деревенский Дом Билясувар', 'en' => 'Bilasuvar Village House'],
        ['az' => 'Göyçay Bağ Evi', 'ru' => 'Садовый Дом Гейчай', 'en' => 'Goychay Garden House'],
        ['az' => 'Şəmkir Üzümlük Villası', 'ru' => 'Виноградная Вилла Шамкир', 'en' => 'Shamkir Vineyard Villa'],
        ['az' => 'Bakı Flame Towers Mənzil', 'ru' => 'Апартаменты Баку Флейм Тауэрс', 'en' => 'Baku Flame Towers Apartment'],
        ['az' => 'Quba Şəlalə Villası', 'ru' => 'Вилла у Водопада Куба', 'en' => 'Quba Waterfall Villa'],
        ['az' => 'Masallı Subtropik Evi', 'ru' => 'Субтропический Дом Масаллы', 'en' => 'Masalli Subtropical House'],
        ['az' => 'Astara Dəniz Kənarı Mənzil', 'ru' => 'Квартира у Моря Астара', 'en' => 'Astara Seaside Apartment'],
        ['az' => 'Sabirabad Bağ Evi', 'ru' => 'Садовый Дом Сабирабад', 'en' => 'Sabirabad Garden House'],
        ['az' => 'Mingəçevir Kənar Mənzil', 'ru' => 'Квартира на Окраине Мингячевира', 'en' => 'Mingachevir Riverside Apartment'],
        ['az' => 'Ağsu Dağ Kotteci', 'ru' => 'Горный Коттедж Агсу', 'en' => 'Agsu Mountain Cottage'],
        ['az' => 'İsmayıllı Dağ Evi', 'ru' => 'Горный Дом Исмаиллы', 'en' => 'Ismayilli Mountain House'],
        ['az' => 'Qax Meşə Kotteci', 'ru' => 'Лесной Коттедж Гах', 'en' => 'Gakh Forest Cottage'],
        ['az' => 'Şəki İpək Evi', 'ru' => 'Шёлковый Дом Шеки', 'en' => 'Sheki Silk House'],
        ['az' => 'Bakı Sabunçu Mənzil', 'ru' => 'Апартаменты Сабунчу Баку', 'en' => 'Baku Sabunchu Apartment'],
        ['az' => 'Naxçıvan Dağ Kotteci', 'ru' => 'Горный Коттедж Нахчыван', 'en' => 'Nakhchivan Mountain Cottage'],
        ['az' => 'Oğuz Şəlalə Evi', 'ru' => 'Дом у Водопада Огуз', 'en' => 'Oguz Waterfall House'],
        ['az' => 'Balakən Meşə Villası', 'ru' => 'Лесная Вилла Балакен', 'en' => 'Balaken Forest Villa'],
        ['az' => 'Tovuz Üzüm Bağı Evi', 'ru' => 'Виноградный Дом Товуз', 'en' => 'Tovuz Vineyard House'],
        ['az' => 'Qazax Tarixi Mənzil', 'ru' => 'Исторические Апартаменты Газах', 'en' => 'Gazakh Historical Apartment'],
        ['az' => 'Salyan Balıqçı Evi', 'ru' => 'Рыбацкий Дом Сальян', 'en' => 'Salyan Fisherman House'],
        ['az' => 'Zərdab Kənd Kotteci', 'ru' => 'Сельский Коттедж Зардаб', 'en' => 'Zardab Village Cottage'],
        ['az' => 'Gədəbəy Dağ Çaleti', 'ru' => 'Горное Шале Гедабей', 'en' => 'Gedabey Mountain Chalet'],
        ['az' => 'Cəlilabad Bağ Evi', 'ru' => 'Садовый Дом Джалилабад', 'en' => 'Jalilabad Garden House'],
    ];

    // -------------------------------------------------------------------------
    // Tour listing titles
    // -------------------------------------------------------------------------

    private array $tourTitles = [
        ['az' => 'Qobustan Milli Parkı Turu', 'ru' => 'Тур в Национальный Парк Гобустан', 'en' => 'Gobustan National Park Tour'],
        ['az' => 'Şöləli Qüllələr Gecə Turu', 'ru' => 'Ночной Тур к Пламенным Башням', 'en' => 'Flame Towers Night Tour'],
        ['az' => 'Şəki Tarixi Şəhər Turu', 'ru' => 'Исторический Тур по Шеки', 'en' => 'Sheki Historical City Tour'],
        ['az' => 'Şamaxı Şərab Turu', 'ru' => 'Винный Тур в Шамахы', 'en' => 'Wine Tour in Shamakhi'],
        ['az' => 'İpək Yolu Turu Şəki', 'ru' => 'Тур по Шёлковому Пути Шеки', 'en' => 'Silk Road Sheki Tour'],
        ['az' => 'Lahıc Kənd Turu', 'ru' => 'Тур в Деревню Лагич', 'en' => 'Lahic Village Tour'],
        ['az' => 'Heydər Əliyev Mərkəzi Turu', 'ru' => 'Тур по Центру Гейдара Алиева', 'en' => 'Heydar Aliyev Center Tour'],
        ['az' => 'İçərişəhər Bakı Turu', 'ru' => 'Тур по Старому Городу Баку', 'en' => 'Old City Baku Tour'],
        ['az' => 'Naxçıvan Arxeoloji Turu', 'ru' => 'Археологический Тур Нахчыван', 'en' => 'Nakhchivan Archaeological Tour'],
        ['az' => 'Xınalıq Kənd Turu', 'ru' => 'Тур в Деревню Хыналыг', 'en' => 'Khinaliq Village Tour'],
        ['az' => 'Quba Şəlalə Turu', 'ru' => 'Тур к Водопадам Кубы', 'en' => 'Quba Waterfall Tour'],
        ['az' => 'Abşeron Yarımadası Turu', 'ru' => 'Тур по Полуострову Абшерон', 'en' => 'Absheron Peninsula Tour'],
        ['az' => 'Göygöl Milli Parkı Turu', 'ru' => 'Тур в Национальный Парк Гейгёль', 'en' => 'Goygol National Park Tour'],
        ['az' => 'Zaqatala Dağ Turu', 'ru' => 'Горный Тур в Закаталы', 'en' => 'Zagatala Mountain Tour'],
        ['az' => 'Gəncə Tarixi Şəhər Turu', 'ru' => 'Исторический Тур по Гяндже', 'en' => 'Ganja Historical City Tour'],
        ['az' => 'Lənkəran Subtropik Tur', 'ru' => 'Субтропический Тур в Ленкорани', 'en' => 'Lankaran Subtropical Tour'],
        ['az' => 'Neft Muzeyləri Bakı Turu', 'ru' => 'Тур по Нефтяным Музеям Баку', 'en' => 'Baku Oil Museums Tour'],
        ['az' => 'Atəşgah Məbədi Turu', 'ru' => 'Тур к Храму Атешгях', 'en' => 'Ateshgah Fire Temple Tour'],
        ['az' => 'Qız Qalası Turu Bakı', 'ru' => 'Тур к Девичьей Башне Баку', 'en' => 'Maiden Tower Tour Baku'],
        ['az' => 'Nüvədi Kənd Naxçıvan Turu', 'ru' => 'Тур в Деревню Нювади Нахчыван', 'en' => 'Nuvadi Village Nakhchivan Tour'],
        ['az' => 'Azərbaycan Kulinariya Turu', 'ru' => 'Кулинарный Тур по Азербайджану', 'en' => 'Azerbaijan Culinary Tour'],
        ['az' => 'Bakı Modernizm Memarlıq Turu', 'ru' => 'Тур по Модернистской Архитектуре Баку', 'en' => 'Baku Modernist Architecture Tour'],
        ['az' => 'Şirvan Milli Parkı Turu', 'ru' => 'Тур в Национальный Парк Ширван', 'en' => 'Shirvan National Park Tour'],
        ['az' => 'Mardakan Qalalar Turu', 'ru' => 'Тур к Замкам Мардакан', 'en' => 'Mardakan Castles Tour'],
        ['az' => 'Bakı Gece Yarısı Turu', 'ru' => 'Полуночный Тур по Баку', 'en' => 'Baku Midnight Tour'],
        ['az' => 'Qax Meşə Turu', 'ru' => 'Лесной Тур в Гах', 'en' => 'Gakh Forest Tour'],
        ['az' => 'İlisu Şəlalə Turu', 'ru' => 'Тур к Водопаду Илысу', 'en' => 'Ilisu Waterfall Tour'],
        ['az' => 'Bakı Adalıq Turu', 'ru' => 'Островной Тур Баку', 'en' => 'Baku Island Tour'],
        ['az' => 'Bakı Xalça Muzeyii Turu', 'ru' => 'Тур по Бакинскому Ковровому Музею', 'en' => 'Baku Carpet Museum Tour'],
        ['az' => 'Əlvəndi Dağ Turu', 'ru' => 'Горный Тур Альвенды', 'en' => 'Alvendi Mountain Tour'],
        ['az' => 'Hacıqabul Neft Gölü Turu', 'ru' => 'Тур на Нефтяное Озеро Хаджигабул', 'en' => 'Hajigabul Oil Lake Tour'],
        ['az' => 'Ağdaş Tarixi Yerlər Turu', 'ru' => 'Исторические Места Агдаш', 'en' => 'Agdash Historical Sites Tour'],
        ['az' => 'Qəbələ Adrenalin Parkı Turu', 'ru' => 'Тур в Адреналин Парк Габала', 'en' => 'Gabala Adrenalin Park Tour'],
        ['az' => 'Kürdəmir Üzüm Bağı Turu', 'ru' => 'Виноградный Тур Кюрдамир', 'en' => 'Kurdamir Vineyard Tour'],
        ['az' => 'Pirəkəşkül Ekoloji Tur', 'ru' => 'Экологический Тур Пиракешкюль', 'en' => 'Pirekeshkul Eco Tour'],
        ['az' => 'Bərdə Tarixi Tur', 'ru' => 'Исторический Тур Барда', 'en' => 'Barda Historical Tour'],
        ['az' => 'Salyan Balıqçı Kəndi Turu', 'ru' => 'Тур в Рыбацкую Деревню Сальян', 'en' => 'Salyan Fishing Village Tour'],
        ['az' => 'Neftçala Dalğıclıq Turu', 'ru' => 'Тур по Дайвингу Нефтчала', 'en' => 'Neftchala Diving Tour'],
        ['az' => 'Bakı Köhnə Neft Sahəsi Turu', 'ru' => 'Тур на Старые Нефтяные Поля Баку', 'en' => 'Baku Old Oil Fields Tour'],
        ['az' => 'Şuşa Bərpa Edilmiş Şəhər Turu', 'ru' => 'Тур по Восстановленному Городу Шуша', 'en' => 'Shusha Restored City Tour'],
        ['az' => 'Muğan Düzü Ekoloji Turu', 'ru' => 'Экологический Тур Муганская Степь', 'en' => 'Mughan Steppe Eco Tour'],
        ['az' => 'Fotoqrafiya Turu Bakı', 'ru' => 'Фотографический Тур Баку', 'en' => 'Photography Tour Baku'],
        ['az' => 'Dağlıq Qarabağ Turu', 'ru' => 'Тур в Нагорный Карабах', 'en' => 'Karabakh Mountain Tour'],
        ['az' => 'Azərbaycan Üzümçülük Turu', 'ru' => 'Тур по Азербайджанскому Виноделию', 'en' => 'Azerbaijan Wine Country Tour'],
        ['az' => 'Yaylaq Turu Qəbələ', 'ru' => 'Тур на Горные Пастбища Габала', 'en' => 'Gabala Alpine Meadow Tour'],
        ['az' => 'Bakı Cümə Bazarı Turu', 'ru' => 'Тур на Пятничный Рынок Баку', 'en' => 'Baku Friday Market Tour'],
        ['az' => 'Şirvanşahlar Sarayı Turu', 'ru' => 'Тур во Дворец Ширваншахов', 'en' => 'Palace of Shirvanshahs Tour'],
        ['az' => 'Naxçıvan Əlincəqala Turu', 'ru' => 'Тур на Крепость Алинджагала Нахчыван', 'en' => 'Nakhchivan Alinjagala Fortress Tour'],
        ['az' => 'Duzdağ Yatağı Turu Naxçıvan', 'ru' => 'Тур к Соляным Горам Нахчыван', 'en' => 'Nakhchivan Salt Mountain Tour'],
        ['az' => 'Qrafik Sənət Bakı Turu', 'ru' => 'Тур по Графическому Искусству Баку', 'en' => 'Baku Graphic Arts Tour'],
    ];

    // -------------------------------------------------------------------------
    // Activity listing titles
    // -------------------------------------------------------------------------

    private array $activityTitles = [
        ['az' => 'Böyük Qafqaz Zirvəsinə Dırmaşmaq', 'ru' => 'Восхождение на Вершины Большого Кавказа', 'en' => 'Big Caucasus Summit Hiking'],
        ['az' => 'Şahdağ Ski Kurortunda Xizək Sürməsi', 'ru' => 'Катание на Лыжах в Курорте Шахдаг', 'en' => 'Shahdag Ski Resort Skiing'],
        ['az' => 'Xəzər Dənizində Su Sürfü', 'ru' => 'Серфинг на Каспийском Море', 'en' => 'Caspian Sea Surfing'],
        ['az' => 'Qəbələdə Paraşüt Açma', 'ru' => 'Парашютный Прыжок в Габала', 'en' => 'Gabala Paragliding'],
        ['az' => 'Göygöl Gölündə Kayak', 'ru' => 'Каякинг на Озере Гейгёль', 'en' => 'Goygol Lake Kayaking'],
        ['az' => 'Dağ Velosipedçiliyi Zaqatala', 'ru' => 'Горный Велоспорт Закаталы', 'en' => 'Mountain Biking Zagatala'],
        ['az' => 'At Minmə Turu Şəki', 'ru' => 'Верховая Езда Тур Шеки', 'en' => 'Horse Riding Tour Sheki'],
        ['az' => 'Cənub Yamaclarında Yelkənli Uçuş', 'ru' => 'Дельтапланеризм на Южных Склонах', 'en' => 'Hang Gliding Southern Slopes'],
        ['az' => 'Quba Dağlarında Piyada Gəzinti', 'ru' => 'Пеший Поход в Горах Кубы', 'en' => 'Quba Mountain Trekking'],
        ['az' => 'Bakı Körfəzində Tekne Turu', 'ru' => 'Тур на Яхте в Бухте Баку', 'en' => 'Baku Bay Boat Tour'],
        ['az' => 'Mingəçevir Kanalında Rafting', 'ru' => 'Рафтинг на Каналах Мингячевира', 'en' => 'Mingachevir Canal Rafting'],
        ['az' => 'Xınalıqda Trekking', 'ru' => 'Трекинг в Хыналыге', 'en' => 'Khinaliq Trekking'],
        ['az' => 'Lahıcda Misgərlik Dərsi', 'ru' => 'Урок Медного Ремесла в Лагиче', 'en' => 'Lahic Coppersmith Craft Class'],
        ['az' => 'Şirvan Düzündə Quş Müşahidəsi', 'ru' => 'Наблюдение за Птицами в Ширванской Степи', 'en' => 'Shirvan Steppe Bird Watching'],
        ['az' => 'Lənkəranda Çay Plantasiyası Ziyarəti', 'ru' => 'Посещение Чайной Плантации в Ленкорани', 'en' => 'Lankaran Tea Plantation Visit'],
        ['az' => 'Bakıda Xalça Toxuma Kursu', 'ru' => 'Курс Ткачества Ковров в Баку', 'en' => 'Baku Carpet Weaving Course'],
        ['az' => 'Naxçıvan Dağlarında Dırmaşmaq', 'ru' => 'Скалолазание в Горах Нахчывана', 'en' => 'Nakhchivan Mountain Climbing'],
        ['az' => 'Qəbələdə İp Parkuru', 'ru' => 'Верёвочный Парк в Габала', 'en' => 'Gabala Rope Course Adventure'],
        ['az' => 'Abşeronda ATV Safari', 'ru' => 'ATV Сафари в Абшероне', 'en' => 'Absheron ATV Safari'],
        ['az' => 'Şahdağda Çarpaz Ülkər Kanalı Sürüşmə', 'ru' => 'Зиплайн на Шахдаге', 'en' => 'Shahdag Zipline Adventure'],
        ['az' => 'Bakıda Dövlət Aqua Parkı', 'ru' => 'Государственный Аква Парк Баку', 'en' => 'Baku State Aqua Park'],
        ['az' => 'Gəncədə Golf', 'ru' => 'Гольф в Гяндже', 'en' => 'Golf in Ganja'],
        ['az' => 'Bakıda Bolinq', 'ru' => 'Боулинг в Баку', 'en' => 'Bowling in Baku'],
        ['az' => 'Qəbələdə Arx Atıcılığı', 'ru' => 'Стрельба из Лука в Габала', 'en' => 'Gabala Archery'],
        ['az' => 'Şirvan Balıq Ovlama Turu', 'ru' => 'Рыбалка Тур Ширван', 'en' => 'Shirvan Fishing Tour'],
        ['az' => 'Bakı Quasar Paintball', 'ru' => 'Пейнтбол Квасар Баку', 'en' => 'Baku Quasar Paintball'],
        ['az' => 'Şahdağ Qış Xizəkçiliyi', 'ru' => 'Зимний Лыжный Курорт Шахдаг', 'en' => 'Shahdag Winter Ski'],
        ['az' => 'Xəzər Dalğıclığı Turu', 'ru' => 'Дайвинг Тур Каспий', 'en' => 'Caspian Diving Tour'],
        ['az' => 'Bakı Mağara Kəşfiyyatı', 'ru' => 'Спелеология Баку', 'en' => 'Baku Cave Exploration'],
        ['az' => 'Quba Dağ Biatlonu', 'ru' => 'Горный Биатлон Куба', 'en' => 'Quba Mountain Biathlon'],
        ['az' => 'Lənkəran Mudbath Terapiyası', 'ru' => 'Грязевая Терапия Ленкорань', 'en' => 'Lankaran Mudbath Therapy'],
        ['az' => 'Şamaxıda Astrotur', 'ru' => 'Астро Тур в Шамахы', 'en' => 'Shamakhi Astro Tour'],
        ['az' => 'Qəbələ Zipline Safari', 'ru' => 'Зиплайн Сафари Габала', 'en' => 'Gabala Zipline Safari'],
        ['az' => 'Naxçıvan Duz Mağarası Sağlamlıq Turu', 'ru' => 'Соляная Пещера Оздоровительный Тур Нахчыван', 'en' => 'Nakhchivan Salt Cave Health Tour'],
        ['az' => 'Göygöl Dağ Velosipedçiliyi', 'ru' => 'Горный Велоспорт Гейгёль', 'en' => 'Goygol Mountain Cycling'],
        ['az' => 'Bakı Çimərliyi Voleybol Turniri', 'ru' => 'Турнир по Пляжному Волейболу Баку', 'en' => 'Baku Beach Volleyball Tournament'],
        ['az' => 'Bakı Keramika Kursu', 'ru' => 'Курс Керамики Баку', 'en' => 'Baku Ceramics Workshop'],
        ['az' => 'Ağstafa Ördək Ovu Turu', 'ru' => 'Охота на Утку Агстафа', 'en' => 'Agstafa Duck Hunting Tour'],
        ['az' => 'Mingəçevir Sualtı Fotoqrafiya', 'ru' => 'Подводная Фотография Мингячевир', 'en' => 'Mingachevir Underwater Photography'],
        ['az' => 'Bakı Əl Sənəti Atölyeyi', 'ru' => 'Мастерская Ручного Ремесла Баку', 'en' => 'Baku Handicraft Workshop'],
        ['az' => 'Şəki İpək Boyama Kursu', 'ru' => 'Курс Окраски Шёлка Шеки', 'en' => 'Sheki Silk Dyeing Course'],
        ['az' => 'Zaqatala Paramotor Uçuşu', 'ru' => 'Парамотор Полёт Закаталы', 'en' => 'Zagatala Paramotor Flight'],
        ['az' => 'Quba Ağ Sular Rafting', 'ru' => 'Рафтинг на Белой Воде Куба', 'en' => 'Quba White Water Rafting'],
        ['az' => 'Bakı Muay Thai Kursu', 'ru' => 'Курс Муай Тай Баку', 'en' => 'Baku Muay Thai Course'],
        ['az' => 'Gəncə Skydiving', 'ru' => 'Скайдайвинг Гянджа', 'en' => 'Ganja Skydiving'],
        ['az' => 'Xəzər Dənizçiliyi Lənkəran', 'ru' => 'Яхтинг Каспий Ленкорань', 'en' => 'Caspian Yachting Lankaran'],
        ['az' => 'Bakı Sualtı Hokey', 'ru' => 'Подводный Хоккей Баку', 'en' => 'Baku Underwater Hockey'],
        ['az' => 'Naxçıvan İsti Hava Balonçuluğu', 'ru' => 'Полёт на Воздушном Шаре Нахчыван', 'en' => 'Nakhchivan Hot Air Balloon'],
        ['az' => 'Qəbələ Dağ Süvari Turu', 'ru' => 'Конный Горный Тур Габала', 'en' => 'Gabala Mountain Horse Tour'],
        ['az' => 'Şirvan Camel Safari', 'ru' => 'Верблюжье Сафари Ширван', 'en' => 'Shirvan Camel Safari'],
    ];

    // -------------------------------------------------------------------------
    // Guide listing titles
    // -------------------------------------------------------------------------

    private array $guideTitles = [
        ['az' => 'Bakı Şəhər Gidimiz Elçin', 'ru' => 'Наш Городской Гид Баку Эльчин', 'en' => 'Baku City Guide Elchin'],
        ['az' => 'Qəbələ Dağ Gid Rauf', 'ru' => 'Горный Гид Габала Рауф', 'en' => 'Gabala Mountain Guide Rauf'],
        ['az' => 'Şəki Tarixi Gid Nigar', 'ru' => 'Исторический Гид Шеки Нигяр', 'en' => 'Sheki Historical Guide Nigar'],
        ['az' => 'Naxçıvan Arxeoloji Gid Anar', 'ru' => 'Археологический Гид Нахчыван Анар', 'en' => 'Nakhchivan Archaeological Guide Anar'],
        ['az' => 'Lənkəran Təbiət Gidi Zaur', 'ru' => 'Природный Гид Ленкорань Заур', 'en' => 'Lankaran Nature Guide Zaur'],
        ['az' => 'Gəncə Mədəniyyət Gidi Aysel', 'ru' => 'Культурный Гид Гянджа Айсел', 'en' => 'Ganja Culture Guide Aysel'],
        ['az' => 'Quba Macəra Gidi Tural', 'ru' => 'Гид Приключений Куба Турал', 'en' => 'Quba Adventure Guide Tural'],
        ['az' => 'Bakı Əcnəbi Dilləri Gidi', 'ru' => 'Многоязычный Гид Баку', 'en' => 'Baku Multilingual Guide'],
        ['az' => 'Şamaxı Şərab Gidi Fərid', 'ru' => 'Винный Гид Шамахы Фарид', 'en' => 'Shamakhi Wine Guide Farid'],
        ['az' => 'Abşeron Tarixi Gid Lalə', 'ru' => 'Исторический Гид Абшерон Лале', 'en' => 'Absheron Historical Guide Lale'],
        ['az' => 'Bakı Fotoqrafiya Gidi', 'ru' => 'Фотографический Гид Баку', 'en' => 'Baku Photography Guide'],
        ['az' => 'Qəbələ Ekzotik Quşlar Gidi', 'ru' => 'Гид по Экзотическим Птицам Габала', 'en' => 'Gabala Exotic Birds Guide'],
        ['az' => 'Naxçıvan İpək Yolu Gidi', 'ru' => 'Гид Шёлкового Пути Нахчыван', 'en' => 'Nakhchivan Silk Road Guide'],
        ['az' => 'Bakı Müasir Memarlıq Gidi', 'ru' => 'Гид Современной Архитектуры Баку', 'en' => 'Baku Modern Architecture Guide'],
        ['az' => 'Şəki İpək Tarixi Gidi', 'ru' => 'Исторический Гид Шёлка Шеки', 'en' => 'Sheki Silk History Guide'],
        ['az' => 'Zaqatala Ornitoloji Gid', 'ru' => 'Орнитологический Гид Закаталы', 'en' => 'Zagatala Ornithology Guide'],
        ['az' => 'Bakı Xalça İncəsənəti Gidi', 'ru' => 'Гид Коврового Искусства Баку', 'en' => 'Baku Carpet Art Guide'],
        ['az' => 'Göygöl Geoloji Gid', 'ru' => 'Геологический Гид Гейгёль', 'en' => 'Goygol Geology Guide'],
        ['az' => 'Gəncə Əlyazma Gidi', 'ru' => 'Гид Рукописей Гянджа', 'en' => 'Ganja Manuscripts Guide'],
        ['az' => 'Bakı Qastronomik Gid', 'ru' => 'Гастрономический Гид Баку', 'en' => 'Baku Gastronomic Guide'],
        ['az' => 'Lahıc Misgər Gidi', 'ru' => 'Гид Медника Лагич', 'en' => 'Lahic Coppersmith Guide'],
        ['az' => 'Bakı Bağlar Botanika Gidi', 'ru' => 'Ботанический Гид Садов Баку', 'en' => 'Baku Botanical Gardens Guide'],
        ['az' => 'Mingəçevir Su Müasirliyi Gidi', 'ru' => 'Гид Водного Современника Мингячевир', 'en' => 'Mingachevir Water Heritage Guide'],
        ['az' => 'Şirvan Milli Parkı Ekogidi', 'ru' => 'Экогид Национального Парка Ширван', 'en' => 'Shirvan National Park Eco-Guide'],
        ['az' => 'Bakı Caz Musiqi Turu Gidi', 'ru' => 'Гид Джазового Музыкального Тура Баку', 'en' => 'Baku Jazz Music Tour Guide'],
        ['az' => 'Quba Xalça Gidi', 'ru' => 'Гид Ковров Куба', 'en' => 'Quba Carpet Guide'],
        ['az' => 'Bakı Neft Tarixi Gidi', 'ru' => 'Гид Нефтяной Истории Баку', 'en' => 'Baku Oil History Guide'],
        ['az' => 'Şəki Bəy Evi Gidi', 'ru' => 'Гид Дома Беев Шеки', 'en' => 'Sheki Bey House Guide'],
        ['az' => 'Naxçıvan Qeydiyyatsız Gid', 'ru' => 'Незарегистрированный Гид Нахчыван', 'en' => 'Nakhchivan Off-Grid Guide'],
        ['az' => 'Bakı Çimərliyi Gidi', 'ru' => 'Гид Пляжа Баку', 'en' => 'Baku Beach Guide'],
        ['az' => 'Qəbələ Meşə Gidi', 'ru' => 'Лесной Гид Габала', 'en' => 'Gabala Forest Guide'],
        ['az' => 'Lənkəran Botanika Gidi', 'ru' => 'Ботанический Гид Ленкорань', 'en' => 'Lankaran Botanical Guide'],
        ['az' => 'Bakı Həssas Yerlər Gidi', 'ru' => 'Гид Секретных Мест Баку', 'en' => 'Baku Secret Spots Guide'],
        ['az' => 'Gəncə Sanat Atölyesi Gidi', 'ru' => 'Гид Художественной Мастерской Гянджа', 'en' => 'Ganja Art Workshop Guide'],
        ['az' => 'Şamaxı Astrofizik Gid', 'ru' => 'Астрофизический Гид Шамахы', 'en' => 'Shamakhi Astrophysics Guide'],
        ['az' => 'Bakı Qəhvə Gidi', 'ru' => 'Кофейный Гид Баку', 'en' => 'Baku Coffee Guide'],
        ['az' => 'Abşeron Neft Gidi', 'ru' => 'Нефтяной Гид Абшерон', 'en' => 'Absheron Oil Guide'],
        ['az' => 'Bakı Dil Müxtəlifliyi Gidi', 'ru' => 'Гид Языкового Разнообразия Баку', 'en' => 'Baku Language Diversity Guide'],
        ['az' => 'Göygöl Göl Sistemi Gidi', 'ru' => 'Гид Системы Озёр Гейгёль', 'en' => 'Goygol Lake System Guide'],
        ['az' => 'Xınalıq Qədim Kənd Gidi', 'ru' => 'Гид Древней Деревни Хыналыг', 'en' => 'Khinaliq Ancient Village Guide'],
        ['az' => 'Bakı Moda Gidi', 'ru' => 'Модный Гид Баку', 'en' => 'Baku Fashion Guide'],
        ['az' => 'Naxçıvan Astronautika Gidi', 'ru' => 'Астронавтический Гид Нахчыван', 'en' => 'Nakhchivan Stargazing Guide'],
        ['az' => 'Şəki İnci Xan Sarayı Gidi', 'ru' => 'Гид Дворца Шеки Хан', 'en' => 'Sheki Khan Palace Guide'],
        ['az' => 'Bakı Dəniz Qüvvələri Gidi', 'ru' => 'Гид Военно-Морских Сил Баку', 'en' => 'Baku Naval Heritage Guide'],
        ['az' => 'Quba Yerli Mətbəx Gidi', 'ru' => 'Гид Местной Кухни Куба', 'en' => 'Quba Local Cuisine Guide'],
        ['az' => 'Bakı Xarici Mətbəx Gidi', 'ru' => 'Гид Иностранной Кухни Баку', 'en' => 'Baku International Cuisine Guide'],
        ['az' => 'Lənkəran Çayçılıq Gidi', 'ru' => 'Гид Чаеводства Ленкорань', 'en' => 'Lankaran Tea Culture Guide'],
        ['az' => 'Qəbələ Astro Gid', 'ru' => 'Астро Гид Габала', 'en' => 'Gabala Astro Guide'],
        ['az' => 'Bakı Musiqi Gidi', 'ru' => 'Музыкальный Гид Баку', 'en' => 'Baku Music Heritage Guide'],
        ['az' => 'Şamaxı Təbiət Gidi', 'ru' => 'Природный Гид Шамахы', 'en' => 'Shamakhi Nature Guide'],
    ];

    // -------------------------------------------------------------------------
    // Restaurant listing titles
    // -------------------------------------------------------------------------

    private array $restaurantTitles = [
        ['az' => 'Firenze Restoranı Bakı', 'ru' => 'Ресторан Фиренце Баку', 'en' => 'Firenze Restaurant Baku'],
        ['az' => 'Muğam Klubu', 'ru' => 'Клуб Мугам', 'en' => 'Mugam Club'],
        ['az' => 'Neft Restoranı Bakı', 'ru' => 'Ресторан Нефть Баку', 'en' => 'Neft Restaurant Baku'],
        ['az' => 'Dolma Restoranı Bakı', 'ru' => 'Ресторан Долма Баку', 'en' => 'Dolma Restaurant Baku'],
        ['az' => 'Köhnə Şəhər Karvansara Restoranı', 'ru' => 'Ресторан Старый Город Каравансарай', 'en' => 'Old City Caravanserai Restaurant'],
        ['az' => 'Sehrli Təndir Restoranı', 'ru' => 'Ресторан Волшебный Тандыр', 'en' => 'Sehrli Tendir Restaurant'],
        ['az' => 'İpək Yolu Restoranı', 'ru' => 'Ресторан Шёлковый Путь', 'en' => 'Silk Road Restaurant'],
        ['az' => 'Çinar Restoranı Bakı', 'ru' => 'Ресторан Чинар Баку', 'en' => 'Chinar Restaurant Baku'],
        ['az' => 'Şərq Bazarı Restoranı', 'ru' => 'Ресторан Восточный Базар', 'en' => 'Eastern Bazaar Restaurant'],
        ['az' => 'Xan Sarayı Restoranı', 'ru' => 'Ресторан Дворец Хана', 'en' => 'Khan Palace Restaurant'],
        ['az' => 'Bakı Şəhər Çayxanası', 'ru' => 'Бакинская Городская Чайхана', 'en' => 'Baku City Teahouse'],
        ['az' => 'Nərgiz Qastronomik Restoranı', 'ru' => 'Гастрономический Ресторан Наргиз', 'en' => 'Nargiz Gastronomic Restaurant'],
        ['az' => 'Caspian Grilli Bakı', 'ru' => 'Каспийский Гриль Баку', 'en' => 'Caspian Grill Baku'],
        ['az' => 'Plov Evi Bakı', 'ru' => 'Дом Плова Баку', 'en' => 'Plov House Baku'],
        ['az' => 'Şəki Karvansara Restoranı', 'ru' => 'Ресторан Каравансарай Шеки', 'en' => 'Sheki Caravanserai Restaurant'],
        ['az' => 'Azərbaycan Mətbəxi Restoranı', 'ru' => 'Ресторан Азербайджанской Кухни', 'en' => 'Azerbaijani Cuisine Restaurant'],
        ['az' => 'Leyla Restoranı Bakı', 'ru' => 'Ресторан Лейла Баку', 'en' => 'Leyla Restaurant Baku'],
        ['az' => 'Pasta Lab Bakı', 'ru' => 'Паста Лаб Баку', 'en' => 'Pasta Lab Baku'],
        ['az' => 'Şirvan Restoranı', 'ru' => 'Ресторан Ширван', 'en' => 'Shirvan Restaurant'],
        ['az' => 'Bakı Steak House', 'ru' => 'Бакинский Стейк Хаус', 'en' => 'Baku Steak House'],
        ['az' => 'Yaşıl Bağ Restoranı', 'ru' => 'Ресторан Зелёный Сад', 'en' => 'Green Garden Restaurant'],
        ['az' => 'Gəncə Tarixi Restoranı', 'ru' => 'Исторический Ресторан Гянджа', 'en' => 'Ganja Historical Restaurant'],
        ['az' => 'Dəniz Sahili Restoranı Bakı', 'ru' => 'Приморский Ресторан Баку', 'en' => 'Seaside Restaurant Baku'],
        ['az' => 'Şuşa Milli Mətbəx Restoranı', 'ru' => 'Национальный Ресторан Шуша', 'en' => 'Shusha National Cuisine Restaurant'],
        ['az' => 'Mingəçevir Balıq Restoranı', 'ru' => 'Рыбный Ресторан Мингячевир', 'en' => 'Mingachevir Fish Restaurant'],
        ['az' => 'Lənkəran Subtropik Mətbəxi', 'ru' => 'Субтропическая Кухня Ленкорань', 'en' => 'Lankaran Subtropical Cuisine'],
        ['az' => 'Bakı Meze Barı', 'ru' => 'Бар Мезе Баку', 'en' => 'Baku Meze Bar'],
        ['az' => 'Qəbələ Dağ Restoranı', 'ru' => 'Горный Ресторан Габала', 'en' => 'Gabala Mountain Restaurant'],
        ['az' => 'Lahıc Mis Qab Restoranı', 'ru' => 'Ресторан Медной Посуды Лагич', 'en' => 'Lahic Copper Plate Restaurant'],
        ['az' => 'Kənd Süfrəsi Restoranı', 'ru' => 'Ресторан Деревенский Стол', 'en' => 'Village Table Restaurant'],
        ['az' => 'Xınalıq Autentik Mətbəx', 'ru' => 'Аутентичная Кухня Хыналыг', 'en' => 'Khinaliq Authentic Cuisine'],
        ['az' => 'İçərişəhər Ləzzət Restoranı', 'ru' => 'Ресторан Лаззат Ичери Шехер', 'en' => 'Icheri Sheher Lazzat Restaurant'],
        ['az' => 'Şamaxı Şərab Evi', 'ru' => 'Дом Вина Шамахы', 'en' => 'Shamakhi Wine House'],
        ['az' => 'Bakı Qutab Evi', 'ru' => 'Дом Кутаба Баку', 'en' => 'Baku Gutab House'],
        ['az' => 'Naxçıvan Dövshana Restoranı', 'ru' => 'Ресторан Довшана Нахчыван', 'en' => 'Nakhchivan Dovshana Restaurant'],
        ['az' => 'Balaca Bakı Restoranı', 'ru' => 'Маленький Бакинский Ресторан', 'en' => 'Little Baku Restaurant'],
        ['az' => 'Quba Pomidor Restoranı', 'ru' => 'Ресторан Томатов Куба', 'en' => 'Quba Tomato Restaurant'],
        ['az' => 'Göygöl Şəlalə Kafesi', 'ru' => 'Кафе Водопада Гейгёль', 'en' => 'Goygol Waterfall Cafe'],
        ['az' => 'Zaqatala Meşə Restoranı', 'ru' => 'Лесной Ресторан Закаталы', 'en' => 'Zagatala Forest Restaurant'],
        ['az' => 'Bakı Bulvar Kafesi', 'ru' => 'Бульварное Кафе Баку', 'en' => 'Baku Boulevard Cafe'],
        ['az' => 'Saray Restoranı Naxçıvan', 'ru' => 'Ресторан Сарай Нахчыван', 'en' => 'Saray Restaurant Nakhchivan'],
        ['az' => 'Nar Restoranı Bakı', 'ru' => 'Ресторан Нар Баку', 'en' => 'Nar Restaurant Baku'],
        ['az' => 'Bakı Modern Köfəlik', 'ru' => 'Современная Кофейня Баку', 'en' => 'Baku Modern Coffee House'],
        ['az' => 'Qafqaz Mətbəxi Restoranı', 'ru' => 'Ресторан Кавказской Кухни', 'en' => 'Caucasian Cuisine Restaurant'],
        ['az' => 'Dəniz Xörəkləri Restoranı Bakı', 'ru' => 'Ресторан Морепродуктов Баку', 'en' => 'Baku Seafood Restaurant'],
        ['az' => 'Mingəçevir Çayxanası', 'ru' => 'Чайхана Мингячевир', 'en' => 'Mingachevir Teahouse'],
        ['az' => 'Abşeron Balıqçı Restoranı', 'ru' => 'Рыбацкий Ресторан Абшерон', 'en' => 'Absheron Fisherman Restaurant'],
        ['az' => 'Bakı Çayxanası 1001', 'ru' => 'Бакинская Чайхана 1001', 'en' => 'Baku Teahouse 1001'],
        ['az' => 'Şəki Piti Evi', 'ru' => 'Дом Пити Шеки', 'en' => 'Sheki Piti House'],
        ['az' => 'Gəncə Sadə Mətbəxi', 'ru' => 'Простая Кухня Гянджа', 'en' => 'Ganja Simple Kitchen'],
    ];

    // -------------------------------------------------------------------------
    // YouTube video IDs per listing type (real Azerbaijan tourism videos)
    // -------------------------------------------------------------------------

    private array $youtubeIds = [
        'hotel'      => ['gvMnNVMuqKg', 'Yzh_gYsE_hg', 'k3_Mjzf38Dc', 'kJxSuvw5lF4', 'xvFZjo5PgG0'],
        'home'       => ['ScMzIvxBSi4', 'UUe-xoOTRoY', 'WpYeekQkAdc', 'fZHXKSxPOwQ', 'e-7v4qUWNjc'],
        'tour'       => ['2LoJuEaE6DQ', 'BrKfKe0vH6k', 'Fn2NQlMtFnU', 'R8Uh0S-7ajs', 'D4G-AMxFiag'],
        'activity'   => ['9DKXMG2oZhU', 'VOb04jPz_rI', '1G0M6nMvNaE', 'XbGs_qK2PQA', 'OXaYj-5JRDU'],
        'guide'      => ['QTg6M1AE5dc', 'J1BEzFJrPBQ', 'y0UdFcFHRpg', 'FdlFtX8bZPo', 'Dn3HHV9GQXY'],
        'restaurant' => ['Fhx5YT-58Cs', 'NsUJbgP5rZg', 'LkU4xDAGOSk', 'SbXLYRUraLs', '9W4LWiNHPww'],
    ];

    // -------------------------------------------------------------------------
    // Definition
    // -------------------------------------------------------------------------

    public function definition(): array
    {
        return [
            'ulid'          => (string) \Illuminate\Support\Str::ulid(),
            'user_id'       => \App\Models\User::factory(),
            'type'          => $this->faker->randomElement(ListingType::cases()),
            'status'        => $this->faker->randomElement([
                ListingStatus::Published,
                ListingStatus::Published,
                ListingStatus::Published,
                ListingStatus::Published,
                ListingStatus::PendingReview,
                ListingStatus::Draft,
            ]),
            'boost_weight'  => $this->faker->numberBetween(0, 10),
            'avg_rating'    => 0,
            'review_count'  => 0,
            'slug'          => null, // set by afterCreating via generateSlug()
            'featured_image' => 'https://picsum.photos/seed/' . $this->faker->unique()->numberBetween(1, 9999) . '/800/600',
            'is_verified'   => $this->faker->boolean(60),
            'contact_email' => $this->faker->safeEmail(),
            'contact_phone' => '+994' . $this->faker->numerify('##-###-##-##'),
            'website_url'   => $this->faker->optional(0.4)->url(),
        ];
    }

    // -------------------------------------------------------------------------
    // Type states
    // -------------------------------------------------------------------------

    public function hotel(): static
    {
        return $this->state(['type' => ListingType::Hotel])
            ->afterCreating(function (Listing $listing) {
                $this->createTranslations($listing, 'hotel');
                $this->createLocation($listing);
                $this->createMedia($listing, 'hotel');
                $this->createTags($listing, 'hotel');
                $this->createHotelDetail($listing);
                $this->generateSlug($listing);
            });
    }

    public function home(): static
    {
        return $this->state(['type' => ListingType::Home])
            ->afterCreating(function (Listing $listing) {
                $this->createTranslations($listing, 'home');
                $this->createLocation($listing);
                $this->createMedia($listing, 'home');
                $this->createTags($listing, 'home');
                $this->createHomeDetail($listing);
                $this->generateSlug($listing);
            });
    }

    public function tour(): static
    {
        return $this->state(['type' => ListingType::Tour])
            ->afterCreating(function (Listing $listing) {
                $this->createTranslations($listing, 'tour');
                $this->createLocation($listing);
                $this->createMedia($listing, 'tour');
                $this->createTags($listing, 'tour');
                $this->createTourDetail($listing);
                $this->generateSlug($listing);
            });
    }

    public function activity(): static
    {
        return $this->state(['type' => ListingType::Activity])
            ->afterCreating(function (Listing $listing) {
                $this->createTranslations($listing, 'activity');
                $this->createLocation($listing);
                $this->createMedia($listing, 'activity');
                $this->createTags($listing, 'activity');
                $this->createActivityDetail($listing);
                $this->generateSlug($listing);
            });
    }

    public function guide(): static
    {
        return $this->state(['type' => ListingType::Guide])
            ->afterCreating(function (Listing $listing) {
                $this->createTranslations($listing, 'guide');
                $this->createLocation($listing);
                $this->createMedia($listing, 'guide');
                $this->createTags($listing, 'guide');
                $this->createGuideDetail($listing);
                $this->generateSlug($listing);
            });
    }

    public function restaurant(): static
    {
        return $this->state(['type' => ListingType::Restaurant])
            ->afterCreating(function (Listing $listing) {
                $this->createTranslations($listing, 'restaurant');
                $this->createLocation($listing);
                $this->createMedia($listing, 'restaurant');
                $this->createTags($listing, 'restaurant');
                $this->createRestaurantDetail($listing);
                $this->generateSlug($listing);
            });
    }

    // -------------------------------------------------------------------------
    // Helper: translations
    // -------------------------------------------------------------------------

    private function createTranslations(Listing $listing, string $type): void
    {
        $titles = $this->{$type . 'Titles'};
        $titleSet = $this->faker->randomElement($titles);

        $descriptions = $this->descriptionsFor($type);

        foreach (['az', 'ru', 'en'] as $locale) {
            ListingTranslation::create([
                'listing_id'      => $listing->id,
                'locale'          => $locale,
                'title'           => $titleSet[$locale],
                'description'     => $descriptions[$locale],
                'address'         => $this->addressFor($locale),
                'seo_title'       => $titleSet[$locale] . ' | Tripaz',
                'seo_description' => Str::limit($descriptions[$locale], 155),
            ]);
        }
    }

    private function descriptionsFor(string $type): array
    {
        $descriptions = [
            'hotel' => [
                'az' => 'Bakının mərkəzində yerləşən bu lüks otel, Xəzər dənizinin möhtəşəm mənzərəsini təqdim edir. Dünya səviyyəli xidmət və müasir otaqlarla unutulmaz istirahət təcrübəsi yaşayacaqsınız. Mehmanxanamız hər bir qonağa ən yüksək rahatlıq standartlarını təqdim etməyə çalışır.',
                'ru' => 'Этот роскошный отель в центре Баку предлагает великолепный вид на Каспийское море. Первоклассный сервис и современные номера обеспечат незабываемый отдых. Наш отель стремится предоставить каждому гостю наивысшие стандарты комфорта.',
                'en' => 'Located in the heart of Baku, this luxury hotel offers magnificent views of the Caspian Sea. World-class service and modern rooms guarantee an unforgettable stay. Our hotel strives to provide every guest with the highest standards of comfort.',
            ],
            'home' => [
                'az' => 'Azərbaycanın gözəl mənzərəsinin ortasında yerləşən bu ev sizə əsl istirahəti vəd edir. Tam avadanlıqlı mətbəx, geniş oturma otağı və hər bir rahatlığı ilə bu ev eviniz kimi hiss etdirəcək. Şəhərin sərgi-səsindən uzaqda sakit bir yerdə yerləşir.',
                'ru' => 'Этот дом, расположенный посреди живописных пейзажей Азербайджана, обещает вам настоящий отдых. Полностью оборудованная кухня, просторная гостиная и все удобства сделают этот дом похожим на ваш собственный. Расположен в тихом месте вдали от городской суеты.',
                'en' => 'Nestled amid the beautiful landscapes of Azerbaijan, this home promises you true relaxation. A fully equipped kitchen, spacious living room, and every amenity will make this home feel like your own. Located in a quiet spot away from the city bustle.',
            ],
            'tour' => [
                'az' => 'Bu maraqlı tur sizi Azərbaycanın ən gözəl tarixi yerləri ilə tanış edəcək. Peşəkar bələdçimiz sizi hər bir məkanda gözləyən hekayələrlə tanış edəcək. Tur boyunca nəqliyyat, naharlıq və sigorta daxildir.',
                'ru' => 'Этот увлекательный тур познакомит вас с красивейшими историческими местами Азербайджана. Наш профессиональный гид расскажет вам истории, которые ждут вас в каждом месте. Транспорт, обед и страховка включены.',
                'en' => 'This fascinating tour will introduce you to the most beautiful historical sites in Azerbaijan. Our professional guide will share the stories waiting for you at every location. Transport, lunch, and insurance are all included.',
            ],
            'activity' => [
                'az' => 'Azərbaycanın möhtəşəm təbiətini aktiv şəkildə kəşf etməyin tam vaxtı gəldi. Bu fəaliyyət sizə güclü emosiyalar yaşatmaqla yanaşı, ölkənin gözəlliklərinə yaxından baxmağa imkan verir. Hər bir yaş qrupu üçün uyğundur.',
                'ru' => 'Пришло время активно исследовать великолепную природу Азербайджана. Эта деятельность позволит вам испытать сильные эмоции, а также вблизи увидеть красоты страны. Подходит для всех возрастных групп.',
                'en' => 'It is time to actively discover the magnificent nature of Azerbaijan. This activity allows you to experience strong emotions while getting up close to the country\'s beauty. Suitable for all age groups.',
            ],
            'guide' => [
                'az' => 'Azərbaycanı yaxşı tanıyan peşəkar bələdçimizlə birlikdə unudulmaz bir səfər yaşayın. Çoxdillilik bacarıqlarına malik olan bələdçimiz sizi ölkənin gizli xəzinələri ilə tanış edəcək. Xüsusi maraqlarınıza uyğun turlar da təşkil edilir.',
                'ru' => 'Совершите незабываемое путешествие вместе с нашим профессиональным гидом, хорошо знающим Азербайджан. Наш многоязычный гид познакомит вас с скрытыми сокровищами страны. Также организуются туры с учётом ваших особых интересов.',
                'en' => 'Experience an unforgettable journey with our professional guide who knows Azerbaijan well. Our multilingual guide will introduce you to the hidden treasures of the country. Tours tailored to your special interests are also arranged.',
            ],
            'restaurant' => [
                'az' => 'Azərbaycan mətbəxinin ən gözəl ləzzətlərini bu restoranda kəşf edin. Usta aşpazlarımız ən təzə yerli məhsullardan istifadə edərək müstəsna yeməklər hazırlayır. Geniş menyu seçimi və xüsusi atmosfer ilə hər bir ziyarət xatirəli olacaq.',
                'ru' => 'Откройте для себя лучшие вкусы азербайджанской кухни в этом ресторане. Наши шеф-повара готовят изысканные блюда, используя самые свежие местные продукты. Широкий выбор меню и особая атмосфера сделают каждый визит незабываемым.',
                'en' => 'Discover the finest flavors of Azerbaijani cuisine at this restaurant. Our master chefs prepare exceptional dishes using the freshest local ingredients. A wide menu selection and special atmosphere make every visit memorable.',
            ],
        ];

        return $descriptions[$type];
    }

    private function addressFor(string $locale): string
    {
        $city = $this->faker->randomElement([
            'az' => ['Bakı', 'Gəncə', 'Şəki', 'Quba', 'Qəbələ', 'Lənkəran', 'Şamaxı', 'Naxçıvan'],
            'ru' => ['Баку', 'Гянджа', 'Шеки', 'Куба', 'Габала', 'Ленкорань', 'Шамахы', 'Нахчыван'],
            'en' => ['Baku', 'Ganja', 'Sheki', 'Quba', 'Gabala', 'Lankaran', 'Shamakhi', 'Nakhchivan'],
        ][$locale]);

        $streetNum = $this->faker->numberBetween(1, 200);

        return match ($locale) {
            'az' => "{$city} şəhəri, {$streetNum} nömrəli küçə",
            'ru' => "г. {$city}, ул. №{$streetNum}",
            default => "{$streetNum} Main Street, {$city}",
        };
    }

    // -------------------------------------------------------------------------
    // Helper: location
    // -------------------------------------------------------------------------

    private function createLocation(Listing $listing): void
    {
        $city = $this->faker->randomElement(array_values($this->cityCoords));

        // small random offset so listings aren't all on exact same point
        $lat = $city['lat'] + $this->faker->randomFloat(4, -0.05, 0.05);
        $lng = $city['lng'] + $this->faker->randomFloat(4, -0.05, 0.05);

        $region = Region::whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.az'))) LIKE ?", ['%' . mb_strtolower(mb_substr($city['region'], 0, 3)) . '%'])->first();

        ListingLocation::create([
            'listing_id'  => $listing->id,
            'region_id'   => $region?->id,
            'latitude'    => round($lat, 8),
            'longitude'   => round($lng, 8),
            'postal_code' => (string) $this->faker->numerify('AZ####'),
            'place_id'    => 'ChIJ' . Str::random(20),
        ]);
    }

    // -------------------------------------------------------------------------
    // Helper: media
    // -------------------------------------------------------------------------

    private function createMedia(Listing $listing, string $type): void
    {
        $count = $this->faker->numberBetween(3, 6);
        $youtubeId = $this->faker->randomElement($this->youtubeIds[$type]);

        for ($i = 0; $i < $count; $i++) {
            $seed = $this->faker->numberBetween(100, 9999);
            $isFirst = ($i === 0);

            ListingMedia::create([
                'listing_id'         => $listing->id,
                'path'               => "listings/{$listing->id}/image-{$i}.jpg",
                'disk'               => 'public',
                'mime_type'          => 'image/jpeg',
                'size'               => $this->faker->numberBetween(200000, 2000000),
                'collection'         => 'gallery',
                'sort_order'         => $i,
                'conversions'        => [
                    'thumb'  => "listings/{$listing->id}/thumb-{$i}.jpg",
                    'medium' => "listings/{$listing->id}/medium-{$i}.jpg",
                ],
                'custom_properties'  => $isFirst
                    ? [
                        'url'         => "https://picsum.photos/seed/{$seed}/800/600",
                        'youtube_url' => "https://www.youtube.com/watch?v={$youtubeId}",
                        'alt'         => $type . ' image ' . $i,
                    ]
                    : [
                        'url' => "https://picsum.photos/seed/{$seed}/800/600",
                        'alt' => $type . ' image ' . $i,
                    ],
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Helper: tags
    // -------------------------------------------------------------------------

    private function createTags(Listing $listing, string $type): void
    {
        $tagSets = [
            'hotel'      => ['az' => ['lüks', 'otel', 'istirahət', 'xidmət', 'rahatlıq', 'ulduzlu'], 'ru' => ['люкс', 'отель', 'отдых', 'сервис', 'комфорт', 'звёздный'], 'en' => ['luxury', 'hotel', 'rest', 'service', 'comfort', 'rated']],
            'home'       => ['az' => ['ev', 'mənzil', 'istirahət', 'geniş', 'aile', 'rahat'], 'ru' => ['дом', 'квартира', 'отдых', 'просторный', 'семья', 'уютный'], 'en' => ['home', 'apartment', 'relaxation', 'spacious', 'family', 'cozy']],
            'tour'       => ['az' => ['tur', 'tarix', 'mədəniyyət', 'kəşf', 'bələdçi', 'səyahət'], 'ru' => ['тур', 'история', 'культура', 'исследование', 'гид', 'путешествие'], 'en' => ['tour', 'history', 'culture', 'discovery', 'guide', 'travel']],
            'activity'   => ['az' => ['fəaliyyət', 'macəra', 'idman', 'əyləncə', 'heyəcan', 'aktiv'], 'ru' => ['активность', 'приключение', 'спорт', 'развлечение', 'азарт', 'активный'], 'en' => ['activity', 'adventure', 'sport', 'fun', 'excitement', 'active']],
            'guide'      => ['az' => ['bələdçi', 'peşəkar', 'çoxdilli', 'yerli', 'bilik', 'təcrübə'], 'ru' => ['гид', 'профессионал', 'многоязычный', 'местный', 'знание', 'опыт'], 'en' => ['guide', 'professional', 'multilingual', 'local', 'knowledge', 'experience']],
            'restaurant' => ['az' => ['restoran', 'milli', 'ləzzət', 'təzə', 'mətbəx', 'halal'], 'ru' => ['ресторан', 'национальный', 'вкус', 'свежий', 'кухня', 'халяль'], 'en' => ['restaurant', 'national', 'taste', 'fresh', 'cuisine', 'halal']],
        ];

        $tags = $tagSets[$type];

        foreach (['az', 'ru', 'en'] as $locale) {
            $selectedTags = $this->faker->randomElements($tags[$locale], $this->faker->numberBetween(3, 5));
            foreach ($selectedTags as $tag) {
                ListingTag::create([
                    'listing_id' => $listing->id,
                    'tag'        => $tag,
                    'locale'     => $locale,
                ]);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Helper: slug generation
    // -------------------------------------------------------------------------

    private function generateSlug(Listing $listing): void
    {
        $listing->refresh();
        $listing->generateSlug();
        $listing->saveQuietly();
    }

    // -------------------------------------------------------------------------
    // Detail creators
    // -------------------------------------------------------------------------

    private function createHotelDetail(Listing $listing): void
    {
        HotelDetail::create([
            'listing_id'     => $listing->id,
            'stars'          => $this->faker->numberBetween(2, 5),
            'total_rooms'    => $this->faker->numberBetween(20, 500),
            'check_in_time'  => $this->faker->randomElement(['12:00', '13:00', '14:00', '15:00']),
            'check_out_time' => $this->faker->randomElement(['10:00', '11:00', '12:00']),
            'policies'       => [
                'cancellation' => 'Free cancellation up to 24 hours before check-in',
                'pets'         => $this->faker->boolean(30) ? 'Pets allowed' : 'No pets',
                'smoking'      => 'Non-smoking property',
            ],
        ]);
    }

    private function createHomeDetail(Listing $listing): void
    {
        HomeDetail::create([
            'listing_id'    => $listing->id,
            'property_type' => $this->faker->randomElement(PropertyType::cases()),
            'bedrooms'      => $this->faker->numberBetween(1, 5),
            'bathrooms'     => $this->faker->numberBetween(1, 3),
            'max_guests'    => $this->faker->numberBetween(2, 10),
            'total_area'    => $this->faker->randomFloat(2, 40, 350),
            'floor'         => $this->faker->numberBetween(0, 20),
            'house_rules'   => [
                'check_in'     => '14:00',
                'check_out'    => '12:00',
                'no_parties'   => true,
                'no_smoking'   => true,
                'price_per_night' => $this->faker->numberBetween(30, 300),
            ],
        ]);
    }

    private function createTourDetail(Listing $listing): void
    {
        TourDetail::create([
            'listing_id'       => $listing->id,
            'duration_hours'   => $this->faker->randomElement([2, 3, 4, 6, 8, 12, 24, 48]),
            'max_participants' => $this->faker->numberBetween(5, 30),
            'meeting_point'    => $this->faker->randomElement([
                'Fountain Square, Baku',
                'Heydar Aliyev Center, Baku',
                'Old City Gates, Icheri Sheher',
                'Sheki Khan Palace entrance',
                'Gobustan Museum parking',
                'Quba city center',
            ]),
            'includes'         => ['Transport', 'Professional guide', 'Entrance fees', 'Lunch'],
            'excludes'         => ['Personal expenses', 'Tips', 'Travel insurance'],
            'itinerary'        => [
                ['time' => '09:00', 'activity' => 'Meeting point and briefing'],
                ['time' => '09:30', 'activity' => 'Depart to first location'],
                ['time' => '12:00', 'activity' => 'Lunch break'],
                ['time' => '14:00', 'activity' => 'Continue to next site'],
                ['time' => '17:00', 'activity' => 'Return and farewell'],
            ],
        ]);

        // Store price as a tag
        ListingTag::create([
            'listing_id' => $listing->id,
            'tag'        => 'price_per_person:' . $this->faker->numberBetween(20, 150),
            'locale'     => 'en',
        ]);
    }

    private function createActivityDetail(Listing $listing): void
    {
        ActivityDetail::create([
            'listing_id'         => $listing->id,
            'duration_minutes'   => $this->faker->randomElement([60, 90, 120, 180, 240, 360]),
            'max_participants'   => $this->faker->numberBetween(5, 25),
        ]);

        ListingTag::create([
            'listing_id' => $listing->id,
            'tag'        => 'price_per_person:' . $this->faker->numberBetween(15, 120),
            'locale'     => 'en',
        ]);
    }

    private function createGuideDetail(Listing $listing): void
    {
        GuideDetail::create([
            'listing_id'       => $listing->id,
            'languages'        => $this->faker->randomElements(['az', 'ru', 'en', 'tr', 'de', 'fr', 'ar'], $this->faker->numberBetween(2, 4)),
            'experience_years' => $this->faker->numberBetween(1, 20),
            'certifications'   => ['Licensed Tour Guide - Ministry of Culture Azerbaijan', 'First Aid Certified'],
            'bio_extra'        => 'Experienced local guide with deep knowledge of Azerbaijani culture, history and nature. Available for private and group tours throughout Azerbaijan.',
        ]);

        ListingTag::create([
            'listing_id' => $listing->id,
            'tag'        => 'daily_rate:' . $this->faker->numberBetween(80, 300),
            'locale'     => 'en',
        ]);
    }

    private function createRestaurantDetail(Listing $listing): void
    {
        RestaurantDetail::create([
            'listing_id'       => $listing->id,
            'cuisine_types'    => $this->faker->randomElements(['azerbaijani', 'international', 'caucasian', 'turkish', 'mediterranean', 'russian', 'seafood', 'vegetarian'], $this->faker->numberBetween(1, 3)),
            'price_range'      => $this->faker->randomElement(PriceRange::cases()),
            'has_outdoor'      => $this->faker->boolean(60),
            'has_delivery'     => $this->faker->boolean(50),
            'has_takeaway'     => $this->faker->boolean(40),
            'opening_hours'    => [
                'monday'    => ['open' => '10:00', 'close' => '23:00'],
                'tuesday'   => ['open' => '10:00', 'close' => '23:00'],
                'wednesday' => ['open' => '10:00', 'close' => '23:00'],
                'thursday'  => ['open' => '10:00', 'close' => '23:00'],
                'friday'    => ['open' => '10:00', 'close' => '00:00'],
                'saturday'  => ['open' => '10:00', 'close' => '00:00'],
                'sunday'    => ['open' => '11:00', 'close' => '22:00'],
            ],
            'menu_url'         => $this->faker->optional(0.3)->url(),
        ]);

        ListingTag::create([
            'listing_id' => $listing->id,
            'tag'        => 'avg_check_per_person:' . $this->faker->numberBetween(10, 80),
            'locale'     => 'en',
        ]);
    }
}
