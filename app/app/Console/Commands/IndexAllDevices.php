<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Services\DeviceSearchService;

class IndexAllDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:indexalldevices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index all devices';

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
    public function handle(DeviceSearchService $esService)
    {
        $page = 1;
        $limit = 250;
        $count = 0;
        $devices = Device::orderBy('id')->take($limit)->get();
        while ($devices->count()) {
            $esService->bulkIndex($devices);
            $count += $devices->count();
            $page += 1;
            $devices = Device::orderBy('id','desc')->skip(($page - 1) * $limit)->take($limit)->get();

            echo "$count \n";
        }

        return 0;
    }
}
