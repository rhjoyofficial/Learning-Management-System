<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCoupon(Request $request, CouponService $couponService)
    {
        $data = $request->validate([
            'code' => 'required|string',
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::findOrFail($data['course_id']);

        return response()->json(
            $couponService->validateCoupon($data['code'], $course)
        );
    }
}
