<?php

namespace App\Http\Controllers\Api\Subscription;

use App\Business\SubscriptionManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __invoke(Request $request, SubscriptionManager $subscriptionManager)
    {
        $validated = $request->validate([
            'receipt' => 'string|required'
        ]);

        $device = auth()->user();

        [$subscription, $subscriptionExpireDate] = $subscriptionManager->purchase($validated['receipt'], $device);

        if (!$subscription){
            return response_error([
                'status' => false,
            ], 'Subs Error');
        }

        return response_success([
            'status' => true,
            'expire_date' => $subscriptionExpireDate,
            'client' => $device
        ]);
    }
}
