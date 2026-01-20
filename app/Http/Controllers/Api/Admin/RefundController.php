<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function refund(Request $request, Payment $payment)
    {
        if ($payment->status !== 'success' || $payment->refunded_at) {
            return response()->json([
                'message' => 'Payment not refundable.'
            ], 422);
        }

        // Block refund if certificate exists (default policy)
        if (Certificate::where('user_id', $payment->user_id)
            ->where('course_id', $payment->course_id)
            ->exists()
        ) {
            return response()->json([
                'message' => 'Refund not allowed after certificate issuance.'
            ], 403);
        }

        DB::transaction(function () use ($payment, $request) {

            // 1. Mark payment refunded
            $payment->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'refund_amount' => $payment->amount,
                'refund_reason' => $request->input('reason', 'Admin refund'),
            ]);

            // 2. Revoke enrollment
            Enrollment::where('user_id', $payment->user_id)
                ->where('course_id', $payment->course_id)
                ->update([
                    'revoked_at' => now(),
                    'revocation_reason' => 'Refund issued',
                ]);
        });

        return response()->json([
            'message' => 'Refund processed and access revoked.'
        ]);
    }
}
