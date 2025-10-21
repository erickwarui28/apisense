<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\ElasticsearchService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiQueryController extends Controller
{
    protected $elasticsearchService;
    protected $geminiService;

    public function __construct(ElasticsearchService $elasticsearchService, GeminiService $geminiService)
    {
        $this->elasticsearchService = $elasticsearchService;
        $this->geminiService = $geminiService;
    }

    /**
     * Process natural language query and return API recommendations
     */
    public function processQuery(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000',
            'session_id' => 'nullable|string',
        ]);

        try {
            $user = Auth::user();
            $query = $request->input('query');
            $sessionId = $request->input('session_id', Str::uuid());

            // Step 1: Analyze requirements using Gemini
            $requirements = $this->geminiService->analyzeRequirements($query);

            // Step 2: Search for relevant APIs using Elasticsearch
            $searchFilters = [];
            if (isset($requirements['required_categories'])) {
                $searchFilters['category'] = $requirements['required_categories'][0] ?? null;
            }
            if (isset($requirements['budget_consideration'])) {
                $searchFilters['pricing'] = $requirements['budget_consideration'];
            }

            $searchResults = $this->elasticsearchService->searchApis(
                $query,
                array_filter($searchFilters),
                20
            );

            // Step 3: Generate recommendations using Gemini
            $availableApis = $this->formatApisForGemini($searchResults['hits'] ?? []);
            $recommendations = $this->geminiService->generateRecommendations($requirements, $availableApis);

            // Step 4: Save conversation
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'user_message' => $query,
                'ai_response' => json_encode($recommendations),
                'recommended_apis' => $availableApis,
                'session_id' => $sessionId,
            ]);

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'requirements' => $requirements,
                'recommendations' => $recommendations,
                'conversation_id' => $conversation->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process query: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get conversation history for a session
     */
    public function getConversationHistory(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $user = Auth::user();
        $sessionId = $request->input('session_id');

        $conversations = Conversation::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
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
                'features' => $source['features'],
                'pricing' => $source['pricing'],
                'documentation_quality' => $source['documentation_quality'],
                'community_rating' => $source['community_rating'],
                'description' => $source['description'],
                'tags' => $source['tags'],
            ];
        }, $hits);
    }
}