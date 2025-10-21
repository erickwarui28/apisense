<?php

namespace App\Http\Controllers;

use App\Models\ProjectUpload;
use App\Services\ElasticsearchService;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    protected $elasticsearchService;
    protected $geminiService;

    public function __construct(ElasticsearchService $elasticsearchService, GeminiService $geminiService)
    {
        $this->elasticsearchService = $elasticsearchService;
        $this->geminiService = $geminiService;
    }

    /**
     * Handle file upload and process for API recommendations
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,md|max:10240', // 10MB max
        ]);

        try {
            $user = Auth::user();
            $file = $request->file('file');
            
            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('uploads', $filename, 'local');
            
            // Read file content
            $fileContent = Storage::get($filePath);
            
            // Process file with Gemini
            $extractedRequirements = $this->geminiService->processFileContent($fileContent, $file->getClientOriginalName());
            
            // Search for relevant APIs
            $searchQuery = $extractedRequirements['summary'] ?? '';
            $searchFilters = [];
            
            if (isset($extractedRequirements['required_categories'])) {
                $searchFilters['category'] = $extractedRequirements['required_categories'][0] ?? null;
            }
            if (isset($extractedRequirements['budget_consideration'])) {
                $searchFilters['pricing'] = $extractedRequirements['budget_consideration'];
            }

            $searchResults = $this->elasticsearchService->searchApis(
                $searchQuery,
                array_filter($searchFilters),
                20
            );

            // Generate recommendations
            $availableApis = $this->formatApisForGemini($searchResults['hits'] ?? []);
            $recommendations = $this->geminiService->generateRecommendations($extractedRequirements, $availableApis);

            // Save upload record
            $upload = ProjectUpload::create([
                'user_id' => $user->id,
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'extracted_requirements' => json_encode($extractedRequirements),
                'recommended_apis' => $availableApis,
            ]);

            return response()->json([
                'success' => true,
                'upload_id' => $upload->id,
                'extracted_requirements' => $extractedRequirements,
                'recommendations' => $recommendations,
                'file_info' => [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's uploaded files
     */
    public function getUserUploads()
    {
        $user = Auth::user();
        
        $uploads = ProjectUpload::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'uploads' => $uploads,
        ]);
    }

    /**
     * Get specific upload details
     */
    public function getUploadDetails($id)
    {
        $user = Auth::user();
        
        $upload = ProjectUpload::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$upload) {
            return response()->json([
                'success' => false,
                'error' => 'Upload not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'upload' => $upload,
            'extracted_requirements' => json_decode($upload->extracted_requirements, true),
            'recommended_apis' => $upload->recommended_apis,
        ]);
    }

    /**
     * Delete uploaded file
     */
    public function deleteUpload($id)
    {
        $user = Auth::user();
        
        $upload = ProjectUpload::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$upload) {
            return response()->json([
                'success' => false,
                'error' => 'Upload not found',
            ], 404);
        }

        // Delete file from storage
        Storage::delete($upload->file_path);
        
        // Delete database record
        $upload->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
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