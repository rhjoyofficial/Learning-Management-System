<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use App\Models\CouponUsage;

class CouponService
{
    public function validateCoupon(string $code, Course $course, ?User $user = null): array
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
        if ($user) {
            $alreadyUsed = CouponUsage::where('coupon_id', $coupon->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyUsed) {
                return [
                    'valid' => false,
                    'message' => 'You have already used this coupon',
                ];
            }
        }

        $discount = match ($coupon->discount_type) {
            'free' => $course->price,
            'percentage' => round(($course->price * $coupon->discount_value) / 100, 2),
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
