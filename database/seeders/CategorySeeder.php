<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'ব্যক্তিগত উন্নয়ন',
                'slug' => 'personal-development',
                'children' => [
                    ['name' => 'ফিনান্সিয়াল শিক্ষা', 'slug' => 'financial-education'],
                    ['name' => 'অভ্যাস ও ট্র্যাকিং', 'slug' => 'habit-tracking'],
                    ['name' => 'লক্ষ্য নির্ধারণ', 'slug' => 'goal-setting'],
                ],
            ],
            [
                'name' => 'আর্থিক শিক্ষা',
                'slug' => 'financial-literacy',
                'children' => [
                    ['name' => 'আয় বৃদ্ধি', 'slug' => 'income-growth'],
                    ['name' => 'বাজেট ও সঞ্চয়', 'slug' => 'budget-savings'],
                    ['name' => 'বিনিয়োগ পরিকল্পনা', 'slug' => 'investment-planning'],
                ],
            ],
            [
                'name' => 'আত্মিক ও মানসিক উন্নয়ন',
                'slug' => 'spiritual-mental-growth',
                'children' => [
                    ['name' => 'আত্মিক উন্নয়ন', 'slug' => 'spiritual-growth'],
                    ['name' => 'মানসিক প্রশান্তি', 'slug' => 'mental-peace'],
                    ['name' => 'আত্মনিয়ন্ত্রণ', 'slug' => 'self-discipline'],
                ],
            ],
            [
                'name' => 'ফ্রি ট্রেনিং',
                'slug' => 'free-training',
                'children' => [
                    ['name' => 'রেকর্ডেড ভিডিও', 'slug' => 'recorded-videos'],
                    ['name' => 'ওয়ার্কশপ', 'slug' => 'workshops'],
                ],
            ],
        ];

        foreach ($categories as $parent) {
            $parentCategory = Category::create([
                'name' => $parent['name'],
                'slug' => $parent['slug'],
            ]);

            foreach ($parent['children'] as $child) {
                Category::create([
                    'name' => $child['name'],
                    'slug' => $child['slug'],
                    'parent_id' => $parentCategory->id,
                ]);
            }
        }
    }
}
