<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SSLCommerzIPNController extends Controller
{
    public function handle(Request $request)
    {
        $tranId = $request->input('tran_id');
        $status = $request->input('status'); // VALID / FAILED

        $payment = Payment::where('transaction_id', $tranId)->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Idempotency
        if ($payment->status === 'success') {
            return response()->json(['message' => 'Already processed']);
        }

        // Validate payment with SSLCommerz
        $isValid = $this->validatePayment($request);

        if (! $isValid) {
            $payment->update(['status' => 'failed']);
            return response()->json(['message' => 'Validation failed'], 403);
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

        return response()->json(['message' => 'Payment confirmed']);
    }

    protected function validatePayment(Request $request): bool
    {
        $baseUrl = config('services.sslcommerz.sandbox')
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';

        $response = Http::asForm()->post(
            $baseUrl . '/validator/api/validationserverAPI.php',
            [
                'val_id' => $request->input('val_id'),
                'store_id' => config('services.sslcommerz.store_id'),
                'store_passwd' => config('services.sslcommerz.store_password'),
                'format' => 'json',
            ]
        );

        $data = $response->json();

        return isset($data['status']) && $data['status'] === 'VALID';
    }
}
