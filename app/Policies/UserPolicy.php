<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Only admins can view the full user list.
     */
    public function viewAny(User $currentUser): bool
    {
        return $currentUser->hasRole('admin');
    }

    /**
     * A user can view their own profile; admins can view any profile.
     */
    public function view(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id
            || $currentUser->hasRole('admin');
    }

    /**
     * A user can update their own profile; admins can update any profile.
     */
    public function update(User $currentUser, User $targetUser): bool
    {
        return $currentUser->id === $targetUser->id
            || $currentUser->hasRole('admin');
    }

    /**
     * Only admins can change a user's role.
     */
    public function updateRole(User $currentUser, User $targetUser): bool
    {
        return $currentUser->hasRole('admin');
    }

    /**
     * Only admins can delete a user account.
     */
    public function delete(User $currentUser, User $targetUser): bool
    {
        return $currentUser->hasRole('admin');
    }
}
