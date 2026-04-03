<?php

namespace App\Filters;

use App\Enums\ListingType;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Stateless filter class for the Listing query builder.
 *
 * Usage:
 *   $filter = new ListingFilter(Listing::query());
 *   $filter
 *       ->filterByType('hotel')
 *       ->filterByRegion(5)
 *       ->filterByStars(4);
 *
 *   $results = $filter->getQuery()->paginate(20);
 */
class ListingFilter
{
    private Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function getQuery(): Builder
    {
        return $this->query;
    }

    // -------------------------------------------------------------------------
    // Generic filters
    // -------------------------------------------------------------------------

    public function filterByType(string|ListingType $type): static
    {
        $value = $type instanceof ListingType ? $type->value : $type;

        $this->query->where('listings.type', $value);

        return $this;
    }

    public function filterByRegion(int $regionId): static
    {
        $this->query->whereHas('location', function (Builder $q) use ($regionId) {
            $q->where('region_id', $regionId);
        });

        return $this;
    }

    public function filterByCategory(int $categoryId): static
    {
        $this->query->whereHas('categories', function (Builder $q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });

        return $this;
    }

    /**
     * @param  array<int>  $categoryIds
     */
    public function filterByCategories(array $categoryIds): static
    {
        $this->query->whereHas('categories', function (Builder $q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        });

        return $this;
    }

    /**
     * @param  array<int>  $amenityIds
     */
    public function filterByAmenities(array $amenityIds): static
    {
        foreach ($amenityIds as $amenityId) {
            $this->query->whereHas('amenities', function (Builder $q) use ($amenityId) {
                $q->where('amenities.id', $amenityId);
            });
        }

        return $this;
    }

    /**
     * Filter by a numeric price range — relevant for tour/activity listing
     * detail tables that have a base_price column (future module).
     * Delegates to type-specific price where available.
     */
    public function filterByPriceRange(float $min, float $max): static
    {
        // Price is managed by the Pricing module (future scope).
        // Placeholder join when a `listing_prices` table is available:
        // $this->query->whereHas('price', fn($q) => $q->whereBetween('amount', [$min, $max]));

        return $this;
    }

    // -------------------------------------------------------------------------
    // Hotel-specific filters
    // -------------------------------------------------------------------------

    public function filterByStars(int $stars): static
    {
        $this->query->whereHas('hotelDetail', fn(Builder $q) => $q->where('stars', $stars));

        return $this;
    }

    public function filterByMinStars(int $minStars): static
    {
        $this->query->whereHas('hotelDetail', fn(Builder $q) => $q->where('stars', '>=', $minStars));

        return $this;
    }

    // -------------------------------------------------------------------------
    // Home-specific filters
    // -------------------------------------------------------------------------

    public function filterByMinBedrooms(int $min): static
    {
        $this->query->whereHas('homeDetail', fn(Builder $q) => $q->where('bedrooms', '>=', $min));

        return $this;
    }

    public function filterByMinMaxGuests(int $min): static
    {
        $this->query->whereHas('homeDetail', fn(Builder $q) => $q->where('max_guests', '>=', $min));

        return $this;
    }

    public function filterByPropertyType(string|PropertyType $type): static
    {
        $value = $type instanceof PropertyType ? $type->value : $type;

        $this->query->whereHas('homeDetail', fn(Builder $q) => $q->where('property_type', $value));

        return $this;
    }

    // -------------------------------------------------------------------------
    // Tour-specific filters
    // -------------------------------------------------------------------------

    public function filterByMaxDurationHours(float $maxHours): static
    {
        $this->query->whereHas('tourDetail', fn(Builder $q) => $q->where('duration_hours', '<=', $maxHours));

        return $this;
    }

    // -------------------------------------------------------------------------
    // Activity-specific filters
    // -------------------------------------------------------------------------

    public function filterByMaxDurationMinutes(int $maxMinutes): static
    {
        $this->query->whereHas('activityDetail', fn(Builder $q) => $q->where('duration_minutes', '<=', $maxMinutes));

        return $this;
    }

    // -------------------------------------------------------------------------
    // Restaurant-specific filters
    // -------------------------------------------------------------------------

    /**
     * Filter restaurants by cuisine (JSON array contains).
     */
    public function filterByCuisine(string $cuisine): static
    {
        $this->query->whereHas('restaurantDetail', fn(Builder $q) => $q->whereJsonContains('cuisine_types', $cuisine));

        return $this;
    }

    public function filterByRestaurantPriceRange(string $priceRange): static
    {
        $this->query->whereHas('restaurantDetail', fn(Builder $q) => $q->where('price_range', $priceRange));

        return $this;
    }

    public function filterByDelivery(bool $hasDelivery = true): static
    {
        $this->query->whereHas('restaurantDetail', fn(Builder $q) => $q->where('has_delivery', $hasDelivery));

        return $this;
    }

    // -------------------------------------------------------------------------
    // Sorting helpers
    // -------------------------------------------------------------------------

    public function sortByRating(string $direction = 'desc'): static
    {
        $this->query->orderBy('avg_rating', $direction);

        return $this;
    }

    public function sortByBoost(): static
    {
        $this->query->orderByDesc('boost_weight')->orderByDesc('avg_rating');

        return $this;
    }

    public function sortByNewest(): static
    {
        $this->query->latest();

        return $this;
    }

    /**
     * Priority groups with randomisation within each group.
     *
     * Group 0 — is_featured + is_verified  (top)
     * Group 1 — is_featured only
     * Group 2 — is_verified only
     * Group 3 — all others
     *
     * Within each group rows are shuffled randomly on every page request.
     * RAND() without a seed is intentional: the catalogue is meant to feel
     * fresh on each visit, and pagination is short enough (20 rows) that
     * cross-page consistency is not required.
     */
    public function sortByPriority(): static
    {
        $rand = DB::connection()->getDriverName() === 'sqlite' ? 'RANDOM()' : 'RAND()';

        $this->query
            ->orderByRaw('listings.boost_weight DESC')
            ->orderByRaw($rand);

        return $this;
    }
}
