<?php

namespace App\DTOs;

use App\Enums\PriceRange;

final readonly class RestaurantDetailData
{
    public function __construct(
        public array $cuisineTypes,
        public PriceRange $priceRange = PriceRange::Mid,
        public bool $hasOutdoor = false,
        public bool $hasDelivery = false,
        public bool $hasTakeaway = false,
        public ?array $openingHours = null,
        public ?string $menuUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cuisineTypes:    $data['cuisine_types'] ?? [],
            priceRange:      PriceRange::from($data['price_range'] ?? 'mid'),
            hasOutdoor:      (bool) ($data['has_outdoor'] ?? false),
            hasDelivery:     (bool) ($data['has_delivery'] ?? false),
            hasTakeaway:     (bool) ($data['has_takeaway'] ?? false),
            openingHours:    $data['opening_hours'] ?? null,
            menuUrl:         $data['menu_url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'cuisine_types'    => $this->cuisineTypes,
            'price_range'      => $this->priceRange->value,
            'has_outdoor'      => $this->hasOutdoor,
            'has_delivery'     => $this->hasDelivery,
            'has_takeaway'     => $this->hasTakeaway,
            'opening_hours'    => $this->openingHours,
            'menu_url'         => $this->menuUrl,
        ];
    }
}
