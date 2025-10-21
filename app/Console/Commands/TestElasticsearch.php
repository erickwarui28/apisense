<?php

namespace App\Console\Commands;

use App\Services\ElasticsearchService;
use Illuminate\Console\Command;

class TestElasticsearch extends Command
{
    protected $signature = 'test:elasticsearch {query?}';
    protected $description = 'Test Elasticsearch search';

    public function handle()
    {
        $elasticsearch = app(ElasticsearchService::class);
        $query = $this->argument('query') ?? 'hotel';

        $this->info("Searching for: {$query}");

        try {
            $result = $elasticsearch->searchApis($query, [], 10);
            
            $this->info('Total hits: ' . ($result['total']['value'] ?? 0));
            $this->info('Returned: ' . count($result['hits'] ?? []));
            
            foreach ($result['hits'] ?? [] as $hit) {
                $this->line($hit['_source']['name'] . ' - ' . $hit['_source']['category']);
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}

