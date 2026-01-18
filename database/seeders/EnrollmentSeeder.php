<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->first();
        $course = Course::where('is_paid', true)->first();

        Enrollment::firstOrCreate([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }
}
