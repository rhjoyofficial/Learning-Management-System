<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function checkout(Request $request, Course $course)
    {
        if ($course->status !== 'published' || ! $course->is_paid) {
            return response()->json(['message' => 'Course not purchasable.'], 403);
        }

        if ($request->user()->enrollments()
            ->where('course_id', $course->id)->exists()
        ) {
            return response()->json(['message' => 'Already enrolled.'], 409);
        }

        $payment = Payment::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            'amount' => $course->price,
            'currency' => 'BDT',
            'status' => 'pending',
            'transaction_id' => Str::uuid(),
        ]);

        return response()->json([
            'payment_id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
        ]);
    }
}
