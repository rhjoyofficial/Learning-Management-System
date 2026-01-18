<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;

class EnrollmentPolicy
{
  public function enroll(User $user, Course $course): bool
  {
    return !$user->enrollments()
      ->where('course_id', $course->id)
      ->exists();
  }
}
