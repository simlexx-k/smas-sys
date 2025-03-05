<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SchoolClass;

class SchoolClassPolicy
{
    public function view(User $user, SchoolClass $class): bool
    {
        if ($user->hasRole('landlord')) {
            return true;
        }

        // Check if user is a teacher of this class
        if ($user->isTeacher()) {
            return $user->teacher->classes->contains($class);
        }

        return false;
    }

    public function takeAttendance(User $user, SchoolClass $class): bool
    {
        if (!$user->isTeacher()) {
            return false;
        }

        return $user->teacher->classes->contains($class);
    }
} 