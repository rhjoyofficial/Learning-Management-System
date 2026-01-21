<?php

namespace App\Services\Bkash;

use Illuminate\Support\Facades\Http;

class BkashTokenService
{
    public function getToken(): string
    {
        $response = Http::withHeaders([
            'username' => config('services.bkash.username'),
            'password' => config('services.bkash.password'),
        ])->post(
            config('services.bkash.base_url') . '/v1.2.0-beta/tokenized/checkout/token/grant',
            [
                'app_key' => config('services.bkash.app_key'),
                'app_secret' => config('services.bkash.app_secret'),
            ]
        );

        return $response->json('id_token');
    }
}
