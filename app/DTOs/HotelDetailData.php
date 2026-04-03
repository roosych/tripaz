<?php

namespace App\DTOs;

final readonly class HotelDetailData
{
    public function __construct(
        public ?int $stars = null,
        public ?int $totalRooms = null,
        public ?string $checkInTime = null,
        public ?string $checkOutTime = null,
        public ?array $policies = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            stars:         $data['stars'] ?? null,
            totalRooms:    $data['total_rooms'] ?? null,
            checkInTime:   $data['check_in_time'] ?? null,
            checkOutTime:  $data['check_out_time'] ?? null,
            policies:      $data['policies'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'stars'          => $this->stars,
            'total_rooms'    => $this->totalRooms,
            'check_in_time'  => $this->checkInTime,
            'check_out_time' => $this->checkOutTime,
            'policies'       => $this->policies,
        ];
    }
}
