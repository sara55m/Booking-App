<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $target): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $target): bool
    {
        // Super admin can update anyone except we can add
        if ($user->role === 'super_admin') {
            return true;
        }

        // Normal admin can only update normal users.
        return $target->role === 'user';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $target): bool
    {
        // A normal admin cannot delete admins or super admins.
         if ($user->role !== 'super_admin') {
            return $target->role === 'user';
        }

        // Never allow a super admin to delete themselves.
        if ($user->is($target)) {
            return false;
        }

        // A super admin may delete another super admin
        // only when at least one other super admin remains.
        if ($target->role === 'super_admin') {
            return User::where('role', 'super_admin')
                ->whereNull('deleted_at')
                ->count() > 1;
        }

        // Super admin can delete normal users.
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $target): bool
    {
        if ($target->role !== 'user') {
            return $user->role === 'super_admin';
        }

        return in_array($user->role, ['admin', 'super_admin'], true);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $target): bool
    {
        // Only super admin can permanently delete.
        if ($user->role !== 'super_admin') {
            return false;
        }

        // Never permanently delete yourself.
        if ($user->is($target)) {
            return false;
        }

        // Never permanently delete the last super admin.
        if ($target->role === 'super_admin') {
            return User::where('role', 'super_admin')
                ->whereNull('deleted_at')
                ->count() > 1;
        }

        return true;
    }
}
