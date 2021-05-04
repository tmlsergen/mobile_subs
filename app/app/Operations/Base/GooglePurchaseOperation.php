<?php

namespace App\Operations\Base;

use App\Operations\Contacts\PurchaseOperationInterface;
use GuzzleHttp\Client;

class GooglePurchaseOperation implements PurchaseOperationInterface
{

    public function purchase($device, $receipt)
    {
        $client = new Client([
            'base_uri' => config('purchase.google_api'),
        ]);

        $response = $client->request('POST','purchase-google', [
            'form_params' => [
                'client' => $device,
                'receipt' => $receipt
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        ]);

        $respJson = $response->getBody()->getContents();

        return json_decode($respJson);
    }

    public function detect($subs)
    {
        $client = new Client([
            'base_uri' => config('purchase.google_api'),
        ]);

        $respIds = [];

        foreach($subs as $sub){
            $response = $client->request('POST','detect-google', [
                'form_params' => [
                    'subscription' => $sub,
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ]
            ]);

            $response = json_decode($response->getBody()->getContents());

            if ($response->status != 'success'){
                continue;
            }

            $respIds[] = $response->id;
        }

        return $respIds;
    }
}
