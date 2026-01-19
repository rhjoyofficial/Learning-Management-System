<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SSLCommerzCheckoutController extends Controller
{
    public function checkout(Request $request, Course $course)
    {
        if (! $course->is_paid || $course->status !== 'published') {
            return response()->json(['message' => 'Course not purchasable'], 403);
        }

        $payment = Payment::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            'amount' => $course->price,
            'currency' => 'BDT',
            'status' => 'pending',
            'transaction_id' => (string) Str::uuid(),
            'gateway' => 'sslcommerz',
        ]);

        $baseUrl = config('services.sslcommerz.sandbox')
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';

        $response = Http::asForm()->post(
            $baseUrl . '/gwprocess/v4/api.php',
            [
                'store_id' => config('services.sslcommerz.store_id'),
                'store_passwd' => config('services.sslcommerz.store_password'),
                'total_amount' => $payment->amount,
                'currency' => 'BDT',
                'tran_id' => $payment->transaction_id,
                'success_url' => url(config('services.sslcommerz.success_url')),
                'fail_url' => url(config('services.sslcommerz.fail_url')),
                'cancel_url' => url(config('services.sslcommerz.cancel_url')),
                'ipn_url' => url(config('services.sslcommerz.ipn_url')),

                'cus_name' => $request->user()->name,
                'cus_email' => $request->user()->email,
                'cus_add1' => 'N/A',
                'cus_city' => 'Dhaka',
                'cus_country' => 'Bangladesh',
            ]
        );

        $data = $response->json();

        if (! isset($data['GatewayPageURL'])) {
            return response()->json(['message' => 'Payment initialization failed'], 500);
        }

        return response()->json([
            'payment_id' => $payment->id,
            'gateway_url' => $data['GatewayPageURL'],
        ]);
    }
}
