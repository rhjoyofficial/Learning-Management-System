<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function view(?User $user, Lesson $lesson): bool
    {
        // Free lessons are always accessible
        if ($lesson->is_free) {
            return true;
        }

        // Paid lesson requires enrollment
        if (!$user) {
            return false;
        }

        // Check if lesson has an associated module
        if (!$lesson->module) {
            return false;
        }

        return $user->enrollments()
            ->where('course_id', $lesson->module->course_id)
            ->whereNull('revoked_at')
            ->exists();
    }
}
