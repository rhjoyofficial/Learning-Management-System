<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Course;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::whereHas('roles', fn($q) => $q->where('name', 'student'))->first();
        $course = Course::where('is_paid', true)->first();

        if (!$student || !$course) {
            $this->command->warn('Skipping ReviewSeeder: student or course not found');
            return;
        }

        Review::firstOrCreate([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ], [
            'rating' => 5,
            'comment' => 'Excellent course with clear explanations.',
        ]);
    }
}
