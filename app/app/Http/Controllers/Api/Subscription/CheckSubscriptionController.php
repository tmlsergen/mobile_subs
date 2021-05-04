<?php

namespace App\Http\Controllers\Api\Subscription;

use App\Business\SubscriptionManager;
use App\Http\Controllers\Controller;

class CheckSubscriptionController extends Controller
{
    public function __invoke(SubscriptionManager $subscriptionManager)
    {
        $status = $subscriptionManager->check(auth()->user());

        if (!$status){
            return response_error($status, 'Subs Ended');
        }

        return response_success($status, 'Subs Ok');
    }

}
