<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyRoleCustom([User::ROLE_TEACHER, User::ROLE_TENANT_ADMIN])
            ? Response::allow()
            : Response::deny('You do not have permission to view attendance records');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Attendance $attendance)
    {
        return $user->tenant_id === $attendance->tenant_id 
            && $user->hasAnyRoleCustom([User::ROLE_TEACHER, User::ROLE_TENANT_ADMIN]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attendance $attendance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attendance $attendance): bool
    {
        return false;
    }
}
