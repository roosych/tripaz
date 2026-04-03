<?php

namespace App\Policies;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    /**
     * Anyone can view published listings.
     * Owners, admins, and moderators can view any status.
     */
    public function view(?User $user, Listing $listing): bool
    {
        if ($listing->status === ListingStatus::Published) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return $this->isOwner($user, $listing)
            || $user->hasAnyRole(['admin', 'moderator']);
    }

    /**
     * Any authenticated user can create a listing.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the owner, admin, or moderator can update a listing.
     */
    public function update(User $user, Listing $listing): bool
    {
        return $this->isOwner($user, $listing)
            || $user->hasAnyRole(['admin', 'moderator']);
    }

    /**
     * Only the owner or admin can delete a listing.
     */
    public function delete(User $user, Listing $listing): bool
    {
        return $this->isOwner($user, $listing)
            || $user->hasRole('admin');
    }

    /**
     * Only admins and moderators can perform moderation actions.
     */
    public function moderate(User $user, Listing $listing): bool
    {
        return $user->hasAnyRole(['admin', 'moderator']);
    }

    /**
     * Only the owner or admin can restore a soft-deleted listing.
     */
    public function restore(User $user, Listing $listing): bool
    {
        return $this->isOwner($user, $listing)
            || $user->hasRole('admin');
    }

    /**
     * Only an admin can permanently delete a listing.
     */
    public function forceDelete(User $user, Listing $listing): bool
    {
        return $user->hasRole('admin');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function isOwner(User $user, Listing $listing): bool
    {
        return $listing->user_id === $user->id;
    }
}
