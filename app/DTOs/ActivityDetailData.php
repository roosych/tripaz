<?php

namespace App\DTOs;

final readonly class ActivityDetailData
{
    public function __construct(
        public int $durationMinutes,
        public ?int $maxParticipants = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            durationMinutes: (int) $data['duration_minutes'],
            maxParticipants: isset($data['max_participants']) ? (int) $data['max_participants'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'duration_minutes' => $this->durationMinutes,
            'max_participants' => $this->maxParticipants,
        ];
    }
}
