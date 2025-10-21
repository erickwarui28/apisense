<?php

namespace App\Services;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Illuminate\Support\Facades\Log;

class ElasticsearchService
{
    protected $client;
    protected $indexPrefix;

    public function __construct()
    {
        $this->indexPrefix = config('elasticsearch.index_prefix', 'apisense');
        
        $clientBuilder = ClientBuilder::create();
        
        if (config('elasticsearch.host')) {
            $clientBuilder->setHosts([config('elasticsearch.host')]);
        }
        
        if (config('elasticsearch.api_key')) {
            $clientBuilder->setApiKey(config('elasticsearch.api_key'));
        }
        
        // Disable SSL verification for Windows environments
        $clientBuilder->setSSLVerification(false);
        
        $this->client = $clientBuilder->build();
    }

    /**
     * Index an API repository document
     */
    public function indexApi($apiData)
    {
        try {
            $params = [
                'index' => $this->getIndexName('apis'),
                'id' => $apiData['id'],
                'body' => $apiData
            ];

            $response = $this->client->index($params);
            return $response;
        } catch (ClientResponseException $e) {
            Log::error('Elasticsearch indexing error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Search APIs using hybrid search (keyword + semantic)
     */
    public function searchApis($query, $filters = [], $size = 10)
    {
        try {
            $searchBody = [
                'size' => $size,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'multi_match' => [
                                    'query' => $query,
                                    'fields' => ['name^3', 'description^2', 'features', 'tags'],
                                    'type' => 'best_fields',
                                    'fuzziness' => 'AUTO'
                                ]
                            ]
                        ]
                    ]
                ],
                'highlight' => [
                    'fields' => [
                        'name' => new \stdClass(),
                        'description' => new \stdClass(),
                        'features' => new \stdClass()
                    ]
                ]
            ];

            // Add filters
            if (!empty($filters)) {
                $filterClauses = [];
                
                if (isset($filters['category'])) {
                    $filterClauses[] = ['term' => ['category' => $filters['category']]];
                }
                
                if (isset($filters['pricing'])) {
                    $filterClauses[] = ['term' => ['pricing' => $filters['pricing']]];
                }
                
                if (isset($filters['min_rating'])) {
                    $filterClauses[] = ['range' => ['community_rating' => ['gte' => $filters['min_rating']]]];
                }
                
                if (!empty($filterClauses)) {
                    $searchBody['query']['bool']['filter'] = $filterClauses;
                }
            }

            $params = [
                'index' => $this->getIndexName('apis'),
                'body' => $searchBody
            ];

            $response = $this->client->search($params);
            return $response['hits'];
        } catch (ClientResponseException $e) {
            Log::error('Elasticsearch search error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create index with mapping
     */
    public function createIndex($indexName)
    {
        try {
            $params = [
                'index' => $this->getIndexName($indexName),
                'body' => [
                    'mappings' => [
                        'properties' => [
                            'name' => ['type' => 'text', 'analyzer' => 'standard'],
                            'category' => ['type' => 'keyword'],
                            'features' => ['type' => 'text', 'analyzer' => 'standard'],
                            'pricing' => ['type' => 'keyword'],
                            'documentation_quality' => ['type' => 'float'],
                            'community_rating' => ['type' => 'float'],
                            'description' => ['type' => 'text', 'analyzer' => 'standard'],
                            'website_url' => ['type' => 'keyword'],
                            'documentation_url' => ['type' => 'keyword'],
                            'tags' => ['type' => 'text', 'analyzer' => 'standard'],
                            'is_active' => ['type' => 'boolean'],
                            'created_at' => ['type' => 'date'],
                            'updated_at' => ['type' => 'date']
                        ]
                    ]
                ]
            ];

            $response = $this->client->indices()->create($params);
            return $response;
        } catch (ClientResponseException $e) {
            Log::error('Elasticsearch index creation error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if index exists
     */
    public function indexExists($indexName)
    {
        try {
            $params = ['index' => $this->getIndexName($indexName)];
            return $this->client->indices()->exists($params);
        } catch (ClientResponseException $e) {
            return false;
        }
    }

    /**
     * Get index name with prefix
     */
    private function getIndexName($suffix)
    {
        return $this->indexPrefix . '_' . $suffix;
    }

    /**
     * Bulk index APIs
     */
    public function bulkIndexApis($apis)
    {
        try {
            $params = ['body' => []];

            foreach ($apis as $api) {
                $params['body'][] = [
                    'index' => [
                        '_index' => $this->getIndexName('apis'),
                        '_id' => $api['id']
                    ]
                ];
                $params['body'][] = $api;
            }

            $response = $this->client->bulk($params);
            return $response;
        } catch (ClientResponseException $e) {
            Log::error('Elasticsearch bulk indexing error: ' . $e->getMessage());
            throw $e;
        }
    }
}

