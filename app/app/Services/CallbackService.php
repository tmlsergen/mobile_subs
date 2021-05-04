<?php

namespace App\Services;

use App\Models\Subscription;
use GuzzleHttp\Client;

class CallbackService
{
    public function callback(Subscription $subscription, string $message)
    {
        $device = $subscription->device;

        $client = new Client([
            'base_uri' => $device->callback_url,
        ]);

        $response = $client->request('POST','/', [
            'form_params' => [
                'receipt' => $subscription->receipt,
                'message' => $message
            ],

            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        ]);

        $respJson = $response->getBody()->getContents();

        return json_decode($respJson);
    }
}
