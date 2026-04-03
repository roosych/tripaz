<?php

namespace Database\Seeders;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    private array $positiveComments = [
        'az' => [
            'Mükəmməl xidmət, hər şey gözləntilərimdən yüksək idi. Tövsiyə edirəm!',
            'Çox gözəl yer idi, mütləq yenidən gələcəyəm. Heyətin mehribanlığı insanı heyran edir.',
            'Azərbaycana ilk səfərimdə bu yerə gəldim. Heç bir şeyi pisliyə salmaq mümkün deyil.',
            'Əla keyfiyyət, əla qiymət. Dostlarıma mütləq məsləhət görəcəyəm.',
            'Tarixi mühit, professional münasibət. Beş ulduz layiqdir!',
            'Hər şey mükəmməl idi. İdeal istirahət yeri.',
            'Xidmət və keyfiyyət baxımından ən yaxşı yer. Çox razı qaldım.',
            'Bu yerə gəlmək qərarı çox düzgün idi. Çox gözəl xatirələrim var.',
        ],
        'ru' => [
            'Отличный сервис, всё превзошло мои ожидания. Настоятельно рекомендую!',
            'Замечательное место, обязательно вернусь. Персонал очень доброжелательный.',
            'Это был мой первый визит в Азербайджан, и это место стало незабываемым.',
            'Отличное соотношение цены и качества. Обязательно посоветую друзьям.',
            'Историческая атмосфера, профессиональное обслуживание. Заслуживает пяти звёзд!',
            'Всё было великолепно. Идеальное место для отдыха.',
            'Лучшее место по уровню сервиса и качества. Очень доволен.',
            'Решение посетить это место было верным. Остались прекрасные воспоминания.',
        ],
        'en' => [
            'Excellent service, everything exceeded my expectations. Highly recommended!',
            'Wonderful place, I will definitely come back. The staff is incredibly friendly.',
            'This was my first visit to Azerbaijan, and this place made it unforgettable.',
            'Great value for money. I will absolutely recommend this to my friends.',
            'Historic atmosphere, professional service. Deserves five stars!',
            'Everything was perfect. Ideal place for relaxation.',
            'Best place in terms of service and quality. Very satisfied.',
            'Deciding to visit this place was the right call. Wonderful memories.',
        ],
    ];

    private array $neutralComments = [
        'az' => [
            'Ümumiyyətlə yaxşı idi, amma bəzi kiçik çatışmazlıqlar var idi.',
            'Gözləntilərimə uyğun gəldi. Normal səviyyəli xidmət.',
            'Xoş yer, amma qiymət bir qədər yüksək idi.',
            'Orta səviyyəli xidmət. Yaxşılaşdırmaq mümkündür.',
        ],
        'ru' => [
            'В целом хорошо, но были некоторые мелкие недостатки.',
            'Соответствовало моим ожиданиям. Обычный уровень обслуживания.',
            'Приятное место, но цена немного завышена.',
            'Средний уровень обслуживания. Есть куда совершенствоваться.',
        ],
        'en' => [
            'Generally good, but there were some minor issues.',
            'Met my expectations. Average level of service.',
            'Nice place, but the price was a bit high.',
            'Average service level. There is room for improvement.',
        ],
    ];

    private array $negativeComments = [
        'az' => [
            'Keyfiyyət gözləntilərimə cavab vermədi. Yaxşılaşdırılmalıdır.',
            'Xidmət çox yavaş idi. Pul dəyər etmədi.',
            'Çatışmazlıqlar çox idi. Tövsiyə etmirəm.',
        ],
        'ru' => [
            'Качество не соответствовало моим ожиданиям. Необходимы улучшения.',
            'Обслуживание было очень медленным. Не стоило своих денег.',
            'Слишком много недостатков. Не рекомендую.',
        ],
        'en' => [
            'Quality did not meet my expectations. Improvements are needed.',
            'Service was very slow. It was not worth the money.',
            'Too many shortcomings. I do not recommend.',
        ],
    ];

    public function run(): void
    {
        // Only regular users leave reviews
        $regularUsers = User::role('user')->get();

        if ($regularUsers->isEmpty()) {
            $this->command->error('No regular users found. Run UserSeeder first.');
            return;
        }

        // Only published listings get reviews
        $publishedListings = Listing::where('status', ListingStatus::Published)->get();

        $this->command->info("Creating reviews for {$publishedListings->count()} published listings…");

        foreach ($publishedListings as $listing) {
            $reviewCount = rand(5, 15);
            $reviewers   = $regularUsers->random(min($reviewCount, $regularUsers->count()));

            foreach ($reviewers as $user) {
                $rating = $this->weightedRating();
                $locale = fake()->randomElement(['az', 'ru', 'en']);

                $comment = match (true) {
                    $rating >= 4  => fake()->randomElement($this->positiveComments[$locale]),
                    $rating === 3 => fake()->randomElement($this->neutralComments[$locale]),
                    default       => fake()->randomElement($this->negativeComments[$locale]),
                };

                Review::create([
                    'listing_id'  => $listing->id,
                    'user_id'     => $user->id,
                    'rating'      => $rating,
                    'comment'     => fake()->boolean(85) ? $comment : null,
                    'is_approved' => fake()->boolean(92),
                ]);
            }
        }

        // Recalculate avg_rating and review_count on every listing that got reviews
        $this->command->info('Recalculating listing ratings…');

        Listing::where('status', ListingStatus::Published)->each(function (Listing $listing): void {
            $approved = Review::where('listing_id', $listing->id)
                ->where('is_approved', true)
                ->get();

            $count  = $approved->count();
            $avgRaw = $count > 0 ? $approved->avg('rating') : 0;

            $listing->update([
                'avg_rating'   => round($avgRaw, 2),
                'review_count' => $count,
            ]);
        });

        $this->command->info('Reviews and ratings seeded successfully.');
    }

    /**
     * Weighted random rating — skewed positive (realistic marketplace distribution).
     *
     *   5 stars — 40%
     *   4 stars — 30%
     *   3 stars — 15%
     *   2 stars — 10%
     *   1 star  —  5%
     */
    private function weightedRating(): int
    {
        $rand = rand(1, 100);

        return match (true) {
            $rand <= 40 => 5,
            $rand <= 70 => 4,
            $rand <= 85 => 3,
            $rand <= 95 => 2,
            default     => 1,
        };
    }
}
