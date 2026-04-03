<?php

namespace App\DTOs;

use App\Enums\PropertyType;

final readonly class HomeDetailData
{
    public function __construct(
        public PropertyType $propertyType,
        public int $bedrooms = 1,
        public int $bathrooms = 1,
        public int $maxGuests = 2,
        public ?float $totalArea = null,
        public ?int $floor = null,
        public ?array $houseRules = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            propertyType: PropertyType::from($data['property_type']),
            bedrooms:     (int) ($data['bedrooms'] ?? 1),
            bathrooms:    (int) ($data['bathrooms'] ?? 1),
            maxGuests:    (int) ($data['max_guests'] ?? 2),
            totalArea:    isset($data['total_area']) ? (float) $data['total_area'] : null,
            floor:        isset($data['floor']) ? (int) $data['floor'] : null,
            houseRules:   $data['house_rules'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'property_type' => $this->propertyType->value,
            'bedrooms'      => $this->bedrooms,
            'bathrooms'     => $this->bathrooms,
            'max_guests'    => $this->maxGuests,
            'total_area'    => $this->totalArea,
            'floor'         => $this->floor,
            'house_rules'   => $this->houseRules,
        ];
    }
}
