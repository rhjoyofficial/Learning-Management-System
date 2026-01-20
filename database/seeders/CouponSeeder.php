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
            Coupon::create([
                'code' => strtoupper($course->slug) . '_50',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'expires_at' => now()->addDays(30),
                'is_active' => true,
                'course_id' => $course->id,
            ]);

            Coupon::create([
                'code' => strtoupper($course->slug) . '_FREE',
                'discount_type' => 'free',
                'discount_value' => null,
                'expires_at' => now()->addDays(7),
                'is_active' => true,
                'course_id' => $course->id,
            ]);

            Coupon::create([
                'code' => strtoupper($course->slug) . '_300',
                'discount_type' => 'fixed',
                'discount_value' => 300,
                'expires_at' => null, // no expiry
                'is_active' => true,
                'course_id' => $course->id,
            ]);
        }
    }
}
