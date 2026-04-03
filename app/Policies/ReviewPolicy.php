<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Admins and moderators can view any review (including unapproved ones).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'moderator']);
    }

    /**
     * Any authenticated user with the 'user' role can create a review.
     * Duplicate prevention is handled at the controller level.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('user');
    }

    /**
     * Only admins and moderators can approve a review.
     */
    public function approve(User $user, Review $review): bool
    {
        return $user->hasAnyRole(['admin', 'moderator']);
    }

    /**
     * Only admins and moderators can reject (hide) a review.
     */
    public function reject(User $user, Review $review): bool
    {
        return $user->hasAnyRole(['admin', 'moderator']);
    }

    /**
     * Only the review's author or an admin can delete a review.
     */
    public function delete(User $user, Review $review): bool
    {
        return $review->user_id === $user->id
            || $user->hasRole('admin');
    }
}
