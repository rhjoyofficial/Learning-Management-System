<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Lesson;

class LessonPolicy
{
    public function view(?User $user, Lesson $lesson): bool
    {
        if ($lesson->is_free) {
            return true;
        }

        if (!$user) {
            return false;
        }

        return $user->enrollments()
            ->where('course_id', $lesson->module->course_id)
            ->exists();
    }
}
