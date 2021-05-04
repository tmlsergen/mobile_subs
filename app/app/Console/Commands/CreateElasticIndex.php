<?php

namespace App\Console\Commands;

use App\Services\ElasticsearchService;
use Illuminate\Console\Command;

class CreateElasticIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:create-index {index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It creates the index with the mapping';

    private $mappings = [
        "properties" => [
            "date_sorting" => [
                "type" => "date"
            ],
            "full_text" => [
                "type" => "text"
            ],
            "full_text_boosted" => [
                "type" => "text"
            ],
            "number_facet" => [
                "properties" => [
                    "facet_name" => [
                        "type" => "keyword"
                    ],
                    "facet_value" => [
                        "type" => "double"
                    ]
                ],
                "type" => "nested"
            ],
            "search_result_data" => [
                "enabled" => false,
                "type" => "object"
            ],
            "string_facet" => [
                "properties" =>  [
                    "facet_composite_key" => [
                        "type" => "keyword"
                    ],
                    "facet_name" => [
                        "normalizer" => "lowercase_normalizer",
                        "type" => "keyword"
                    ],
                    "facet_slug" => [
                        "normalizer" => "lowercase_normalizer",
                        "type" => "keyword"
                    ],
                    "facet_value" => [
                        "normalizer" => "lowercase_normalizer",
                        "type" => "keyword"
                    ]
                ],
                "type" => "nested"
            ]
        ]
    ];

    private $settings = [
        "analysis" => [
            "normalizer" => [
                "lowercase_normalizer" =>  [
                    "filter" => [
                        "lowercase"
                    ],
                    "type" => "custom"
                ]
            ]
        ],
        "index" => [
            "number_of_shards" => 2,
            "number_of_replicas" => 1
        ]
    ];


    public function handle(ElasticsearchService $esService)
    {
        $index = $this->argument('index');
        return $esService->createIndex($index, $this->mappings, $this->settings);
    }
}
