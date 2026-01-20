<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Services\Bkash\BkashTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BkashCheckoutController extends Controller
{
    public function checkout(Request $request, Course $course, BkashTokenService $bkash)
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
            'gateway' => 'bkash',
        ]);

        $token = $bkash->getToken();

        $response = Http::withToken($token)->post(
            config('services.bkash.base_url') . '/v1.2.0-beta/tokenized/checkout/create',
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

        $data = $response->json();

        if (! isset($data['paymentID'])) {
            return response()->json(['message' => 'bKash initialization failed'], 500);
        }

        $payment->update([
            'gateway_payment_id' => $data['paymentID'],
        ]);

        return response()->json([
            'bkash_url' => $data['bkashURL'],
        ]);
    }
}
