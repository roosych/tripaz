<?php

namespace App\Http\Controllers\Admin;

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
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Listing;
use App\Models\LookupOption;
use App\Models\Region;
use App\Models\User;
use App\Services\ModerationService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    public function __construct(private readonly ModerationService $moderation) {}

    /**
     * List all listings with their current status for admin review.
     */
    public function index(Request $request)
    {
        $currentStatus = $request->input('status', '');
        $q             = $request->input('q', '');
        $typeFilter    = $request->input('type', '');
        $categoryId    = $request->input('category_id', '');

        $query = Listing::query()
            ->with(['translations', 'location.region', 'user'])
            ->latest();

        // Status filter (driven by the tab bar)
        if ($currentStatus && ListingStatus::tryFrom($currentStatus)) {
            $query->where('status', $currentStatus);
        }

        // Full-text search: match by ID or by title in any locale
        if ($q !== '') {
            $query->where(function ($q2) use ($q) {
                // Numeric input — also try exact ID match
                if (is_numeric($q)) {
                    $q2->where('listings.id', (int) $q);
                }
                // Title search across all translation rows
                $q2->orWhereHas('translations', function ($t) use ($q) {
                    $t->where('title', 'like', "%{$q}%");
                });
                // Also match slug
                $q2->orWhere('slug', 'like', "%{$q}%");
            });
        }

        // Type filter
        if ($typeFilter && ListingType::tryFrom($typeFilter)) {
            $query->where('type', $typeFilter);
        }

        // Category filter (pivot table)
        if ($categoryId && is_numeric($categoryId)) {
            $query->whereHas('categories', function ($c) use ($categoryId) {
                $c->where('categories.id', (int) $categoryId);
            });
        }

        $listings = $query->paginate(30)->withQueryString();

        // Count pending for the tab badge
        $pendingListingsCount = Listing::where('status', ListingStatus::PendingReview->value)->count();

        // Data for filter dropdowns
        $listingTypes    = ListingType::cases();
        $listingStatuses = ListingStatus::cases();
        $categories      = Category::orderBy('name->az')->get(['id', 'name']);

        return view('admin.listings.index', compact(
            'listings',
            'currentStatus',
            'pendingListingsCount',
            'listingTypes',
            'listingStatuses',
            'categories',
            'q',
            'typeFilter',
            'categoryId',
        ));
    }

    /**
     * Show the form for creating a new listing on behalf of a host user.
     */
    public function create()
    {
        $regions    = Region::whereNotNull('parent_id')->orderBy('name->az')->get();
        $categories = Category::roots()->ordered()->get();
        $amenities  = Amenity::with('amenityGroup')->orderByRaw("COALESCE(amenity_group_id, 999999)")->orderBy('name->az')->get()->groupBy('group');
        $hosts      = User::role('host')->orderBy('name')->get(['id', 'name', 'email']);
        $mediaItems = collect();

        return view('admin.listings.create', compact('regions', 'categories', 'amenities', 'hosts', 'mediaItems'));
    }

    /**
     * Store a new listing created by admin, assigned to the given host user.
     */
    public function store(Request $request, CreateListingAction $action)
    {
        $type = ListingType::from($request->input('type'));

        $validated = $request->validate(array_merge(
            $this->validationRules($type),
            ['user_id' => 'nullable|integer|exists:users,id'],
        ));

        $translations = [];
        foreach (['az', 'ru', 'en'] as $locale) {
            $t = $validated['translations'][$locale] ?? [];
            if (!empty($t['title'])) {
                $translations[] = TranslationData::fromArray(array_merge($t, ['locale' => $locale]));
            }
        }

        $location = LocationData::fromArray($validated['location'] ?? []);
        $detail   = $this->buildDetailData($type, $validated['detail'] ?? []);

        $data = new CreateListingData(
            userId:       $validated['user_id'] ? (int) $validated['user_id'] : null,
            type:         $type,
            translations: $translations,
            location:     $location,
            detail:       $detail,
            contactEmail: $validated['contact_email'] ?? null,
            contactPhone: $validated['contact_phone'] ?? null,
            websiteUrl:   $validated['website_url'] ?? null,
            socialLinks:  $validated['social_links'] ?? null,
            categoryIds:  (array) ($validated['categories'] ?? []),
            amenityIds:   (array) ($validated['amenities'] ?? []),
        );

        $listing = $action->execute($data);

        return redirect()->route('admin.listings.index')
            ->with('success', "Listing [{$listing->slug}] created successfully.");
    }

    /**
     * Show the edit form for an existing listing (admin can reassign owner).
     */
    public function edit(Listing $listing)
    {
        $listing->load(['translations', 'location.region', 'amenities', 'categories', 'media']);

        $regions    = Region::whereNotNull('parent_id')->orderBy('name->az')->get();
        $categories = Category::roots()->ordered()->get();
        $amenities  = Amenity::with('amenityGroup')->orderByRaw("COALESCE(amenity_group_id, 999999)")->orderBy('name->az')->get()->groupBy('group');
        $hosts      = User::role('host')->orderBy('name')->get(['id', 'name', 'email']);

        // Load the type-specific detail record and expose it as a flat array for JS.
        // detail() uses lazy loading and requires a hydrated model, so we call it here
        // (not in the view) to avoid the prototype-type=null issue documented in Listing::detail().
        $detailRecord = $listing->detail()->first();
        $detailValues = $detailRecord ? $detailRecord->toArray() : [];

        $bookingEnabled = $listing->isBookingEnabled();
        $mediaItems     = $listing->media;

        return view('admin.listings.edit', compact('listing', 'regions', 'categories', 'amenities', 'hosts', 'detailValues', 'bookingEnabled', 'mediaItems'));
    }

    /**
     * Update a listing, including optional owner reassignment.
     */
    public function update(Request $request, Listing $listing)
    {
        $type = $listing->type;

        $validated = $request->validate(array_merge(
            $this->validationRules($type),
            ['user_id' => 'nullable|integer|exists:users,id'],
        ));

        $listing->update([
            'user_id'       => $validated['user_id'] ? (int) $validated['user_id'] : null,
            'contact_email' => $validated['contact_email'] ?? $listing->contact_email,
            'contact_phone' => $validated['contact_phone'] ?? $listing->contact_phone,
            'website_url'   => $validated['website_url'] ?? $listing->website_url,
            'social_links'  => $validated['social_links'] ?? $listing->social_links,
        ]);

        foreach (['az', 'ru', 'en'] as $locale) {
            $t = $validated['translations'][$locale] ?? [];
            if (!empty($t['title'])) {
                $listing->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title'       => $t['title'],
                        'description' => $t['description'] ?? '',
                        'address'     => $t['address'] ?? null,
                    ]
                );
            }
        }

        if (!empty($validated['location'])) {
            $listing->location()->updateOrCreate(
                ['listing_id' => $listing->id],
                $validated['location']
            );
        }

        if (!empty($validated['detail'])) {
            $detailData = $this->buildDetailData($type, $validated['detail']);
            $listing->detail()->updateOrCreate(
                ['listing_id' => $listing->id],
                $detailData->toArray()
            );
        }

        // Always sync so that deselecting all checkboxes correctly detaches everything.
        $listing->categories()->sync($validated['categories'] ?? []);
        $listing->amenities()->sync($validated['amenities'] ?? []);

        $listing->generateSlug();
        $listing->save();

        return redirect()->route('admin.listings.index')
            ->with('success', "Listing [{$listing->slug}] updated successfully.");
    }

    /**
     * Approve a listing that is pending review, publishing it.
     */
    public function approve(Listing $listing)
    {
        $this->authorize('moderate', $listing);

        try {
            $this->moderation->approve($listing, Auth::user());
        } catch (DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', "Listing [{$listing->slug}] has been approved and published.");
    }

    /**
     * Reject a listing that is pending review.
     */
    public function reject(Request $request, Listing $listing)
    {
        $this->authorize('moderate', $listing);

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->moderation->reject($listing, Auth::user(), $validated['reason']);
        } catch (DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', "Listing [{$listing->slug}] has been rejected.");
    }

    /**
     * Suspend a published listing.
     */
    public function suspend(Request $request, Listing $listing)
    {
        $this->authorize('moderate', $listing);

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->moderation->suspend($listing, Auth::user(), $validated['reason']);
        } catch (DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', "Listing [{$listing->slug}] has been suspended.");
    }

    /**
     * Reinstate a suspended listing back to published status.
     */
    public function reinstate(Listing $listing)
    {
        $this->authorize('moderate', $listing);

        try {
            $this->moderation->reinstate($listing, Auth::user());
        } catch (DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', "Listing [{$listing->slug}] has been reinstated and is now published.");
    }

    // -------------------------------------------------------------------------
    // AJAX
    // -------------------------------------------------------------------------

    /**
     * Return filtered categories, amenities, and type-specific field schema for a listing type.
     * Called by the create/edit form via AJAX when the user selects a listing type.
     *
     * GET /admin/api/listing-type-data?type={hotel|home|tour|activity|guide|restaurant}
     */
    public function listingTypeData(Request $request)
    {
        $typeString = $request->input('type', '');
        $type       = ListingType::tryFrom($typeString);

        if (! $type) {
            return response()->json(['error' => 'Invalid listing type.'], 422);
        }

        // Filtered categories: null listing_type (universal) OR matching the selected type
        $categories = Category::forType($type)
            ->roots()
            ->ordered()
            ->get(['id', 'name', 'icon'])
            ->map(fn (Category $c) => [
                'id'      => $c->id,
                'name_az' => $c->getTranslation('name', 'az'),
                'name_ru' => $c->getTranslation('name', 'ru'),
                'name_en' => $c->getTranslation('name', 'en'),
                'icon'    => $c->icon,
            ]);

        // Filtered amenities: null listing_type (universal) OR matching the selected type
        $amenities = Amenity::forType($type)
            ->with('amenityGroup')
            ->orderByRaw("COALESCE(amenity_group_id, 999999)")
            ->orderBy('name->az')
            ->get(['id', 'name', 'icon', 'amenity_group_id'])
            ->groupBy('group')
            ->map(fn ($items, $group) => [
                'group' => $group ?: 'General',
                'items' => $items->map(fn (Amenity $a) => [
                    'id'      => $a->id,
                    'name_az' => $a->getTranslation('name', 'az'),
                    'name_ru' => $a->getTranslation('name', 'ru'),
                    'name_en' => $a->getTranslation('name', 'en'),
                    'icon'    => $a->icon,
                ])->values(),
            ])
            ->values();

        // Type-specific field schema describing what detail fields to render
        $fields = $this->typeFieldSchema($type);

        return response()->json([
            'type'       => $type->value,
            'categories' => $categories,
            'amenities'  => $amenities,
            'fields'     => $fields,
        ]);
    }

    /**
     * Return a descriptor array for the type-specific detail fields.
     * Each entry is: [name, label, type, required, options (for select), min, max, step]
     */
    private function typeFieldSchema(ListingType $type): array
    {
        return match ($type) {
            ListingType::Hotel => [
                ['name' => 'detail[stars]',          'label' => 'Stars',          'type' => 'select', 'required' => false,
                 'options' => [['value'=>'','label'=>'— Choose —'],['value'=>1,'label'=>'1 Star'],['value'=>2,'label'=>'2 Stars'],['value'=>3,'label'=>'3 Stars'],['value'=>4,'label'=>'4 Stars'],['value'=>5,'label'=>'5 Stars']]],
                ['name' => 'detail[total_rooms]',    'label' => 'Total Rooms',    'type' => 'number', 'required' => false, 'min' => 1],
                ['name' => 'detail[check_in_time]',  'label' => 'Check-In Time',  'type' => 'time',   'required' => false],
                ['name' => 'detail[check_out_time]', 'label' => 'Check-Out Time', 'type' => 'time',   'required' => false],
            ],
            ListingType::Home => [
                ['name' => 'detail[property_type]', 'label' => 'Property Type',  'type' => 'select', 'required' => true,
                 'options' => array_merge([['value' => '', 'label' => '— Choose —']], LookupOption::optionsFor('property_type'))],
                ['name' => 'detail[bedrooms]',      'label' => 'Bedrooms',       'type' => 'number', 'required' => true,  'min' => 1],
                ['name' => 'detail[bathrooms]',     'label' => 'Bathrooms',      'type' => 'number', 'required' => true,  'min' => 1],
                ['name' => 'detail[max_guests]',    'label' => 'Max Guests',     'type' => 'number', 'required' => true,  'min' => 1],
                ['name' => 'detail[total_area]',    'label' => 'Total Area (m²)','type' => 'number', 'required' => false, 'min' => 0, 'step' => '0.01'],
                ['name' => 'detail[floor]',         'label' => 'Floor',          'type' => 'number', 'required' => false],
            ],
            ListingType::Tour => [
                ['name' => 'detail[duration_hours]',   'label' => 'Duration (hours)',   'type' => 'number', 'required' => true, 'min' => 0.5, 'step' => '0.5'],
                ['name' => 'detail[max_participants]', 'label' => 'Max Participants',   'type' => 'number', 'required' => false, 'min' => 1],
                ['name' => 'detail[meeting_point]',    'label' => 'Meeting Point',      'type' => 'text',   'required' => false],
            ],
            ListingType::Activity => [
                ['name' => 'detail[duration_minutes]', 'label' => 'Duration (minutes)', 'type' => 'number', 'required' => true,  'min' => 15],
                ['name' => 'detail[max_participants]', 'label' => 'Max Participants',   'type' => 'number', 'required' => false, 'min' => 1],
            ],
            ListingType::Guide => [
                ['name' => 'detail[languages][]',      'label' => 'Languages Spoken',   'type' => 'pills', 'required' => true,  'color' => 'primary',
                 'options' => LookupOption::optionsFor('language')],
                ['name' => 'detail[experience_years]', 'label' => 'Years of Experience','type' => 'number', 'required' => false, 'min' => 0],
            ],
            ListingType::Restaurant => [
                ['name' => 'detail[cuisine_types][]', 'label' => 'Cuisine Types',    'type' => 'pills',  'required' => false, 'color' => 'danger',
                 'options' => LookupOption::optionsFor('cuisine_type')],
                ['name' => 'detail[price_range]',     'label' => 'Price Range',      'type' => 'select', 'required' => true,
                 'options' => [['value'=>'','label'=>'— Choose —'],['value'=>'budget','label'=>'Budget (₼)'],['value'=>'mid','label'=>'Mid-range (₼₼)'],['value'=>'upscale','label'=>'Upscale (₼₼₼)'],['value'=>'fine_dining','label'=>'Fine Dining (₼₼₼₼)']]],
                ['name' => 'detail[has_outdoor]',     'label' => 'Outdoor Seating',  'type' => 'checkbox', 'required' => false],
                ['name' => 'detail[has_delivery]',    'label' => 'Delivery',         'type' => 'checkbox', 'required' => false],
                ['name' => 'detail[has_takeaway]',    'label' => 'Takeaway',         'type' => 'checkbox', 'required' => false],
                ['name' => 'detail[menu_url]',        'label' => 'Menu URL',         'type' => 'url',    'required' => false],
            ],
        };
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function validationRules(ListingType $type): array
    {
        $base = [
            'type'                            => 'required|string',
            'contact_email'                   => 'nullable|email|max:255',
            'contact_phone'                   => 'nullable|string|max:30',
            'website_url'                     => 'nullable|url|max:255',
            'social_links'                    => 'nullable|array',
            'social_links.*.platform'         => 'required|string|in:instagram,facebook,tiktok,whatsapp',
            'social_links.*.value'            => 'required|string|max:255',
            'translations.az.title'           => 'required|string|max:255',
            'translations.az.description'     => 'required|string',
            'translations.az.address'         => 'nullable|string|max:255',
            'translations.ru.title'           => 'nullable|string|max:255',
            'translations.ru.description'     => 'nullable|string',
            'translations.ru.address'         => 'nullable|string|max:255',
            'translations.en.title'           => 'nullable|string|max:255',
            'translations.en.description'     => 'nullable|string',
            'translations.en.address'         => 'nullable|string|max:255',
            'location.region_id'              => 'nullable|integer|exists:regions,id',
            'location.latitude'               => 'nullable|numeric|between:-90,90',
            'location.longitude'              => 'nullable|numeric|between:-180,180',
            'location.postal_code'            => 'nullable|string|max:20',
            'categories'                      => 'nullable|array',
            'categories.*'                    => 'integer|exists:categories,id',
            'amenities'                       => 'nullable|array',
            'amenities.*'                     => 'integer|exists:amenities,id',
        ];

        $typeRules = match ($type) {
            ListingType::Hotel => [
                'detail.stars'          => 'nullable|integer|between:1,5',
                'detail.total_rooms'    => 'nullable|integer|min:1',
                'detail.check_in_time'  => 'nullable|string|max:10',
                'detail.check_out_time' => 'nullable|string|max:10',
            ],
            ListingType::Home => [
                'detail.property_type' => ['required', Rule::in(LookupOption::valuesFor('property_type'))],
                'detail.bedrooms'      => 'required|integer|min:1',
                'detail.bathrooms'     => 'required|integer|min:1',
                'detail.max_guests'    => 'required|integer|min:1',
                'detail.total_area'    => 'nullable|numeric',
                'detail.floor'         => 'nullable|integer',
            ],
            ListingType::Tour => [
                'detail.duration_hours'          => 'required|numeric|min:0.5',
                'detail.max_participants'        => 'nullable|integer|min:1',
                'detail.meeting_point'           => 'nullable|string|max:255',
                'detail.itinerary'               => 'nullable|array',
                'detail.itinerary.*.title'       => 'nullable|string|max:255',
                'detail.itinerary.*.description' => 'nullable|string',
            ],
            ListingType::Activity => [
                'detail.duration_minutes' => 'required|integer|min:15',
                'detail.max_participants' => 'nullable|integer|min:1',
            ],
            ListingType::Guide => [
                'detail.languages'        => ['required', 'array', 'min:1'],
                'detail.languages.*'      => Rule::in(LookupOption::valuesFor('language')),
                'detail.experience_years' => 'nullable|integer|min:0',
            ],
            ListingType::Restaurant => [
                'detail.cuisine_types'    => ['nullable', 'array'],
                'detail.cuisine_types.*'  => Rule::in(LookupOption::valuesFor('cuisine_type')),
                'detail.price_range'      => 'required|string',
                'detail.has_outdoor'  => 'boolean',
                'detail.has_delivery' => 'boolean',
                'detail.has_takeaway' => 'boolean',
                'detail.menu_url'     => 'nullable|url|max:255',
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
