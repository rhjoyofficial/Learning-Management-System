<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Course;
use Carbon\Carbon;
use App\Models\CouponUsage;
use Illuminate\Support\Facades\Auth;

class CouponService
{
    public function validateCoupon(string $code, Course $course): array
    {
        $coupon = Coupon::where('code', $code)
            ->where('course_id', $course->id)
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            return [
                'valid' => false,
                'message' => 'Invalid coupon code',
            ];
        }

        if ($coupon->expires_at && now()->greaterThan($coupon->expires_at)) {
            return [
                'valid' => false,
                'message' => 'Coupon has expired',
            ];
        }

        // 🔒 One-time use per user
        if (Auth::check()) {
            $alreadyUsed = CouponUsage::where('coupon_id', $coupon->id)
                ->where('user_id', Auth::id())
                ->exists();

            if ($alreadyUsed) {
                return [
                    'valid' => false,
                    'message' => 'You have already used this coupon',
                ];
            }
        }

        // $basePrice = $course->offer_price > 0 ? $course->offer_price : $course->price;

        // $discount = match ($coupon->discount_type) {
        //     'free' => $basePrice,
        //     'percentage' => round(($basePrice * $coupon->discount_value) / 100),
        //     'fixed' => min($coupon->discount_value, $basePrice),
        //     default => 0,
        // };

        $discount = match ($coupon->discount_type) {
            'free' => $course->price,
            'percentage' => round(($course->price * $coupon->discount_value) / 100),
            'fixed' => min($coupon->discount_value, $course->price),
            default => 0,
        };

        return [
            'valid' => true,
            'coupon_id' => $coupon->id,
            'discount_type' => $coupon->discount_type,
            'discount_amount' => $discount,
            'final_price' => max(0, $course->price - $discount),
        ];
    }
}
