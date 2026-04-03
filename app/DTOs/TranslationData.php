<?php

namespace App\DTOs;

use App\Enums\Locale;

final readonly class TranslationData
{
    public function __construct(
        public Locale $locale,
        public string $title,
        public string $description,
        public ?string $address = null,
        public ?string $seoTitle = null,
        public ?string $seoDescription = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            locale:         Locale::from($data['locale']),
            title:          $data['title'],
            description:    $data['description'] ?? '',
            address:        $data['address'] ?? null,
            seoTitle:       $data['seo_title'] ?? null,
            seoDescription: $data['seo_description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'locale'          => $this->locale->value,
            'title'           => $this->title,
            'description'     => $this->description,
            'address'         => $this->address,
            'seo_title'       => $this->seoTitle,
            'seo_description' => $this->seoDescription,
        ];
    }
}
