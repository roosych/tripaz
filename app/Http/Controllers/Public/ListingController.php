<?php

namespace App\Http\Controllers\Public;

use App\Enums\ListingType;
use App\Filters\ListingFilter;
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Region;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();

        $query = Listing::query()
            ->published()
            ->with([
                'translations',
                'location.region',
                'media',
                'amenities',
            ]);

        $filter = new ListingFilter($query);

        // Type filter
        $type = $request->input('type');
        if ($type && in_array($type, array_column(ListingType::cases(), 'value'), true)) {
            $filter->filterByType($type);
        }

        // Region filter — accepts both numeric ID and slug
        if ($regionParam = $request->input('region')) {
            if (is_numeric($regionParam)) {
                $filter->filterByRegion((int) $regionParam);
            } else {
                $region = \App\Models\Region::where('slug', $regionParam)->first();
                if ($region) {
                    $filter->filterByRegion($region->id);
                }
            }
        }

        // Category filter
        if ($categoryId = $request->integer('category')) {
            $filter->filterByCategory($categoryId);
        }

        // Amenities filter
        if ($amenityIds = $request->input('amenities', [])) {
            $filter->filterByAmenities(array_map('intval', (array) $amenityIds));
        }

        // Hotel-specific
        if ($type === 'hotel') {
            if ($stars = $request->input('stars')) {
                $filter->filterByMinStars((int) $stars);
            }
        }

        // Home-specific
        if ($type === 'home') {
            if ($bedrooms = $request->integer('bedrooms')) {
                $filter->filterByMinBedrooms($bedrooms);
            }
            if ($guests = $request->integer('guests')) {
                $filter->filterByMinMaxGuests($guests);
            }
            if ($propertyType = $request->input('property_type')) {
                $filter->filterByPropertyType($propertyType);
            }
        }

        // Tour-specific
        if ($type === 'tour') {
            if ($maxHours = $request->input('max_hours')) {
                $filter->filterByMaxDurationHours((float) $maxHours);
            }
        }

        // Activity-specific
        if ($type === 'activity') {
            if ($maxMinutes = $request->input('max_minutes')) {
                $filter->filterByMaxDurationMinutes((int) $maxMinutes);
            }
        }

        // Restaurant-specific
        if ($type === 'restaurant') {
            $cuisines = array_filter((array) $request->input('cuisine', []));
            if (!empty($cuisines)) {
                $filter->filterByCuisine($cuisines[0]); // pass first selected for now
            }
            if ($priceRange = $request->input('price_range')) {
                $filter->filterByRestaurantPriceRange($priceRange);
            }
            if ($request->boolean('delivery')) {
                $filter->filterByDelivery(true);
            }
        }

        // Sorting
        // Default is 'priority': featured+verified first, then featured, then
        // verified, then the rest — random within each group on every request.
        $sort = $request->input('sort', 'priority');
        match ($sort) {
            'rating'  => $filter->sortByRating(),
            'newest'  => $filter->sortByNewest(),
            'boost'   => $filter->sortByBoost(),
            default   => $filter->sortByPriority(),
        };

        $query = $filter->getQuery();

        if (auth()->check()) {
            $query->withExists([
                'favorites as is_favorited' => fn ($q) => $q->where('user_id', auth()->id()),
            ]);
        }

        $listings = $query->paginate(20)->withQueryString();

        // Sidebar data
        $regions = Region::whereNull('parent_id')
            ->with('children')
            ->orderBy('name->az')
            ->get();

        $amenities = collect();
        if ($type) {
            $amenities = Amenity::forType($type)
                ->with('amenityGroup')
                ->leftJoin('amenity_groups', 'amenities.amenity_group_id', '=', 'amenity_groups.id')
                ->orderByRaw('COALESCE(amenity_groups.sort_order, 9999)')
                ->orderBy('amenities.sort_order')
                ->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(amenities.name, '$.az'))")
                ->select('amenities.*')
                ->get()
                ->groupBy('group');
        }

        $categories = Category::when($type, fn($q) => $q->forType($type))
            ->roots()
            ->ordered()
            ->get();

        // Min/max values for home sliders — derived from published home listings
        $homeQuery = fn() => \App\Models\HomeDetail::whereHas(
            'listing', fn($q) => $q->published()->where('type', 'home')
        );

        $minBedrooms = $homeQuery()->min('bedrooms') ?? 1;
        $maxBedrooms = $homeQuery()->max('bedrooms') ?? 10;
        $minGuests   = $homeQuery()->min('max_guests') ?? 1;
        $maxGuests   = $homeQuery()->max('max_guests') ?? 20;

        return view('listings.index', compact(
            'listings',
            'regions',
            'amenities',
            'categories',
            'type',
            'locale',
            'minBedrooms',
            'maxBedrooms',
            'minGuests',
            'maxGuests',
        ));
    }

    public function show(string $slug)
    {
        $listing = Listing::with([
            'translations',
            'location.region',
            'media',
            'amenities.amenityGroup',
            'categories',
            'reviews' => fn ($q) => $q->where('is_approved', true)->select('listing_id', 'rating'),
        ])->where('slug', $slug)->published()->firstOrFail();

        $locale = app()->getLocale();

        // Sort amenities by group sort_order then by amenity sort_order, then group by group name
        $amenitiesByGroup = $listing->amenities
            ->sortBy(fn ($a) => sprintf('%05d_%05d',
                $a->amenityGroup?->sort_order ?? 9999,
                $a->sort_order
            ))
            ->groupBy('group');

        // Separate image media and find YouTube URL
        $imageMedia = $listing->media->filter(fn($m) => str_starts_with($m->mime_type ?? '', 'image/'));
        $youtubeMedia = $listing->media->firstWhere('mime_type', 'video/youtube');
        $youtubeUrl = $youtubeMedia?->custom_properties['youtube_url'] ?? null;

        $bookingEnabled = $listing->isBookingEnabled();

        $similarListings = Listing::query()
            ->published()
            ->with(['translations', 'location.region', 'media', 'amenities'])
            ->whereHas('location', fn ($q) => $q->where('region_id', $listing->location->region_id))
            ->where('id', '!=', $listing->id)
            ->orderByRaw('listings.boost_weight DESC')
            ->orderByRaw('RAND()')
            ->limit(8)
            ->get();

        return view('listings.show', compact(
            'listing',
            'locale',
            'amenitiesByGroup',
            'imageMedia',
            'youtubeUrl',
            'bookingEnabled',
            'similarListings',
        ));
    }
}
