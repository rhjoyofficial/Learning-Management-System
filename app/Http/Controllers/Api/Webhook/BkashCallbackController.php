<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Services\Bkash\BkashTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class BkashCallbackController extends Controller
{
    public function handle(Request $request, BkashTokenService $bkash)
    {
        $paymentID = $request->input('paymentID');

        $payment = Payment::where('gateway_payment_id', $paymentID)->first();

        if (! $payment || $payment->status === 'success') {
            return response()->json(['message' => 'Invalid or already processed']);
        }

        $token = $bkash->getToken();

        $response = Http::withToken($token)->post(
            config('services.bkash.base_url') . '/v1.2.0-beta/tokenized/checkout/execute',
            ['paymentID' => $paymentID]
        );

        $data = $response->json();

        if (($data['statusCode'] ?? null) !== '0000') {
            $payment->update(['status' => 'failed']);
            return response()->json(['message' => 'Payment failed']);
        }

        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'success']);

            Enrollment::firstOrCreate([
                'user_id' => $payment->user_id,
                'course_id' => $payment->course_id,
            ], [
                'enrolled_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Payment successful']);
    }
}
