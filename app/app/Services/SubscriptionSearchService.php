<?php

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Support\Arr;
use stdClass;

class SubscriptionSearchService
{

    private $esService;

    public function __construct(ElasticsearchService $esService)
    {
        $this->esService = $esService;
    }


    public function bulkIndex($subscriptions)
    {
        $body = [];
        foreach ($subscriptions as $subscription) {
            $body[] = $this->getIndexBody($subscription);
        }
        return $this->esService->bulkIndex('subscriptions', $body);
    }

    public function index(Subscription $subscription)
    {
        $esData = $this->getIndexBody($subscription);
        return $this->esService->indexDocument('subscriptions', $esData);
    }

    public function getIndexBody(Subscription $subscription)
    {
        $searchResultData = [
            'id' => $subscription->id,
            'device_id' => $subscription->device_id,
            'status' => $subscription->status,
            'receipt' => $subscription->receipt,
            'expire_date' => $subscription->expire_date
        ];

        $dateSorting = $subscription->created_at->toAtomString();

        $stringFacets = [
            [
                "facet_name" => "id",
                "facet_slug" => $subscription->id,
                "facet_value" => $subscription->id,
                "facet_composite_key" => "{$subscription->id}~{$subscription->id}"
            ],
            [
                "facet_name" => "device_id",
                "facet_slug" => $subscription->device_id,
                "facet_value" => $subscription->device_id,
                "facet_composite_key" => "{$subscription->device_id}~{$subscription->device_id}"
            ],
        ];

        $esData = [
            "id" => "{$subscription->id}",
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

        if (!empty($params['ids'])) {
            $query = $this->getQueryForIds($params['ids'], $query);
        }

        if (!empty($params['term'])) {
            $query = $this->getQueryForSearchTerm($params['term'], $query);
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

        $docs = $this->esService->search('subscriptions', $body);
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

    private function getQueryForSearchTerm($term, $initialQuery = [])
    {
        $baseBoostValue = Arr::get($term, 'boost', 10);
        $searchTerm = Arr::get($term, 'searchTerm');

        $searchBoolQuery = [
            'bool' => [
                'should' => [
                    ['wildcard' => [
                        'full_text' => [
                            'value' => "*{$searchTerm}*",
                            'boost' => $baseBoostValue * 5
                        ]
                    ]],
                    ['multi_match' => [
                        'fields' => ["full_text_boosted^" . $baseBoostValue, "full_text^" . $baseBoostValue * 0.5],
                        'type' => 'most_fields',
                        'query' => $searchTerm,
                        'fuzziness' => 'AUTO'
                    ]]
                ],
                'minimum_should_match' => 1
            ]
        ];

        if (empty($initialQuery['bool'])) {
            $initialQuery['bool'] = [];
        }

        if (empty($initialQuery['bool']['must'])) {
            $initialQuery['bool']['must'] = [];
        }

        $initialQuery['bool']['must'][] = $searchBoolQuery;
        return $initialQuery;
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


    private function getQueryForIds($ids, $initialQuery)
    {
        if (empty($initialQuery['bool'])) {
            $initialQuery['bool'] = [];
        }

        $queryType = Arr::get($ids, 'queryType', 'filter');
        $baseBoostValue = Arr::get($ids, 'boost', 10);

        if (empty($initialQuery['bool'][$queryType])) {
            $initialQuery['bool'][$queryType] = [];
        }

        $initialQuery['bool'][$queryType][] = [
            "ids" => [
                "values" => $ids['values'],
                "boost" => $baseBoostValue
            ]
        ];

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
