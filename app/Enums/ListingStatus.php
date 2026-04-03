<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Draft           = 'draft';
    case PendingReview   = 'pending_review';
    case Published       = 'published';
    case AwaitingPayment = 'awaiting_payment';
    case Rejected        = 'rejected';
    case Suspended       = 'suspended';

    /**
     * Returns the allowed next states for the current state.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::Draft           => [self::PendingReview],
            self::PendingReview   => [self::Published, self::AwaitingPayment, self::Rejected],
            self::AwaitingPayment => [self::Published, self::Draft],
            self::Rejected        => [self::PendingReview],
            self::Published       => [self::Suspended, self::Draft],
            self::Suspended       => [self::Published],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), strict: true);
    }

    /**
     * Returns true for statuses that count toward the host's free listing limit.
     * Only active (Published) listings occupy a slot; suspended ones are free.
     */
    public function countsTowardFreeLimit(): bool
    {
        return $this === self::Published;
    }

    /**
     * Returns true only for statuses that should appear in the search index.
     */
    public function isSearchable(): bool
    {
        return $this === self::Published;
    }
}
