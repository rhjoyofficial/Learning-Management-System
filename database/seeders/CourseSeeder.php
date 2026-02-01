<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;
use Carbon\Carbon; // Import Carbon

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

        // Main Course Entry
        Course::create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => '২০ ঘন্টায় কুরআন শেখা —উপহার কোর্স',
            'slug' => Str::slug('quran20'),
            'description' => 'কুরআন শেখার ইচ্ছা আছে কিন্তু কোথা থেকে শুরু করবেন বুঝতে পারছেন না? এই কোর্সটি এমনভাবে তৈরি করা হয়েছে যেন যে কেউ, যেকোনো বয়সে, অল্প সময়েই কুরআন পড়ার ভিত্তি গড়ে তুলতে পারেন',
            'duration' => '২০ দিন',
            'image' => '/images/quran-course-poster.png',
            'price' => 12000,
            'offer_price' => 6000,
            'is_paid' => true,
            'level' => 'advanced',
            'status' => 'published',
            'demo_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 
            'start_at' => Carbon::now()->addDays(7), 
            'end_at' => Carbon::now()->addMonths(3), 
        ]);

        $courses = [
            ['ট্র্যাকার ভিডিও ট্রেনিং (রেকর্ডেড)', 'দৈনিক অভ্যাস, লক্ষ্য অর্জন ও নিজের অগ্রগতি পর্যবেক্ষণের জন্য একটি সহজ ও কার্যকর ভিডিও ট্রেনিং প্রোগ্রাম।', '/images/tracker-video.png', '১ দিন', 0, false],
            ['আত্মিক উন্নয়ন কোর্স', 'মানসিক প্রশান্তি, আত্মনিয়ন্ত্রণ ও জীবনের উদ্দেশ্য খুঁজে পেতে একটি ধীর কিন্তু গভীর আত্মিক যাত্রা।', '/images/spiritual-course.jpg', '৮ সপ্তাহ', 3999, true],
        ];

        foreach ($courses as [$title, $description, $img, $duration, $price, $paid]) {
            Course::create([
                'instructor_id' => $instructor->id,
                'category_id' => $category->id,
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => $description,
                'image' => $img,
                'duration' => $duration,
                'price' => $price,
                'is_paid' => $paid,
                'level' => 'beginner',
                'status' => 'published',
                'demo_video_url' => 'https://www.youtube.com/embed/example',
                'start_at' => now(),
                'end_at' => now()->addYear(),
            ]);
        }
    }
}
