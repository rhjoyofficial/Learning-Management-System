<?php

namespace App\Http\Controllers\Api\Student;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Models\CouponUsage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\CouponService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Services\Bkash\BkashTokenService;

class BkashCheckoutController extends Controller
{
    public function checkout(Request $request, Course $course, CouponService $couponService, BkashTokenService $bkash)
    {
        // Course must be paid & published
        if (! $course->is_paid || $course->status !== 'published') {
            return response()->json([
                'message' => 'Course not purchasable',
            ], 403);
        }

        /**
         * 1️⃣ Validate coupon (if provided)
         */
        $couponData = null;

        if ($request->filled('coupon_code')) {
            $couponData = $couponService->validateCoupon(
                $request->coupon_code,
                $course,
                $request->user()
            );

            if (! $couponData['valid']) {
                return response()->json([
                    'message' => $couponData['message'],
                ], 422);
            }
        }

        /**
         * 2️⃣ Determine final payable amount
         */
        $finalAmount = $couponData
            ? $couponData['final_price']
            : $course->price;

        /**
         * 3️⃣ If 100% discount → auto enroll (NO PAYMENT)
         */
        if ($finalAmount == 0) {
            $enrollment = Enrollment::firstOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'course_id' => $course->id,
                ],
                [
                    'enrolled_at' => now(),
                ]
            );

            // Only create coupon usage if enrollment was just created
            if ($enrollment->wasRecentlyCreated && isset($couponData['coupon_id'])) {
                CouponUsage::create([
                    'coupon_id' => $couponData['coupon_id'],
                    'user_id' => $request->user()->id,
                    'used_at' => now(),
                ]);
            }

            return response()->json([
                'message' => $enrollment->wasRecentlyCreated
                    ? 'Enrolled successfully using coupon'
                    : 'Already enrolled in this course',
            ]);
        }

        /**
         * 4️⃣ Create payment record
         */
        $payment = Payment::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            'coupon_id' => $couponData['coupon_id'] ?? null,
            'amount' => $finalAmount,
            'currency' => 'BDT',
            'status' => 'pending',
            'transaction_id' => (string) Str::uuid(),
            'gateway' => 'bkash',
        ]);


        /**
         * 5️⃣ Create bKash checkout session
         */
        try {
            $token = $bkash->getToken();

            $response = Http::timeout(10)
                ->withToken($token)
                ->post(
                    config('services.bkash.base_url') .
                        '/v1.2.0-beta/tokenized/checkout/create',
                    [
                        'mode' => '0011',
                        'payerReference' => $request->user()->email,
                        'callbackURL' => url(config('services.bkash.callback_url')),
                        'amount' => $payment->amount,
                        'currency' => 'BDT',
                        'intent' => 'sale',
                        'merchantInvoiceNumber' => $payment->transaction_id,
                    ]
                );

            if (! $response->successful()) {
                throw new \Exception('bKash API returned ' . $response->status());
            }

            $data = $response->json();

            if (! isset($data['paymentID'])) {
                throw new \Exception('Missing paymentID in bKash response');
            }
        } catch (\Exception $e) {
            \Log::error('bKash checkout failed', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
                'course_id' => $course->id,
                'payment_id' => $payment->id,
            ]);

            return response()->json([
                'message' => 'Payment gateway temporarily unavailable. Please try again.',
            ], 503);
        }

        /**
         * 6️⃣ Store gateway payment ID
         */
        $payment->update([
            'gateway_payment_id' => $data['paymentID'],
        ]);

        return response()->json([
            'bkash_url' => $data['bkashURL'],
        ]);
    }
}
