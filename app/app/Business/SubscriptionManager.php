<?php

namespace App\Business;

use App\Events\SubscriptionReNewed;
use App\Events\SubscriptionStarted;
use App\Repositories\SubscriptionRepository;
use App\Services\PurchaseService;
use App\Services\SubscriptionSearchService;
use Illuminate\Contracts\Auth\Authenticatable;

class SubscriptionManager
{
    private SubscriptionRepository $subscriptionRepository;
    private PurchaseService $purchaseService;
    private SubscriptionSearchService $searchService;

    public function __construct(SubscriptionRepository $subscriptionRepository, PurchaseService $purchaseService, SubscriptionSearchService $searchService)
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->purchaseService = $purchaseService;
        $this->searchService = $searchService;
    }

    public function purchase($receipt, Authenticatable $device)
    {
        $response = $this->purchaseService->purchase($device, $receipt);

        if (!$response->status) {

            return [null, null];
        }

        $subData = [
            'device_id' => $response->client->id,
            'status' => 'a',
            'receipt' => $receipt,
            'expire_date' => $response->expire_date
        ];

        $subscription = $this->subscriptionRepository->create($subData);

        return [$subscription, $subData['expire_date']];
    }

    public function check($device)
    {
        $esParams = [
            'stringFacets' => [
                [
                    'name' => 'device_id',
                    'slugs' => [$device->id]
                ]
            ]
        ];

        $subs = $this->searchService->search($esParams);

        if (!($subs['totalCount'] > 0)){
            return false;
        }

        return checkExpireDate($subs['results'][0]['expire_date']);

    }

    public function detect($devices, $operating_system)
    {
        $devicesCollection = collect($devices);
        $deviceIds = $devicesCollection->pluck('id')->toArray();

        $esParams = [
            'stringFacets' => [
                [
                    'name' => 'device_id',
                    'slugs' => $deviceIds
                ]
            ]
        ];

        $subs = $this->searchService->search($esParams);

        $subIds = $this->purchaseService->detect($subs['results'], $operating_system);

        $this->subscriptionRepository->updateStatus($subIds);
    }
}
