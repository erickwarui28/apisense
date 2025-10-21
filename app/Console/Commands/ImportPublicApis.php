<?php

namespace App\Console\Commands;

use App\Models\ApiRepository;
use App\Services\ElasticsearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportPublicApis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apis:import-public';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import public APIs from public_apis.json file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $elasticsearch = app(ElasticsearchService::class);

        $this->info('Importing Public APIs from JSON file...');

        // Get the JSON file path
        $jsonPath = base_path('../public_apis.json');

        if (!File::exists($jsonPath)) {
            $this->error('public_apis.json file not found at: ' . $jsonPath);
            return 1;
        }

        // Read and parse JSON file
        $jsonContent = File::get($jsonPath);
        $apis = json_decode($jsonContent, true);

        if (!$apis || !is_array($apis)) {
            $this->error('Invalid JSON format in public_apis.json');
            return 1;
        }

        $this->info('Found ' . count($apis) . ' APIs in the JSON file.');

        // Clear existing data
        $this->info('Clearing existing API data...');
        ApiRepository::truncate();

        // Create index if it doesn't exist
        if (!$elasticsearch->indexExists('apis')) {
            $this->info('Creating APIs index...');
            $elasticsearch->createIndex('apis');
            $this->info('Index created successfully!');
        }

        // Process and import APIs
        $this->info('Importing APIs...');
        $bar = $this->output->createProgressBar(count($apis));
        $bar->start();

        $batch = [];
        $batchSize = 100;
        $imported = 0;

        foreach ($apis as $api) {
            // Map the JSON structure to our database structure
            $apiData = [
                'name' => $api['name'] ?? 'Unknown',
                'category' => $this->extractCategory($api['name'], $api['description'] ?? ''),
                'features' => $this->extractFeatures($api['description'] ?? ''),
                'pricing' => 'unknown',
                'documentation_quality' => 7.0,
                'community_rating' => 4.0,
                'description' => $api['description'] ?? '',
                'website_url' => $api['url'] ?? '',
                'documentation_url' => $api['url'] ?? '',
                'tags' => $this->extractTags($api['name'], $api['description'] ?? ''),
            ];

            // Save to database
            $apiModel = ApiRepository::create($apiData);
            $apiData['id'] = $apiModel->id;

            // Add to batch for Elasticsearch
            $batch[] = $apiData;

            if (count($batch) >= $batchSize) {
                $elasticsearch->bulkIndexApis($batch);
                $batch = [];
            }

            $imported++;
            $bar->advance();
        }

        // Index remaining APIs
        if (!empty($batch)) {
            $elasticsearch->bulkIndexApis($batch);
        }

        $bar->finish();
        $this->newLine();

        $this->info('Successfully imported ' . $imported . ' APIs to database and Elasticsearch!');

        return 0;
    }

    /**
     * Extract category from API name and description
     */
    private function extractCategory($name, $description)
    {
        $text = strtolower($name . ' ' . $description);

        $categories = [
            'travel' => ['hotel', 'booking', 'travel', 'flight', 'vacation', 'tourism', 'accommodation', 'rental'],
            'weather' => ['weather', 'climate', 'forecast', 'meteorolog'],
            'finance' => ['stock', 'finance', 'trading', 'crypto', 'currency', 'exchange', 'payment', 'bank'],
            'maps' => ['map', 'location', 'geocod', 'navigation', 'address'],
            'social' => ['social', 'twitter', 'facebook', 'instagram', 'linkedin'],
            'news' => ['news', 'article', 'headline', 'journal'],
            'sports' => ['sport', 'football', 'soccer', 'basketball', 'nba', 'nfl'],
            'gaming' => ['game', 'gaming', 'player', 'steam', 'xbox', 'playstation'],
            'music' => ['music', 'song', 'audio', 'spotify', 'sound', 'lyrics'],
            'video' => ['video', 'youtube', 'movie', 'film', 'tv', 'streaming'],
            'food' => ['food', 'recipe', 'restaurant', 'drink', 'nutrition'],
            'transportation' => ['transport', 'transit', 'train', 'bus', 'airport'],
            'health' => ['health', 'medical', 'covid', 'disease', 'symptom'],
            'education' => ['education', 'university', 'learn', 'course'],
            'government' => ['government', 'open data', 'census', 'official'],
            'security' => ['security', 'cybersecurity', 'vulnerability', 'malware'],
            'development' => ['api', 'development', 'developer', 'code', 'github'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return 'general';
    }

    /**
     * Extract features from description
     */
    private function extractFeatures($description)
    {
        $features = [];
        
        if (stripos($description, 'real-time') !== false || stripos($description, 'realtime') !== false) {
            $features[] = 'Real-time data';
        }
        if (stripos($description, 'historical') !== false) {
            $features[] = 'Historical data';
        }
        if (stripos($description, 'search') !== false) {
            $features[] = 'Search functionality';
        }
        if (stripos($description, 'free') !== false) {
            $features[] = 'Free tier available';
        }

        if (empty($features)) {
            $features[] = 'API access';
        }

        return $features;
    }

    /**
     * Extract tags from name and description
     */
    private function extractTags($name, $description)
    {
        $text = strtolower($name . ' ' . $description);
        $tags = [];

        // Common tag keywords
        $tagKeywords = [
            'weather', 'finance', 'map', 'social', 'news', 'sports', 'game',
            'music', 'video', 'food', 'transport', 'health', 'education',
            'government', 'security', 'data', 'api', 'free', 'real-time'
        ];

        foreach ($tagKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $tags[] = $keyword;
            }
        }

        if (empty($tags)) {
            $tags[] = 'api';
        }

        return $tags;
    }
}

