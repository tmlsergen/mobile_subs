<?php

namespace App\Providers;

use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $hosts = [[
            'host' => config('elasticsearch.host'),
            'port' => config('elasticsearch.port'),
            'scheme' => config('elasticsearch.scheme')
        ]];

        $client = ClientBuilder::create()->setHosts($hosts);

        $username = config('elasticsearch.username');
        $password = config('elasticsearch.password');
        if ($username && $password) {
            $client = $client->setBasicAuthentication($username, $password);
        }

        $client = $client->build();
        $this->app->singleton(\Elasticsearch\Client::class, function () use ($client) {
            return $client;
        });
    }
}
