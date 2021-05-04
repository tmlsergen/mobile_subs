<?php

namespace App\Events;

use App\Models\Subscription;
use App\Services\CallbackService;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubscriptionCanceled
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private const MESSAGE = 'canceled';
    private Subscription $subscription;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }


    public function handle(CallbackService $callbackService)
    {
        $callbackService->callback($this->subscription, self::MESSAGE);
    }
}
