<?php

namespace App\DTOs;

final readonly class LocationData
{
    public function __construct(
        public ?int $regionId = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $postalCode = null,
        public ?string $placeId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            regionId:   $data['region_id'] ?? null,
            latitude:   isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude:  isset($data['longitude']) ? (float) $data['longitude'] : null,
            postalCode: $data['postal_code'] ?? null,
            placeId:    $data['place_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'region_id'   => $this->regionId,
            'latitude'    => $this->latitude,
            'longitude'   => $this->longitude,
            'postal_code' => $this->postalCode,
            'place_id'    => $this->placeId,
        ];
    }
}
