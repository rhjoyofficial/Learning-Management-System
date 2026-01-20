<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::whereHas('roles', fn($q) => $q->where('name', 'instructor'))->first();
        $category = Category::where('name', 'Laravel')->first();

        if (!$instructor || !$category) {
            $this->command->warn('Skipping CourseSeeder: instructor or category not found');
            return;
        }

        Course::create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => 'Laravel 12 From Scratch',
            'slug' => Str::slug('Laravel 12 From Scratch'),
            'description' => 'Complete Laravel 12 learning path.',
            'price' => 4999,
            'is_paid' => true,
            'level' => 'beginner',
            'status' => 'published',
        ]);

        Course::create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => 'Free Laravel Basics',
            'slug' => Str::slug('Free Laravel Basics'),
            'description' => 'Introduction to Laravel.',
            'price' => 0,
            'is_paid' => false,
            'level' => 'beginner',
            'status' => 'published',
        ]);
    }
}
