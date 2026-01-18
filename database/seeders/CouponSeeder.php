<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Models\Course;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupon = Coupon::create([
            'code' => 'WELCOME50',
            'discount_type' => 'percent',
            'discount_value' => 50,
            'usage_limit' => 100,
            'expires_at' => now()->addDays(30),
        ]);

        $coupon->courses()->attach(
            Course::where('is_paid', true)->pluck('id')
        );
    }
}
