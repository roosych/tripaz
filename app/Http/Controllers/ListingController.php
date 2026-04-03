<?php

namespace App\Http\Controllers;

use App\Actions\CreateListingAction;
use App\DTOs\ActivityDetailData;
use App\DTOs\CreateListingData;
use App\DTOs\GuideDetailData;
use App\DTOs\HomeDetailData;
use App\DTOs\HotelDetailData;
use App\DTOs\LocationData;
use App\DTOs\RestaurantDetailData;
use App\DTOs\TourDetailData;
use App\DTOs\TranslationData;
use App\Enums\ListingType;
use App\Filters\ListingFilter;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    // -------------------------------------------------------------------------
    // Public catalog
    // -------------------------------------------------------------------------

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

        // Region filter
        if ($regionId = $request->integer('region')) {
            $filter->filterByRegion($regionId);
        }

        // Category filter — single (legacy) or multi-select
        if ($categoryIds = array_filter(array_map('intval', (array) $request->input('categories', [])))) {
            $filter->filterByCategories($categoryIds);
        } elseif ($categoryId = $request->integer('category')) {
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
            if ($cuisine = $request->input('cuisine')) {
                $filter->filterByCuisine($cuisine);
            }
            if ($priceRange = $request->input('price_range')) {
                $filter->filterByRestaurantPriceRange($priceRange);
            }
            if ($request->boolean('delivery')) {
                $filter->filterByDelivery(true);
            }
        }

        // Sorting
        $sort = $request->input('sort', 'boost');
        match ($sort) {
            'rating' => $filter->sortByRating(),
            'newest' => $filter->sortByNewest(),
            default  => $filter->sortByBoost(),
        };

        $listings = $filter->getQuery()->paginate(20)->withQueryString();

        // Sidebar data
        $regions = Region::whereNull('parent_id')
            ->with('children')
            ->orderBy('name->az')
            ->get();

        $amenities = collect();
        if ($type) {
            $amenities = Amenity::forType($type)
                ->orderBy('group')
                ->orderBy('name->az')
                ->get()
                ->groupBy('group');
        }

        $categories = Category::when($type, fn($q) => $q->forType($type))
            ->roots()
            ->ordered()
            ->get();

        return view('listings.index', compact(
            'listings',
            'regions',
            'amenities',
            'categories',
            'type',
            'locale',
        ));
    }

    public function show(string $slug)
    {
        $listing = Listing::with([
            'translations',
            'location.region',
            'media',
            'amenities',
            'categories',
        ])->where('slug', $slug)->published()->firstOrFail();

        $locale = app()->getLocale();

        // Group amenities by group
        $amenitiesByGroup = $listing->amenities->groupBy('group');

        // Separate image media and find YouTube URL
        $imageMedia = $listing->media->filter(fn($m) => str_starts_with($m->mime_type ?? '', 'image/'));
        $youtubeMedia = $listing->media->firstWhere('mime_type', 'video/youtube');
        $youtubeUrl = $youtubeMedia?->custom_properties['youtube_url'] ?? null;

        return view('listings.show', compact(
            'listing',
            'locale',
            'amenitiesByGroup',
            'imageMedia',
            'youtubeUrl',
        ));
    }

    // -------------------------------------------------------------------------
    // Owner / Admin forms
    // -------------------------------------------------------------------------

    public function create()
    {
        $this->authorize('create', Listing::class);

        $regions    = Region::whereNull('parent_id')->with('children')->orderBy('name->az')->get();
        $categories = Category::roots()->ordered()->get();
        $amenities  = Amenity::orderBy('group')->orderBy('name->az')->get()->groupBy('group');

        return view('listings.create', compact('regions', 'categories', 'amenities'));
    }

    public function store(Request $request, CreateListingAction $action)
    {
        $this->authorize('create', Listing::class);

        $type = ListingType::from($request->input('type'));

        $validated = $request->validate($this->validationRules($type));

        $translations = [];
        foreach (['az', 'ru', 'en'] as $locale) {
            $t = $validated["translations"][$locale] ?? [];
            if (!empty($t['title'])) {
                $translations[] = TranslationData::fromArray(array_merge($t, ['locale' => $locale]));
            }
        }

        $location = LocationData::fromArray($validated['location'] ?? []);
        $detail   = $this->buildDetailData($type, $validated['detail'] ?? []);

        $data = new CreateListingData(
            userId:       Auth::id(),
            type:         $type,
            translations: $translations,
            location:     $location,
            detail:       $detail,
            contactEmail: $validated['contact_email'] ?? null,
            contactPhone: $validated['contact_phone'] ?? null,
            websiteUrl:   $validated['website_url'] ?? null,
            categoryIds:  (array) ($validated['categories'] ?? []),
            amenityIds:   (array) ($validated['amenities'] ?? []),
        );

        $listing = $action->execute($data);

        return redirect()->route('listings.show', $listing->slug)
            ->with('success', 'Listing created successfully.');
    }

    public function edit(string $slug)
    {
        $listing = Listing::with(['translations', 'location.region', 'media', 'amenities', 'categories', 'detail'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('update', $listing);

        $regions    = Region::whereNull('parent_id')->with('children')->orderBy('name->az')->get();
        $categories = Category::roots()->ordered()->get();
        $amenities  = Amenity::orderBy('group')->orderBy('name->az')->get()->groupBy('group');

        return view('listings.edit', compact('listing', 'regions', 'categories', 'amenities'));
    }

    public function update(Request $request, string $slug)
    {
        $listing = Listing::where('slug', $slug)->firstOrFail();

        $this->authorize('update', $listing);

        $type = $listing->type;

        $validated = $request->validate($this->validationRules($type));

        // Update core listing fields
        $listing->update([
            'contact_email' => $validated['contact_email'] ?? $listing->contact_email,
            'contact_phone' => $validated['contact_phone'] ?? $listing->contact_phone,
            'website_url'   => $validated['website_url'] ?? $listing->website_url,
        ]);

        // Update translations
        foreach (['az', 'ru', 'en'] as $locale) {
            $t = $validated['translations'][$locale] ?? [];
            if (!empty($t['title'])) {
                $listing->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title'           => $t['title'],
                        'description'     => $t['description'] ?? '',
                        'address'         => $t['address'] ?? null,
                        'seo_title'       => $t['seo_title'] ?? null,
                        'seo_description' => $t['seo_description'] ?? null,
                    ]
                );
            }
        }

        // Update location
        if (!empty($validated['location'])) {
            $listing->location()->updateOrCreate(
                ['listing_id' => $listing->id],
                $validated['location']
            );
        }

        // Update detail
        if (!empty($validated['detail'])) {
            $detailData = $this->buildDetailData($type, $validated['detail']);
            $listing->detail()->updateOrCreate(
                ['listing_id' => $listing->id],
                $detailData->toArray()
            );
        }

        // Sync categories and amenities
        if (isset($validated['categories'])) {
            $listing->categories()->sync($validated['categories']);
        }
        if (isset($validated['amenities'])) {
            $listing->amenities()->sync($validated['amenities']);
        }

        // Regenerate slug if az title changed
        $listing->generateSlug();
        $listing->save();

        return redirect()->route('listings.show', $listing->slug)
            ->with('success', 'Listing updated successfully.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function validationRules(ListingType $type): array
    {
        $base = [
            'type'                      => 'required|string',
            'contact_email'             => 'nullable|email|max:255',
            'contact_phone'             => 'nullable|string|max:30',
            'website_url'               => 'nullable|url|max:255',
            'translations.az.title'     => 'required|string|max:255',
            'translations.az.description' => 'required|string',
            'translations.az.address'   => 'nullable|string|max:255',
            'translations.az.seo_title' => 'nullable|string|max:255',
            'translations.az.seo_description' => 'nullable|string|max:500',
            'translations.ru.title'     => 'nullable|string|max:255',
            'translations.ru.description' => 'nullable|string',
            'translations.ru.address'   => 'nullable|string|max:255',
            'translations.en.title'     => 'nullable|string|max:255',
            'translations.en.description' => 'nullable|string',
            'translations.en.address'   => 'nullable|string|max:255',
            'location.region_id'        => 'nullable|integer|exists:regions,id',
            'location.latitude'         => 'nullable|numeric|between:-90,90',
            'location.longitude'        => 'nullable|numeric|between:-180,180',
            'location.postal_code'      => 'nullable|string|max:20',
            'categories'                => 'nullable|array',
            'categories.*'              => 'integer|exists:categories,id',
            'amenities'                 => 'nullable|array',
            'amenities.*'               => 'integer|exists:amenities,id',
        ];

        $typeRules = match ($type) {
            ListingType::Hotel => [
                'detail.stars'          => 'nullable|integer|between:1,5',
                'detail.total_rooms'    => 'nullable|integer|min:1',
                'detail.check_in_time'  => 'nullable|string|max:10',
                'detail.check_out_time' => 'nullable|string|max:10',
            ],
            ListingType::Home => [
                'detail.property_type' => 'required|string',
                'detail.bedrooms'      => 'required|integer|min:1',
                'detail.bathrooms'     => 'required|integer|min:1',
                'detail.max_guests'    => 'required|integer|min:1',
                'detail.total_area'    => 'nullable|numeric',
                'detail.floor'         => 'nullable|integer',
            ],
            ListingType::Tour => [
                'detail.duration_hours'   => 'required|numeric|min:0.5',
                'detail.max_participants' => 'nullable|integer|min:1',
                'detail.meeting_point'    => 'nullable|string|max:255',
            ],
            ListingType::Activity => [
                'detail.duration_minutes'   => 'required|integer|min:15',
                'detail.activity_type'      => 'nullable|string|max:100',
                'detail.max_participants'   => 'nullable|integer|min:1',
            ],
            ListingType::Guide => [
                'detail.languages'        => 'required|array|min:1',
                'detail.experience_years' => 'nullable|integer|min:0',
            ],
            ListingType::Restaurant => [
                'detail.cuisine_types'    => 'nullable|array',
                'detail.price_range'      => 'required|string',
                'detail.has_outdoor'  => 'boolean',
                'detail.has_delivery'     => 'boolean',
                'detail.has_takeaway'     => 'boolean',
                'detail.menu_url'         => 'nullable|url|max:255',
            ],
        };

        return array_merge($base, $typeRules);
    }

    private function buildDetailData(
        ListingType $type,
        array $data,
    ): HotelDetailData|HomeDetailData|TourDetailData|ActivityDetailData|GuideDetailData|RestaurantDetailData {
        return match ($type) {
            ListingType::Hotel      => HotelDetailData::fromArray($data),
            ListingType::Home       => HomeDetailData::fromArray($data),
            ListingType::Tour       => TourDetailData::fromArray($data),
            ListingType::Activity   => ActivityDetailData::fromArray($data),
            ListingType::Guide      => GuideDetailData::fromArray($data),
            ListingType::Restaurant => RestaurantDetailData::fromArray($data),
        };
    }
}
