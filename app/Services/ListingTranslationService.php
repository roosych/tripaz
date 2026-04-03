<?php

namespace App\Services;

use App\Enums\Locale;
use App\Models\Listing;
use App\Models\ListingTranslation;
use RuntimeException;

class ListingTranslationService
{
    /**
     * Return the best available translation for the listing.
     *
     * Fallback chain: requested locale → az → en → any available.
     *
     * @throws RuntimeException When the listing has no translations at all.
     */
    public function getTranslation(Listing $listing, string|Locale $locale): ListingTranslation
    {
        $listing->loadMissing('translations');

        if ($listing->translations->isEmpty()) {
            throw new RuntimeException(
                "Listing [{$listing->id}] has no translations."
            );
        }

        $localeEnum = $locale instanceof Locale
            ? $locale
            : (Locale::tryFrom($locale) ?? Locale::Az);

        // Try the requested locale
        $match = $this->findByLocale($listing, $localeEnum);

        if ($match !== null) {
            return $match;
        }

        // Walk the fallback chain
        foreach ($localeEnum->fallbackChain() as $fallback) {
            $match = $this->findByLocale($listing, $fallback);

            if ($match !== null) {
                return $match;
            }
        }

        // Final fallback: return whatever is available
        return $listing->translations->first();
    }

    /**
     * Return all translations for a listing keyed by locale string.
     *
     * @return array<string, ListingTranslation>
     */
    public function getAllTranslations(Listing $listing): array
    {
        $listing->loadMissing('translations');

        return $listing->translations
            ->keyBy(fn(ListingTranslation $t) => $t->locale->value)
            ->all();
    }

    /**
     * Upsert a translation — creates it if not present, updates if already exists.
     */
    public function upsertTranslation(Listing $listing, array $data): ListingTranslation
    {
        /** @var ListingTranslation $translation */
        $translation = $listing->translations()->updateOrCreate(
            ['locale' => $data['locale']],
            $data,
        );

        // If the slug source changed (az title), regenerate slug
        if (($data['locale'] ?? '') === 'az' && isset($data['title'])) {
            $listing->generateSlug();
            $listing->save();
        }

        return $translation;
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function findByLocale(Listing $listing, Locale $locale): ?ListingTranslation
    {
        return $listing->translations->first(
            fn(ListingTranslation $t) => $t->locale === $locale
        );
    }
}
