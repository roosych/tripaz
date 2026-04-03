<?php

namespace App\Models;

use App\Enums\Locale;
use App\Enums\ListingPaymentStatus;
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\ListingTypeConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Listing extends Model
{
    use HasFactory;
    use HasSlug;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'ulid',
        'user_id',
        'type',
        'status',
        'boost_weight',
        'avg_rating',
        'review_count',
        'slug',
        'featured_image',
        'is_verified',
        'is_featured',
        'booking_enabled',
        'payment_required',
        'contact_email',
        'contact_phone',
        'website_url',
        'social_links',
    ];

    protected function casts(): array
    {
        return [
            'type'            => ListingType::class,
            'status'          => ListingStatus::class,
            'boost_weight'    => 'integer',
            'avg_rating'      => 'decimal:2',
            'review_count'    => 'integer',
            'is_verified'      => 'boolean',
            'is_featured'      => 'boolean',
            'booking_enabled'  => 'boolean',
            'payment_required' => 'boolean',
            'social_links'     => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Boot — auto-assign ULID on creation
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function (Listing $listing): void {
            if (empty($listing->ulid)) {
                $listing->ulid = (string) Str::ulid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Spatie Sluggable — slug from az translation title
    // -------------------------------------------------------------------------

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function (Listing $listing): string {
                $azTitle = $listing->translations()
                    ->where('locale', Locale::Az->value)
                    ->value('title');

                return $azTitle ?? $listing->ulid ?? (string) Str::ulid();
            })
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(255);
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ListingTranslation::class);
    }

    public function location(): HasOne
    {
        return $this->hasOne(ListingLocation::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ListingMedia::class)->orderBy('sort_order');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'listing_categories');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'listing_amenities');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ListingTag::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')
                    ->withPivot(['created_at']);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ListingPayment::class);
    }

    public function latestPendingPayment(): HasOne
    {
        return $this->hasOne(ListingPayment::class)
            ->where('status', ListingPaymentStatus::Pending->value)
            ->latestOfMany();
    }

    // -------------------------------------------------------------------------
    // CTI detail resolver
    // -------------------------------------------------------------------------

    public function detail(): HasOne
    {
        // NOTE: do NOT use with(['detail']) for eager loading — calling this method
        // on a prototype (fresh, unhydrated) model yields type=null and the match
        // will fail. Always access $listing->detail via lazy loading on a hydrated model.
        return match($this->type) {
            ListingType::Hotel      => $this->hasOne(HotelDetail::class, 'listing_id'),
            ListingType::Home       => $this->hasOne(HomeDetail::class, 'listing_id'),
            ListingType::Tour       => $this->hasOne(TourDetail::class, 'listing_id'),
            ListingType::Activity   => $this->hasOne(ActivityDetail::class, 'listing_id'),
            ListingType::Guide      => $this->hasOne(GuideDetail::class, 'listing_id'),
            ListingType::Restaurant => $this->hasOne(RestaurantDetail::class, 'listing_id'),
            default                 => $this->hasOne(HotelDetail::class, 'listing_id'),
        };
    }

    // -------------------------------------------------------------------------
    // Named detail relationships (used by ListingFilter — avoids prototype issue)
    // -------------------------------------------------------------------------

    public function hotelDetail(): HasOne
    {
        return $this->hasOne(HotelDetail::class, 'listing_id');
    }

    public function homeDetail(): HasOne
    {
        return $this->hasOne(HomeDetail::class, 'listing_id');
    }

    public function tourDetail(): HasOne
    {
        return $this->hasOne(TourDetail::class, 'listing_id');
    }

    public function activityDetail(): HasOne
    {
        return $this->hasOne(ActivityDetail::class, 'listing_id');
    }

    public function guideDetail(): HasOne
    {
        return $this->hasOne(GuideDetail::class, 'listing_id');
    }

    public function restaurantDetail(): HasOne
    {
        return $this->hasOne(RestaurantDetail::class, 'listing_id');
    }

    // -------------------------------------------------------------------------
    // Booking availability
    // -------------------------------------------------------------------------

    /**
     * Resolves whether booking is currently enabled for this listing.
     *
     * Priority:
     *   1. If the listing has an explicit per-listing override (`booking_enabled` is not null),
     *      that value wins.
     *   2. Otherwise, fall back to the global type-level config in `listing_type_configs`.
     */
    public function isBookingEnabled(): bool
    {
        if ($this->booking_enabled !== null) {
            return (bool) $this->booking_enabled;
        }

        return ListingTypeConfig::forType($this->type)->booking_enabled;
    }

    // -------------------------------------------------------------------------
    // Translation helper with fallback chain
    // -------------------------------------------------------------------------

    /**
     * Returns the best-available ListingTranslation for the given locale.
     * Fallback chain: requested → az → en → any.
     */
    public function translation(string|Locale|null $locale = null): ?ListingTranslation
    {
        $localeEnum = match(true) {
            $locale instanceof Locale => $locale,
            is_string($locale)        => Locale::tryFrom($locale) ?? Locale::Az,
            default                   => Locale::Az,
        };

        $loaded = $this->translations->keyBy(fn(ListingTranslation $t) => $t->locale->value);

        // Try requested locale first
        if ($loaded->has($localeEnum->value)) {
            return $loaded->get($localeEnum->value);
        }

        // Walk fallback chain
        foreach ($localeEnum->fallbackChain() as $fallback) {
            if ($loaded->has($fallback->value)) {
                return $loaded->get($fallback->value);
            }
        }

        // Final fallback: anything available
        return $loaded->first();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ListingStatus::Published->value);
    }

    public function scopeByType(Builder $query, string|ListingType $type): Builder
    {
        $value = $type instanceof ListingType ? $type->value : $type;

        return $query->where('type', $value);
    }

    public function scopeByLocale(Builder $query, string|Locale $locale): Builder
    {
        $value = $locale instanceof Locale ? $locale->value : $locale;

        return $query->whereHas('translations', function (Builder $q) use ($value) {
            $q->where('locale', $value);
        });
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    // -------------------------------------------------------------------------
    // Laravel Scout — Meilisearch integration
    // -------------------------------------------------------------------------

    /**
     * Only published listings are indexed.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === ListingStatus::Published;
    }

    /**
     * Build the searchable array with all locale translations included.
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['translations', 'location.region']);

        $translationData = [];
        foreach ($this->translations as $translation) {
            $locale = $translation->locale->value;
            $translationData["title_{$locale}"]       = $translation->title;
            $translationData["description_{$locale}"]  = $translation->description;
        }

        return array_merge([
            'id'           => $this->id,
            'ulid'         => $this->ulid,
            'type'         => $this->type->value,
            'status'       => $this->status->value,
            'avg_rating'   => $this->avg_rating,
            'review_count' => $this->review_count,
            'boost_weight' => $this->boost_weight,
            'is_verified'  => $this->is_verified,
            'is_featured'  => $this->is_featured,
            'region_id'    => $this->location?->region_id,
            'region_name'  => $this->location?->region?->name_az,
            'slug'         => $this->slug,
            'created_at'   => $this->created_at?->toIso8601String(),
        ], $translationData);
    }
}
