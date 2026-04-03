<?php

namespace App\DTOs;

final readonly class TourDetailData
{
    public function __construct(
        public float $durationHours,
        public ?int $maxParticipants = null,
        public ?string $meetingPoint = null,
        public ?array $includes = null,
        public ?array $excludes = null,
        public ?array $itinerary = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            durationHours:   (float) $data['duration_hours'],
            maxParticipants: isset($data['max_participants']) ? (int) $data['max_participants'] : null,
            meetingPoint:    $data['meeting_point'] ?? null,
            includes:        $data['includes'] ?? null,
            excludes:        $data['excludes'] ?? null,
            itinerary:       $data['itinerary'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'duration_hours'   => $this->durationHours,
            'max_participants' => $this->maxParticipants,
            'meeting_point'    => $this->meetingPoint,
            'includes'         => $this->includes,
            'excludes'         => $this->excludes,
            'itinerary'        => $this->itinerary,
        ];
    }
}
