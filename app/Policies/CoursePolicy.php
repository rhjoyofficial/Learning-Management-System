<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;

class CoursePolicy
{
    public function view(?User $user, Course $course): bool
    {
        if ($course->status === 'published') {
            return true;
        }

        // Allow the owner or admin to see drafts
        return $user && ($user->id === $course->instructor_id || $user->hasRole('admin'));
    }

    public function create(User $user): bool
    {
        return $user->hasRole('instructor') || $user->hasRole('admin');
    }

    public function update(User $user, Course $course): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('instructor')
            && $course->instructor_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->hasRole('admin');
    }
}
