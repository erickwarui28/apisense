<?php

namespace App\Http\Controllers;

use App\Services\ElasticsearchService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicApiController extends Controller
{
    protected $elasticsearchService;
    protected $geminiService;

    public function __construct(ElasticsearchService $elasticsearchService, GeminiService $geminiService)
    {
        $this->elasticsearchService = $elasticsearchService;
        $this->geminiService = $geminiService;
    }

    /**
     * Analyze project description (public endpoint - no auth required)
     */
    public function analyzeDescription(Request $request)
    {
        // Set a longer execution time for this operation
        set_time_limit(180); // 3 minutes
        
        $request->validate([
            'description' => 'required|string|max:2000',
        ]);

        try {
            $description = $request->input('description');

            // Step 1: Analyze requirements using Gemini
            $requirements = $this->geminiService->analyzeRequirements($description);

            // Step 2: Search for relevant APIs using Elasticsearch
            // Don't apply any filters - let Elasticsearch find all matching APIs based on keywords
            // Gemini will filter and rank them based on all requirements including pricing
            $searchFilters = [];
            
            $searchResults = $this->elasticsearchService->searchApis(
                $description,
                $searchFilters, // No filters - broader search
                15 // Limit to prevent token overflow with Gemini
            );
            
            Log::info('Elasticsearch search results', [
                'query' => $description,
                'filters' => $searchFilters,
                'total_hits' => $searchResults['total']['value'] ?? 0,
                'returned_hits' => count($searchResults['hits'] ?? [])
            ]);

            // Step 3: Generate recommendations using Gemini
            $availableApis = $this->formatApisForGemini($searchResults['hits'] ?? []);
            
            Log::info('APIs being sent to Gemini', [
                'count' => count($availableApis),
                'api_names' => array_column($availableApis, 'name')
            ]);
            
            $recommendations = $this->geminiService->generateRecommendations($requirements, $availableApis);
            
            // Merge API URLs into recommendations
            $recommendations = $this->enrichRecommendationsWithUrls($recommendations, $availableApis);
            
            // If Gemini didn't recommend any, create basic recommendations from search results
            if (empty($recommendations['recommendations']) && count($availableApis) > 0) {
                Log::warning('Gemini returned no recommendations, using fallback');
                $recommendations['recommendations'] = array_map(function($api) {
                    return [
                        'api_id' => $api['id'],
                        'api_name' => $api['name'],
                        'match_score' => 70,
                        'reasoning' => $api['description'],
                        'pros' => ['Matches your search criteria'],
                        'cons' => ['Requires further evaluation'],
                        'integration_tips' => ['Check the official documentation for integration details'],
                        'website_url' => $api['website_url'] ?? '',
                        'documentation_url' => $api['documentation_url'] ?? '',
                    ];
                }, array_slice($availableApis, 0, 10));
                
                if (empty($recommendations['summary'])) {
                    $recommendations['summary'] = 'Found ' . count($availableApis) . ' potential APIs for your project. Review the documentation to determine the best fit.';
                }
            }

            // TODO: Save recommendations if user is logged in (SavedRecommendation model not created yet)
            // if (Auth::check()) {
            //     SavedRecommendation::create([...]);
            // }

            return response()->json([
                'success' => true,
                'requirements' => $requirements,
                'recommendations' => $recommendations,
            ]);

        } catch (\Exception $e) {
            Log::error('Public API analysis error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Provide more specific error messages
            $errorMessage = 'Failed to analyze your project. Please try again later.';
            if (strpos($e->getMessage(), 'timeout') !== false || strpos($e->getMessage(), 'timed out') !== false) {
                $errorMessage = 'The analysis is taking too long. Please try with a shorter description.';
            } elseif (strpos($e->getMessage(), 'token limit') !== false) {
                $errorMessage = 'Your description is too long. Please shorten it and try again.';
            }
            
            return response()->json([
                'success' => false,
                'error' => $errorMessage,
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Analyze uploaded file (public endpoint - no auth required)
     */
    public function analyzeFile(Request $request)
    {
        // Set a longer execution time for this operation
        set_time_limit(180); // 3 minutes
        
        $request->validate([
            'file' => 'required|file|mimes:txt,md|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            
            // Read file content
            $fileContent = file_get_contents($file->getRealPath());
            $originalSize = strlen($fileContent);
            
            // Limit file content to first 2000 characters to prevent timeouts
            // Focus on the beginning of README where project description usually is
            if ($originalSize > 2000) {
                $fileContent = substr($fileContent, 0, 2000);
                Log::info('File content truncated', ['original_size' => $originalSize, 'truncated_to' => 2000]);
            }
            
            // Process file with Gemini (with timeout fallback)
            try {
                $extractedRequirements = $this->geminiService->processFileContent($fileContent, $file->getClientOriginalName());
            } catch (\Exception $e) {
                // If Gemini times out or fails, create basic requirements from file content
                Log::warning('File processing with Gemini failed, using fallback', ['error' => $e->getMessage()]);
                $extractedRequirements = [
                    'project_type' => 'web application',
                    'required_categories' => [],
                    'specific_features' => [],
                    'technical_requirements' => [],
                    'priority_level' => 'medium',
                    'budget_consideration' => 'unknown',
                    'summary' => substr($fileContent, 0, 500), // Use first 500 chars as summary
                ];
            }
            
            // Search for relevant APIs
            $searchQuery = $extractedRequirements['summary'] ?? $fileContent;
            $searchFilters = [];
            
            // Don't apply any filters - let Gemini do all the filtering
            
            $searchResults = $this->elasticsearchService->searchApis(
                $searchQuery,
                $searchFilters, // No filters - broader search
                15 // Limit to prevent token overflow with Gemini
            );

            // Generate recommendations
            $availableApis = $this->formatApisForGemini($searchResults['hits'] ?? []);
            $recommendations = $this->geminiService->generateRecommendations($extractedRequirements, $availableApis);
            
            // Merge API URLs into recommendations
            $recommendations = $this->enrichRecommendationsWithUrls($recommendations, $availableApis);
            
            // If Gemini didn't recommend any, create basic recommendations from search results
            if (empty($recommendations['recommendations']) && count($availableApis) > 0) {
                Log::warning('Gemini returned no recommendations, using fallback');
                $recommendations['recommendations'] = array_map(function($api) {
                    return [
                        'api_id' => $api['id'],
                        'api_name' => $api['name'],
                        'match_score' => 70,
                        'reasoning' => $api['description'],
                        'pros' => ['Matches your search criteria'],
                        'cons' => ['Requires further evaluation'],
                        'integration_tips' => ['Check the official documentation for integration details'],
                        'website_url' => $api['website_url'] ?? '',
                        'documentation_url' => $api['documentation_url'] ?? '',
                    ];
                }, array_slice($availableApis, 0, 10));
                
                if (empty($recommendations['summary'])) {
                    $recommendations['summary'] = 'Found ' . count($availableApis) . ' potential APIs for your project. Review the documentation to determine the best fit.';
                }
            }

            // TODO: Save recommendations if user is logged in (SavedRecommendation model not created yet)
            // if (Auth::check()) {
            //     SavedRecommendation::create([...]);
            // }

            return response()->json([
                'success' => true,
                'extracted_requirements' => $extractedRequirements,
                'recommendations' => $recommendations,
            ]);

        } catch (\Exception $e) {
            Log::error('Public file analysis error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Provide more specific error messages
            $errorMessage = 'Failed to process your file. Please try again later.';
            if (strpos($e->getMessage(), 'timeout') !== false || strpos($e->getMessage(), 'timed out') !== false) {
                $errorMessage = 'The analysis is taking too long. Please try with a smaller file.';
            } elseif (strpos($e->getMessage(), 'token limit') !== false) {
                $errorMessage = 'Your file is too long. Please use a shorter file and try again.';
            }
            
            return response()->json([
                'success' => false,
                'error' => $errorMessage,
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Format API data for Gemini processing
     */
    private function formatApisForGemini($hits)
    {
        return array_map(function ($hit) {
            $source = $hit['_source'];
            return [
                'id' => $hit['_id'],
                'name' => $source['name'],
                'category' => $source['category'],
                'description' => $source['description'],
                'pricing' => $source['pricing'],
                // Keep URLs for later enrichment
                'website_url' => $source['website_url'] ?? '',
                'documentation_url' => $source['documentation_url'] ?? '',
                // Simplified: removed features, tags, ratings to reduce token usage
            ];
        }, $hits);
    }
    
    /**
     * Enrich Gemini recommendations with API URLs
     */
    private function enrichRecommendationsWithUrls($recommendations, $availableApis)
    {
        // Create a lookup map for APIs by ID and name
        $apiMap = [];
        foreach ($availableApis as $api) {
            $apiMap[$api['id']] = $api;
            $apiMap[$api['name']] = $api;
            
            // Also create fuzzy matches for common variations
            $name = strtolower($api['name']);
            $apiMap[$name] = $api;
            
            // Handle common abbreviations and variations
            if (strpos($name, 'tmdb') !== false || strpos($name, 'the movie database') !== false) {
                $apiMap['tmdb'] = $api;
                $apiMap['the movie database'] = $api;
                $apiMap['themoviedb'] = $api;
            }
        }
        
        // Add URLs to each recommendation
        if (isset($recommendations['recommendations']) && is_array($recommendations['recommendations'])) {
            $recommendations['recommendations'] = array_map(function($rec) use ($apiMap) {
                // Try to find the API by ID or name (exact match first)
                $apiData = null;
                if (isset($rec['api_id']) && isset($apiMap[$rec['api_id']])) {
                    $apiData = $apiMap[$rec['api_id']];
                } elseif (isset($rec['api_name']) && isset($apiMap[$rec['api_name']])) {
                    $apiData = $apiMap[$rec['api_name']];
                } elseif (isset($rec['api_name'])) {
                    // Try fuzzy matching
                    $recName = strtolower($rec['api_name']);
                    if (isset($apiMap[$recName])) {
                        $apiData = $apiMap[$recName];
                    } else {
                        // Try partial matching
                        foreach ($apiMap as $key => $api) {
                            if (is_string($key) && (
                                strpos($key, $recName) !== false || 
                                strpos($recName, $key) !== false
                            )) {
                                $apiData = $api;
                                break;
                            }
                        }
                    }
                }
                
                // Add URLs if API data found
                if ($apiData) {
                    $rec['website_url'] = $apiData['website_url'] ?? '';
                    $rec['documentation_url'] = $apiData['documentation_url'] ?? '';
                }
                
                return $rec;
            }, $recommendations['recommendations']);
        }
        
        return $recommendations;
    }
}