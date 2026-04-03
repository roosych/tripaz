<?php

namespace App\DTOs;

final readonly class GuideDetailData
{
    public function __construct(
        public array $languages,
        public ?int $experienceYears = null,
        public ?array $certifications = null,
        public ?string $bioExtra = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            languages:       $data['languages'],
            experienceYears: isset($data['experience_years']) ? (int) $data['experience_years'] : null,
            certifications:  $data['certifications'] ?? null,
            bioExtra:        $data['bio_extra'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'languages'        => $this->languages,
            'experience_years' => $this->experienceYears,
            'certifications'   => $this->certifications,
            'bio_extra'        => $this->bioExtra,
        ];
    }
}
