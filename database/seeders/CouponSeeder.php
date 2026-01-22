<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Models\Course;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $paidCourses = Course::where('is_paid', true)->get();

        foreach ($paidCourses as $course) {

            // 50% coupon
            Coupon::create([
                'code' => strtoupper($course->slug) . '_50',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'expires_at' => now()->addDays(30),
                'is_active' => true,
                'course_id' => $course->id,
            ]);

            // FREE coupon
            Coupon::create([
                'code' => strtoupper($course->slug) . '_FREE',
                'discount_type' => 'free',
                'discount_value' => null,
                'expires_at' => now()->addDays(7),
                'is_active' => true,
                'course_id' => $course->id,
            ]);

            // Fixed amount coupon
            Coupon::create([
                'code' => strtoupper($course->slug) . '_300',
                'discount_type' => 'fixed',
                'discount_value' => 300,
                'expires_at' => null,
                'is_active' => true,
                'course_id' => $course->id,
            ]);

            // ✅ Special Ramadan coupon for Quran course
            if ($course->slug === 'quran20') {
                Coupon::create([
                    'code' => 'BIONICRAMADAN',
                    'discount_type' => 'percentage',
                    'discount_value' => 100, // 100% free
                    'expires_at' => now()->addDays(15), // optional
                    'is_active' => true,
                    'course_id' => $course->id,
                ]);
            }
        }
    }
}
