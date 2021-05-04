<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Arr;
use stdClass;

class DeviceSearchService
{

    private $esService;

    public function __construct(ElasticsearchService $esService)
    {
        $this->esService = $esService;
    }


    public function bulkIndex($devices)
    {
        $body = [];
        foreach ($devices as $device) {
            $body[] = $this->getIndexBody($device);
        }
        return $this->esService->bulkIndex('devices', $body);
    }

    public function index(Device $device)
    {
        $esData = $this->getIndexBody($device);
        return $this->esService->indexDocument('devices', $esData);
    }

    public function getIndexBody(Device $device)
    {
        $searchResultData = [
            'id' => $device->id,
            'u_id' => $device->u_id,
            'app_id' => $device->app_id,
            'language' => $device->language,
            'operating_system' => $device->operating_system
        ];

        $dateSorting = $device->created_at->toAtomString();

        $stringFacets = [
            [
                "facet_name" => "appId",
                "facet_slug" => $device->app_id,
                "facet_value" => $device->app_id,
                "facet_composite_key" => "{$device->app_id}~{$device->app_id}"
            ],
            [
                "facet_name" => "uId",
                "facet_slug" => $device->u_id,
                "facet_value" => $device->u_id,
                "facet_composite_key" => "{$device->u_id}~{$device->u_id}"
            ],
            [
                "facet_name" => "operating_system",
                "facet_slug" => $device->operating_system,
                "facet_value" => $device->operating_system,
                "facet_composite_key" => "{$device->operating_system}~{$device->operating_system}"
            ],
        ];

        $esData = [
            "id" => "{$device->u_id}_{$device->app_id}",
            "search_result_data" => $searchResultData,
            "string_facet" => $stringFacets,
            "date_sorting" => $dateSorting
        ];

        return $esData;
    }

    public function search($params = [], $options = [])
    {
        $query = [];

        if (!empty($params['matchAll'])) {
            $query = $this->getQueryForMatchAll($query);
        }

        if (!empty($params['stringFacets'])) {
            $query = $this->getQueryForStringFacets($params['stringFacets'], $query);
        }

        if (empty($query)) {
            $query = [
                'match_all' => new stdClass()
            ];
        }

        $body = [
            'query' => $query,
            "track_scores" => true
        ];

        $docs = $this->esService->search('devices', $body);
        //dd(json_encode($body));
        $data = [];
        $totalCount = 0;

        if ($docs !== null) {
            $hits = $docs['hits']['hits'];
            $totalCount = $docs['hits']['total']['value'];
            $data = Arr::pluck($hits, '_source.search_result_data');
        }

        return [
            'results' => $data,
            'totalCount' => $totalCount
        ];
    }

    private function getQueryForStringFacets($stringFacets, $initialQuery = [])
    {
        if (empty($initialQuery['bool'])) {
            $initialQuery['bool'] = [];
        }

        foreach ($stringFacets as $stringFacet) {
            $queryType = Arr::get($stringFacet, 'queryType', 'filter');

            if (empty($initialQuery['bool'][$queryType])) {
                $initialQuery['bool'][$queryType] = [];
            }

            $baseBoostValue = Arr::get($stringFacet, 'boost', 1);
            $slugs = Arr::get($stringFacet, 'slugs', [0, 0]);
            $type = Arr::get($stringFacet, 'type', 'terms');
            $name = Arr::get($stringFacet, 'name');

            $nestedQuery = [
                'nested' => [
                    'path' => 'string_facet',
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term' => ['string_facet.facet_name' => $name]]
                            ]
                        ]
                    ]
                ]
            ];

            if ($type === 'terms') {
                $nestedQuery['nested']['query']['bool']['must'][] = [
                    'terms' => [
                        'boost' => $baseBoostValue,
                        'string_facet.facet_slug' => $slugs
                    ]
                ];
            }

            if ($type === 'terms_set') {
                $nestedQuery['nested']['query']['bool']['must'][] = [
                    'terms_set' => [
                        'string_facet.facet_slug' => [
                            'boost' => $baseBoostValue,
                            'terms' => $slugs,
                            'minimum_should_match_script' => [
                                'source' => 'params.num_terms'
                            ]
                        ]
                    ]
                ];
            }

            $initialQuery['bool'][$queryType][] = $nestedQuery;
        }

        return $initialQuery;
    }

    private function getQueryForMatchAll($initialQuery)
    {
        if (empty($initialQuery['bool'])) {
            $initialQuery['bool'] = [];
        }

        $initialQuery['bool']['must'] = [
            'match_all' => new stdClass()
        ];

        return $initialQuery;
    }
}
