<?php

namespace App\Services;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;
use DomainException;

class ModerationService
{
    public function __construct(
        private readonly ListingPublishService $publishService,
    ) {}

    /**
     * Approve a listing that is pending review.
     * Delegates to ListingPublishService to determine the correct
     * post-approval status (Published or AwaitingPayment) based on the
     * host's free listing quota.
     *
     * @throws DomainException If the moderator lacks permission or the listing is not pending review.
     */
    public function approve(Listing $listing, User $moderator): Listing
    {
        $this->guardIsModerator($moderator);
        $this->guardIsPendingReview($listing);

        return $this->publishService->publishListing($listing, $moderator);
    }

    /**
     * Reject a listing that is pending review.
     *
     * @throws DomainException If the moderator lacks permission or transition is invalid.
     */
    public function reject(Listing $listing, User $moderator, string $reason): Listing
    {
        $this->guardIsModerator($moderator);
        $this->guardTransition($listing, ListingStatus::Rejected);

        $listing->status = ListingStatus::Rejected;
        $listing->save();

        // Remove from search index — rejected listings must not appear
        $listing->unsearchable();

        return $listing;
    }

    /**
     * Suspend a published listing.
     *
     * @throws DomainException If the moderator lacks permission or transition is invalid.
     */
    public function suspend(Listing $listing, User $moderator, string $reason): Listing
    {
        $this->guardIsModerator($moderator);
        $this->guardTransition($listing, ListingStatus::Suspended);

        $listing->status = ListingStatus::Suspended;
        $listing->save();

        // Remove from search index
        $listing->unsearchable();

        return $listing;
    }

    /**
     * Reinstate a suspended listing back to published.
     *
     * @throws DomainException If the moderator lacks permission or transition is invalid.
     */
    public function reinstate(Listing $listing, User $moderator): Listing
    {
        $this->guardIsModerator($moderator);
        $this->guardTransition($listing, ListingStatus::Published);

        $listing->status = ListingStatus::Published;
        $listing->save();

        $listing->searchable();

        return $listing;
    }

    // -------------------------------------------------------------------------
    // Guards
    // -------------------------------------------------------------------------

    private function guardIsModerator(User $moderator): void
    {
        if (! $moderator->hasAnyRole(['admin', 'moderator'])) {
            throw new DomainException('Only admins and moderators can perform moderation actions.');
        }
    }

    /**
     * Guards that the listing is currently in pending_review status.
     * Used exclusively by approve(), which can lead to either Published
     * or AwaitingPayment — so a single-target guardTransition() would be wrong.
     */
    private function guardIsPendingReview(Listing $listing): void
    {
        if ($listing->status !== ListingStatus::PendingReview) {
            throw new DomainException(
                "Cannot approve a listing that is not pending review. Current status: [{$listing->status->value}]."
            );
        }
    }

    private function guardTransition(Listing $listing, ListingStatus $target): void
    {
        if (! $listing->status->canTransitionTo($target)) {
            throw new DomainException(
                "Cannot transition listing from [{$listing->status->value}] to [{$target->value}]."
            );
        }
    }
}
