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
        $category = Category::first();

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

        $courses = [
            ['Laravel 12 From Scratch', 4999, true],
            ['Advanced Laravel APIs', 3999, true],
            ['Laravel Performance Optimization', 2999, true],
            ['Free Laravel Basics', 0, false],
            ['PHP for Beginners', 0, false],
            ['OOP in PHP', 1999, true],
            ['MySQL Masterclass', 1499, true],
            ['Web Security Essentials', 0, false],
        ];

        foreach ($courses as [$title, $price, $paid]) {
            Course::create([
                'instructor_id' => $instructor->id,
                'category_id' => $category->id,
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => "Complete course on {$title}.",
                'price' => $price,
                'is_paid' => $paid,
                'level' => 'beginner',
                'status' => 'published',
            ]);
        }
    }
}
