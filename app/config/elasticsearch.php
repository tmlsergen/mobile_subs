<?php

return [
    'enabled' => env('ELASTICSEARCH_ENABLED', true),
    'host' => env('ELASTICSEARCH_HOST', 'localhost'),
    'port' => env('ELASTICSEARCH_PORT', 9200),
    'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
    'username' => env('ELASTICSEARCH_USERNAME', null),
    'password' => env('ELASTICSEARCH_PASSWORD', null)
];
