<?php

namespace App\DTOs;

use App\Enums\ListingType;
use InvalidArgumentException;

final readonly class CreateListingData
{
    /**
     * @param  array<TranslationData>  $translations  Must contain at least the 'az' translation.
     * @param  HotelDetailData|HomeDetailData|TourDetailData|ActivityDetailData|GuideDetailData|RestaurantDetailData  $detail
     */
    public function __construct(
        public ?int $userId,
        public ListingType $type,
        public array $translations,
        public LocationData $location,
        public HotelDetailData|HomeDetailData|TourDetailData|ActivityDetailData|GuideDetailData|RestaurantDetailData $detail,
        public ?float $priceFrom = null,
        public ?string $contactEmail = null,
        public ?string $contactPhone = null,
        public ?string $websiteUrl = null,
        public ?array $socialLinks = null,
        public array $categoryIds = [],
        public array $amenityIds = [],
    ) {
        $this->guardAtLeastAzTranslation();
    }

    private function guardAtLeastAzTranslation(): void
    {
        $hasAz = false;

        foreach ($this->translations as $translation) {
            if ($translation->locale->value === 'az') {
                $hasAz = true;
                break;
            }
        }

        if (! $hasAz) {
            throw new InvalidArgumentException('At least an Azerbaijani (az) translation is required.');
        }
    }
}
