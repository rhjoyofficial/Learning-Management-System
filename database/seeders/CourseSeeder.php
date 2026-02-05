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
        $instructor = User::whereHas('roles', fn($q) => $q->where('name', 'instructor'))->get();
        $category = Category::get();

        if (!$instructor || !$category->count()) {
            $this->command->warn('Skipping CourseSeeder: instructor or category not found');
            return;
        }

        // Main Course Entry
        Course::create([
            'instructor_id' => $instructor->first()->id,
            'category_id' => $category->first()->id,
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
            'promo_text' => 'প্রোমো কোড থাকলে কোর্সটি ১০০% ফ্রি',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addMonths(3),
        ]);

        $courses = [
            ['ট্র্যাকার ভিডিও ট্রেনিং (রেকর্ডেড)', 'Tracker Video Training', 'দৈনিক অভ্যাস, লক্ষ্য অর্জন ও নিজের অগ্রগতি পর্যবেক্ষণের জন্য একটি সহজ ও কার্যকর ভিডিও ট্রেনিং প্রোগ্রাম।', '/images/tracker-video.png', '১ দিন', 0, false, 'প্রোমো কোড থাকলে কোর্সটি ১০০% ফ্রি'],
            // ['আত্মিক উন্নয়ন কোর্স', 'মানসিক প্রশান্তি, আত্মনিয়ন্ত্রণ ও জীবনের উদ্দেশ্য খুঁজে পেতে একটি ধীর কিন্তু গভীর আত্মিক যাত্রা।', '/images/spiritual-course.jpg', '৮ সপ্তাহ', 3999, true],
        ];

        foreach ($courses as [$title_bn, $title, $description, $img, $duration, $price, $paid, $promo_text]) {
            Course::create([
                'instructor_id' => $instructor->where('email', 'cmmoin@gmail.com')->first()->id,
                'category_id' => $category->where('slug', 'ramadan-campaigns')->first()->id,
                'title' => $title_bn,
                'slug' => Str::slug($title),
                'description' => $description,
                'image' => $img,
                'duration' => $duration,
                'price' => $price,
                'is_paid' => $paid,
                'promo_text' => $promo_text,
                'level' => 'beginner',
                'status' => 'published',
                'demo_video_url' => 'https://www.youtube.com/embed/tBbdSzwxqyY?si=huPcxXyTUk8u3ZJ9',
                'start_at' => now(),
                'end_at' => now()->addYear(),
            ]);
        }
    }
}
