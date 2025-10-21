<?php

namespace App\Http\Controllers;

use App\Models\ApiRepository;
use App\Services\ElasticsearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiRepositoryController extends Controller
{
    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->elasticsearchService = $elasticsearchService;
    }

    /**
     * Get all APIs with pagination and filtering
     */
    public function index(Request $request)
    {
        $query = ApiRepository::active();

        // Apply filters
        if ($request->has('category')) {
            $query->byCategory($request->input('category'));
        }

        if ($request->has('pricing')) {
            $query->byPricing($request->input('pricing'));
        }

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereJsonContains('features', $searchTerm)
                  ->orWhereJsonContains('tags', $searchTerm);
            });
        }

        $apis = $query->orderBy('community_rating', 'desc')
                     ->paginate(20);

        return response()->json([
            'success' => true,
            'apis' => $apis,
        ]);
    }

    /**
     * Get specific API details
     */
    public function show($id)
    {
        $api = ApiRepository::active()->findOrFail($id);

        return response()->json([
            'success' => true,
            'api' => $api,
        ]);
    }

    /**
     * Ingest new API into repository
     */
    public function ingest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'features' => 'required|array',
            'pricing' => 'required|string|in:free,freemium,paid',
            'documentation_quality' => 'nullable|numeric|between:0,10',
            'community_rating' => 'nullable|numeric|between:0,5',
            'description' => 'nullable|string',
            'website_url' => 'nullable|url',
            'documentation_url' => 'nullable|url',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create API in database
            $api = ApiRepository::create($request->all());

            // Index in Elasticsearch
            $apiData = $api->toArray();
            $this->elasticsearchService->indexApi($apiData);

            return response()->json([
                'success' => true,
                'api' => $api,
                'message' => 'API successfully ingested and indexed',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to ingest API: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update API
     */
    public function update(Request $request, $id)
    {
        $api = ApiRepository::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'features' => 'sometimes|array',
            'pricing' => 'sometimes|string|in:free,freemium,paid',
            'documentation_quality' => 'nullable|numeric|between:0,10',
            'community_rating' => 'nullable|numeric|between:0,5',
            'description' => 'nullable|string',
            'website_url' => 'nullable|url',
            'documentation_url' => 'nullable|url',
            'tags' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Update API in database
            $api->update($request->all());

            // Update in Elasticsearch
            $apiData = $api->toArray();
            $this->elasticsearchService->indexApi($apiData);

            return response()->json([
                'success' => true,
                'api' => $api,
                'message' => 'API successfully updated',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update API: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete API
     */
    public function destroy($id)
    {
        $api = ApiRepository::findOrFail($id);

        try {
            // Soft delete by setting is_active to false
            $api->update(['is_active' => false]);

            // Note: In a real application, you might want to delete from Elasticsearch too
            // For now, we'll keep it indexed but mark as inactive

            return response()->json([
                'success' => true,
                'message' => 'API successfully deactivated',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete API: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get API categories
     */
    public function getCategories()
    {
        $categories = ApiRepository::active()
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * Search APIs using Elasticsearch
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:500',
            'filters' => 'nullable|array',
            'size' => 'nullable|integer|min:1|max:50',
        ]);

        try {
            $query = $request->input('query');
            $filters = $request->input('filters', []);
            $size = $request->input('size', 10);

            $results = $this->elasticsearchService->searchApis($query, $filters, $size);

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}