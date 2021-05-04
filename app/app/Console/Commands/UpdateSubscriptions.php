<?php

namespace App\Console\Commands;

use App\Business\SubscriptionManager;
use App\Services\DeviceSearchService;
use App\Services\SubscriptionSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class UpdateSubscriptions extends Command
{
    const OPERATING_SYSTEM = [
        'android',
        'ios'
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and Update Subscription Status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(SubscriptionManager $subscriptionManager, DeviceSearchService $deviceSearchService, SubscriptionSearchService $subscriptionSearchService)
    {
        $googleParams = [
            'stringFacets' => [
                [
                    'name' => 'operating_system',
                    'slugs' => [self::OPERATING_SYSTEM[0]]
                ]
            ]
        ];

        $iosParams = [
            'stringFacets' => [
                [
                    'name' => 'operating_system',
                    'slugs' => [self::OPERATING_SYSTEM[1]]
                ]
            ]
        ];

        $googleDevices = $deviceSearchService->search($googleParams);
        $iosDevices = $deviceSearchService->search($iosParams);


        $subscriptionManager->detect($googleDevices['results'], self::OPERATING_SYSTEM[0]);
        $subscriptionManager->detect($iosDevices['results'], self::OPERATING_SYSTEM[1]);

        Artisan::call('elasticsearch:indexallsubs');

        return 1;
    }
}
