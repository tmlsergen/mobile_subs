<?php

namespace App\Services;

use Elasticsearch\Client;

class ElasticsearchService
{
    private $esClient;

    public function __construct(Client $esClient)
    {
        $this->esClient = $esClient;
    }

    public function search($index, $body)
    {
        if (!$this->isEnabled()) {
            return null;
        }

        try {
            $params = [
                'index' => $index,
                'body'  => $body
            ];

            $results = $this->esClient->search($params);
            return $results;
        } catch (\Exception $e) {
            \Log::error("Elasticsearch search error|{$e->getMessage()}");
            return null;
        }
    }

    public function createIndex(string $index, array $mappings, array $settings)
    {
        if (!$this->isEnabled()) {
            return null;
        }

        try {
            $params = [
                'index' => $index,
                'body' => [
                    'settings' => $settings,
                    'mappings' => $mappings
                ]
            ];

            // Create the index with mappings and settings now
            $response = $this->esClient->indices()->create($params);
            return $response;
        } catch (\Exception $e) {
            \Log::error("Error occured on creating elastic index({$index})| {$e->getMessage()}");
            return null;
        }
    }

    public function indexDocument(string $index, array $body)
    {
        if (!$this->isEnabled()) {
            return null;
        }

        try {
            $params = [
                'index' => $index,
                'id' => $body['id'],
                'body' => $body
            ];

            // Create the index with mappings and settings now
            $response = $this->esClient->index($params);
            return $response;
        } catch (\Exception $e) {
            \Log::error("Error occured on creating adding document({$body['id']}) to index({$index}) | {$e->getMessage()}");
            return null;
        }
    }

    public function bulkIndex(string $index, array $body)
    {
        if (!$this->isEnabled()) {
            return null;
        }

        try {
            $params = ['body' => []];

            for ($i = 0; $i < count($body); $i++) {
                $params['body'][] = [
                    'index' => [
                        '_index' => $index,
                        '_id'    => $body[$i]['id']
                    ]
                ];

                $params['body'][] = $body[$i];

                // Every 1000 documents stop and send the bulk request
                if ($i % 1000 == 0) {
                    $responses = $this->esClient->bulk($params);

                    // erase the old bulk request
                    $params = ['body' => []];

                    // unset the bulk response when you are done to save memory
                    unset($responses);
                }
            }

            // Send the last batch if it exists
            if (!empty($params['body'])) {
                $responses = $this->esClient->bulk($params);
            }

            return $responses;
        } catch (\Exception $e) {
            \Log::error("Error occured on creating adding to index({$index}) | {$e->getMessage()}");
            return null;
        }
    }

    private function isEnabled()
    {
        return config('elasticsearch.enabled', false);
    }
}
