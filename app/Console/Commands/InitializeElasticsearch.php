<?php

namespace App\Console\Commands;

use App\Models\ApiRepository;
use App\Services\ElasticsearchService;
use Illuminate\Console\Command;

class InitializeElasticsearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Elasticsearch index and populate with API data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $elasticsearch = app(ElasticsearchService::class);

        $this->info('Initializing Elasticsearch...');

        try {
            // Create index if it doesn't exist
            if (!$elasticsearch->indexExists('apis')) {
                $this->info('Creating APIs index...');
                $elasticsearch->createIndex('apis');
                $this->info('Index created successfully!');
            } else {
                $this->info('Index already exists!');
            }

            // Get all APIs from database
            $apis = ApiRepository::all()->toArray();

            if (empty($apis)) {
                $this->warn('No APIs found in database. Please run the seeder first.');
                return;
            }

            $this->info('Indexing ' . count($apis) . ' APIs...');

            // Bulk index APIs
            $elasticsearch->bulkIndexApis($apis);

            $this->info('Elasticsearch initialization completed successfully!');
            $this->info('Indexed ' . count($apis) . ' APIs.');

        } catch (\Exception $e) {
            $this->error('Error initializing Elasticsearch: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}