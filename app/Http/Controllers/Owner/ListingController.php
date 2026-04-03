<?php

namespace App\Http\Controllers\Owner;

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
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Listing;
use App\Models\LookupOption;
use App\Models\Region;
use Illuminate\Validation\Rule;
use App\Services\ListingPublishService;
use App\Services\SystemSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    public function __construct(
        private ListingPublishService $publishService,
        private SystemSettingsService $settings,
    ) {}

    // -------------------------------------------------------------------------
    // Owner's own listings
    // -------------------------------------------------------------------------

    public function index()
    {
        $listings = Auth::user()
            ->listings()
            ->with(['translations', 'location.region', 'payments' => fn ($q) => $q->latest()])
            ->latest()
            ->paginate(20);

        $publishedCount = $this->publishService->countPublishedListings(Auth::user());
        $freeLimit      = $this->settings->getInt('free_published_listings_limit');

        return view('owner.listings.index', compact('listings', 'publishedCount', 'freeLimit'));
    }

    public function create()
    {
        $this->authorize('create', Listing::class);

        $regions    = Region::whereNull('parent_id')->with('children')->orderBy('name->az')->get();
        $categories = Category::roots()->ordered()->get();
        $amenities  = Amenity::with('amenityGroup')->orderByRaw("COALESCE(amenity_group_id, 999999)")->orderBy('name->az')->get()->groupBy('group');

        return view('owner.listings.create', compact('regions', 'categories', 'amenities'));
    }

    public function store(Request $request, CreateListingAction $action)
    {
        $this->authorize('create', Listing::class);

        $type = ListingType::from($request->input('type'));

        $validated = $request->validate($this->validationRules($type));

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
            userId:       Auth::id(),
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

        return redirect()->route('owner.listings.show', $listing->slug)
            ->with('success', 'Listing created. It is now in draft — submit it for review when ready.');
    }

    public function show(string $slug)
    {
        $listing = Auth::user()
            ->listings()
            ->with(['translations', 'location.region', 'media', 'amenities', 'categories'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('view', $listing);

        $locale = app()->getLocale();

        return view('owner.listings.show', compact('listing', 'locale'));
    }

    public function edit(string $slug)
    {
        $listing = Auth::user()
            ->listings()
            ->with(['translations', 'location.region', 'media', 'amenities', 'categories'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('update', $listing);

        $regions    = Region::whereNull('parent_id')->with('children')->orderBy('name->az')->get();
        $categories = Category::roots()->ordered()->get();
        $amenities  = Amenity::with('amenityGroup')->orderByRaw("COALESCE(amenity_group_id, 999999)")->orderBy('name->az')->get()->groupBy('group');

        $detailRecord = $listing->detail()->first();
        $detailValues = $detailRecord ? $detailRecord->toArray() : [];
        $mediaItems   = $listing->media()->orderBy('sort_order')->get();

        return view('owner.listings.edit', compact('listing', 'regions', 'categories', 'amenities', 'detailValues', 'mediaItems'));
    }

    public function update(Request $request, string $slug)
    {
        $listing = Auth::user()
            ->listings()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('update', $listing);

        $type = $listing->type;

        $validated = $request->validate($this->validationRules($type));

        // Update core listing fields
        $listing->update([
            'contact_email' => $validated['contact_email'] ?? $listing->contact_email,
            'contact_phone' => $validated['contact_phone'] ?? $listing->contact_phone,
            'website_url'   => $validated['website_url'] ?? $listing->website_url,
            'social_links'  => $validated['social_links'] ?? $listing->social_links,
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

        return redirect()->route('owner.listings.show', $listing->slug)
            ->with('success', 'Listing updated successfully.');
    }

    public function submit(string $slug)
    {
        $listing = Auth::user()
            ->listings()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('update', $listing);

        $status = $listing->status instanceof \App\Enums\ListingStatus
            ? $listing->status
            : \App\Enums\ListingStatus::from((string) $listing->status);

        if (! $status->canTransitionTo(\App\Enums\ListingStatus::PendingReview)) {
            return back()->with('error', 'Это объявление нельзя отправить на проверку в текущем статусе.');
        }

        $listing->status = \App\Enums\ListingStatus::PendingReview;
        $listing->save();

        return redirect()->route('owner.listings.show', $listing->slug)
            ->with('success', 'Объявление отправлено на проверку. Мы уведомим вас о результате.');
    }

    public function toggleStatus(string $slug)
    {
        $listing = Auth::user()
            ->listings()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('update', $listing);

        $status = $listing->status instanceof \App\Enums\ListingStatus
            ? $listing->status
            : \App\Enums\ListingStatus::from((string) $listing->status);

        if ($status === \App\Enums\ListingStatus::Published) {
            $listing->status = \App\Enums\ListingStatus::Suspended;
            $listing->save();
            return back()->with('success', 'Объявление приостановлено и скрыто с сайта.');
        }

        if ($status === \App\Enums\ListingStatus::Suspended) {
            if (! $this->publishService->canPublishFree(Auth::user())) {
                $freeLimit = $this->settings->getInt('free_published_listings_limit');
                return back()->with('error',
                    "Вы достигли лимита бесплатных объявлений ({$freeLimit}). " .
                    'Для активации этого объявления необходима оплата. ' .
                    'Обратитесь к администратору или приостановите другое активное объявление.'
                );
            }
            $listing->status = \App\Enums\ListingStatus::Published;
            $listing->save();
            return back()->with('success', 'Объявление активировано и снова отображается на сайте.');
        }

        return back()->with('error', 'Смена статуса недоступна для этого объявления.');
    }

    public function destroy(string $slug)
    {
        $listing = Auth::user()
            ->listings()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('delete', $listing);

        if ($listing->payment_required) {
            return back()->with('error', 'This listing cannot be deleted while a payment is outstanding. Withdraw it to draft first.');
        }

        $listing->delete();

        return redirect()->route('owner.listings.index')
            ->with('success', 'Listing deleted successfully.');
    }

    // -------------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------------

    /**
     * GET /owner/api/listing-type-data?type=hotel|home|tour|...
     * Returns categories, amenities, and field schema for the given listing type.
     */
    public function listingTypeData(Request $request): \Illuminate\Http\JsonResponse
    {
        $type = ListingType::tryFrom($request->input('type', ''));

        if (! $type) {
            return response()->json(['error' => 'Invalid listing type.'], 422);
        }

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

        $fields = $this->typeFieldSchema($type);

        return response()->json([
            'type'       => $type->value,
            'categories' => $categories,
            'amenities'  => $amenities,
            'fields'     => $fields,
        ]);
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
            'translations.az.seo_title'       => 'nullable|string|max:255',
            'translations.az.seo_description' => 'nullable|string|max:500',
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
                'detail.duration_hours'   => 'required|numeric|min:0.5',
                'detail.max_participants' => 'nullable|integer|min:1',
                'detail.meeting_point'    => 'nullable|string|max:255',
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
                'detail.has_delivery'     => 'boolean',
                'detail.has_takeaway'     => 'boolean',
                'detail.menu_url'         => 'nullable|url|max:255',
            ],
        };

        return array_merge($base, $typeRules);
    }

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
                ['name' => 'detail[duration_hours]',   'label' => 'Duration (hours)',  'type' => 'number', 'required' => true, 'min' => 0.5, 'step' => '0.5'],
                ['name' => 'detail[max_participants]', 'label' => 'Max Participants',  'type' => 'number', 'required' => false, 'min' => 1],
                ['name' => 'detail[meeting_point]',    'label' => 'Meeting Point',     'type' => 'text',   'required' => false],
            ],
            ListingType::Activity => [
                ['name' => 'detail[duration_minutes]', 'label' => 'Duration (minutes)', 'type' => 'number', 'required' => true,  'min' => 15],
                ['name' => 'detail[max_participants]', 'label' => 'Max Participants',   'type' => 'number', 'required' => false, 'min' => 1],
            ],
            ListingType::Guide => [
                ['name' => 'detail[languages][]',      'label' => 'Languages Spoken',   'type' => 'pills', 'required' => true, 'color' => 'primary',
                 'options' => LookupOption::optionsFor('language')],
                ['name' => 'detail[experience_years]', 'label' => 'Years of Experience','type' => 'number', 'required' => false, 'min' => 0],
            ],
            ListingType::Restaurant => [
                ['name' => 'detail[cuisine_types][]', 'label' => 'Cuisine Types',   'type' => 'pills',  'required' => false, 'color' => 'danger',
                 'options' => LookupOption::optionsFor('cuisine_type')],
                ['name' => 'detail[price_range]',     'label' => 'Price Range',     'type' => 'select', 'required' => true,
                 'options' => [['value'=>'','label'=>'— Choose —'],['value'=>'budget','label'=>'Budget ($)'],['value'=>'mid','label'=>'Mid-range ($$)'],['value'=>'upscale','label'=>'Upscale ($$$)'],['value'=>'fine_dining','label'=>'Fine Dining ($$$$)']]],
                ['name' => 'detail[has_outdoor]',     'label' => 'Outdoor Seating', 'type' => 'checkbox', 'required' => false],
                ['name' => 'detail[has_delivery]',    'label' => 'Delivery',        'type' => 'checkbox', 'required' => false],
                ['name' => 'detail[has_takeaway]',    'label' => 'Takeaway',        'type' => 'checkbox', 'required' => false],
                ['name' => 'detail[menu_url]',        'label' => 'Menu URL',        'type' => 'url',    'required' => false],
            ],
        };
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
