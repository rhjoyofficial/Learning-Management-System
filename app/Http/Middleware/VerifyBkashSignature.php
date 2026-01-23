<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyBkashSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Bkash-Signature');
        $payload = $request->getContent();

        // Get webhook secret from config
        $secret = config('services.bkash.webhook_secret');

        if (!$secret) {
            \Log::warning('bKash webhook secret not configured');
            // In development, you might want to skip verification
            // In production, this should abort
            if (app()->environment('production')) {
                abort(403, 'Webhook verification not configured');
            }
            return $next($request);
        }

        if (!$signature) {
            \Log::warning('bKash webhook signature missing', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            abort(403, 'Webhook signature missing');
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            \Log::error('bKash webhook signature mismatch', [
                'ip' => $request->ip(),
                'expected' => substr($expectedSignature, 0, 10) . '...',
                'received' => substr($signature, 0, 10) . '...',
            ]);
            abort(403, 'Invalid webhook signature');
        }

        return $next($request);
    }
}
