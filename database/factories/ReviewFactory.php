<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    // Realistic review comments per locale and rating tier
    private array $positiveComments = [
        'az' => [
            'Mükəmməl xidmət, hər şey gözləntilərimdən yüksək idi. Tövsiyə edirəm!',
            'Çox gözəl yer idi, mütləq yenidən gələcəyəm. Heyətin mehribanlığı insanı heyran edir.',
            'Azərbaycana ilk səfərimdə bu yerə gəldim. Heç bir şeyi pisliyə salmaq mümkün deyil.',
            'Əla keyfiyyət, əla qiymət. Dostlarıma mütləq məsləhət görəcəyəm.',
            'Tarixi mühit, professional münasibət. Beş ulduz layiqdir!',
        ],
        'ru' => [
            'Отличный сервис, всё превзошло мои ожидания. Настоятельно рекомендую!',
            'Замечательное место, обязательно вернусь. Персонал очень доброжелательный.',
            'Это был мой первый визит в Азербайджан, и это место стало незабываемым.',
            'Отличное соотношение цены и качества. Обязательно посоветую друзьям.',
            'Историческая атмосфера, профессиональное обслуживание. Заслуживает пяти звёзд!',
        ],
        'en' => [
            'Excellent service, everything exceeded my expectations. Highly recommended!',
            'Wonderful place, I will definitely come back. The staff is incredibly friendly.',
            'This was my first visit to Azerbaijan, and this place made it unforgettable.',
            'Great value for money. I will absolutely recommend this to my friends.',
            'Historic atmosphere, professional service. Deserves five stars!',
        ],
    ];

    private array $neutralComments = [
        'az' => [
            'Ümumiyyətlə yaxşı idi, amma bəzi kiçik çatışmazlıqlar var idi. Bir daha gəlmərəm əmin deyiləm.',
            'Gözləntilərimə uyğun gəldi. Normal səviyyəli xidmət.',
            'Xoş yer, amma qiymət bir qədər yüksək idi.',
        ],
        'ru' => [
            'В целом хорошо, но были некоторые мелкие недостатки. Не уверен, вернусь ли снова.',
            'Соответствовало моим ожиданиям. Обычный уровень обслуживания.',
            'Приятное место, но цена немного завышена.',
        ],
        'en' => [
            'Generally good, but there were some minor issues. Not sure I would come back.',
            'Met my expectations. Average level of service.',
            'Nice place, but the price was a bit high.',
        ],
    ];

    private array $negativeComments = [
        'az' => [
            'Keyfiyyət gözləntilərimə cavab vermədi. Yaxşılaşdırılmalıdır.',
            'Xidmət çox yavaş idi. Pul dəyər etmədi.',
        ],
        'ru' => [
            'Качество не соответствовало моим ожиданиям. Необходимы улучшения.',
            'Обслуживание было очень медленным. Не стоило своих денег.',
        ],
        'en' => [
            'Quality did not meet my expectations. Improvements are needed.',
            'Service was very slow. It was not worth the money.',
        ],
    ];

    public function definition(): array
    {
        $rating  = $this->faker->numberBetween(1, 5);
        $locale  = $this->faker->randomElement(['az', 'ru', 'en']);

        $comment = match (true) {
            $rating >= 4  => $this->faker->randomElement($this->positiveComments[$locale]),
            $rating === 3 => $this->faker->randomElement($this->neutralComments[$locale]),
            default       => $this->faker->randomElement($this->negativeComments[$locale]),
        };

        return [
            'listing_id'  => Listing::factory(),
            'user_id'     => User::factory(),
            'rating'      => $rating,
            'comment'     => $this->faker->boolean(85) ? $comment : null,
            'is_approved' => $this->faker->boolean(90),
        ];
    }

    public function positive(): static
    {
        return $this->state(fn () => [
            'rating'      => $this->faker->numberBetween(4, 5),
            'is_approved' => true,
        ]);
    }

    public function negative(): static
    {
        return $this->state(fn () => [
            'rating'      => $this->faker->numberBetween(1, 2),
            'is_approved' => true,
        ]);
    }

    public function approved(): static
    {
        return $this->state(['is_approved' => true]);
    }

    public function pending(): static
    {
        return $this->state(['is_approved' => false]);
    }
}
