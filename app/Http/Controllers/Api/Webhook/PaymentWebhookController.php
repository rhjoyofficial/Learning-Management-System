<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Verify signature (pseudo – depends on gateway)
        if (! $this->verifySignature($request)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionId = $request->input('transaction_id');
        $status = $request->input('status'); // success | failed

        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Idempotency
        if ($payment->status === 'success') {
            return response()->json(['message' => 'Already processed']);
        }

        DB::transaction(function () use ($payment, $status) {

            if ($status === 'success') {
                $payment->update(['status' => 'success']);

                Enrollment::firstOrCreate([
                    'user_id' => $payment->user_id,
                    'course_id' => $payment->course_id,
                ], [
                    'enrolled_at' => now(),
                ]);
            } else {
                $payment->update(['status' => 'failed']);
            }
        });

        return response()->json(['message' => 'Webhook processed']);
    }

    protected function verifySignature(Request $request): bool
    {
        // Gateway-specific implementation
        return true;
    }
}
