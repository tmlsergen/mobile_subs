<?php

namespace App\Console\Commands;

use App\Services\SubscriptionSearchService;
use Illuminate\Console\Command;
use App\Models\Subscription;

class IndexAllSubs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:indexallsubs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index all subscriptions';

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
    public function handle(SubscriptionSearchService $esService)
    {
        $page = 1;
        $limit = 250;
        $count = 0;
        $devices = Subscription::orderBy('id')->take($limit)->get();
        while ($devices->count()) {
            $esService->bulkIndex($devices);
            $count += $devices->count();
            $page += 1;
            $devices = Subscription::orderBy('id','desc')->skip(($page - 1) * $limit)->take($limit)->get();

            echo "$count \n";
        }

        return 0;
    }
}
