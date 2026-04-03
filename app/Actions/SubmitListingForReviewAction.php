<?php

namespace App\Actions;

use App\Enums\Locale;
use App\Enums\ListingStatus;
use App\Models\Listing;
use DomainException;

class SubmitListingForReviewAction
{
    /**
     * Validate the listing and transition it from draft → pending_review.
     *
     * Validation rules:
     * - Must have an Azerbaijani (az) translation.
     * - Must have a type-specific detail record.
     * - Must currently be in draft or rejected status.
     *
     * @throws DomainException When transition preconditions are not met.
     */
    public function execute(Listing $listing): Listing
    {
        $this->guardTransitionAllowed($listing);
        $this->guardHasAzTranslation($listing);
        $this->guardHasDetailRecord($listing);

        $listing->status = ListingStatus::PendingReview;
        $listing->save();

        return $listing;
    }

    private function guardTransitionAllowed(Listing $listing): void
    {
        if (! $listing->status->canTransitionTo(ListingStatus::PendingReview)) {
            throw new DomainException(
                "Listing in status [{$listing->status->value}] cannot be submitted for review."
            );
        }
    }

    private function guardHasAzTranslation(Listing $listing): void
    {
        $listing->loadMissing('translations');

        $hasAz = $listing->translations->contains(
            fn($t) => $t->locale === Locale::Az || $t->locale->value === 'az'
        );

        if (! $hasAz) {
            throw new DomainException(
                'Listing must have an Azerbaijani (az) translation before review submission.'
            );
        }
    }

    private function guardHasDetailRecord(Listing $listing): void
    {
        $detail = $listing->detail()->exists();

        if (! $detail) {
            throw new DomainException(
                "Listing must have a [{$listing->type->value}] detail record before review submission."
            );
        }
    }
}
