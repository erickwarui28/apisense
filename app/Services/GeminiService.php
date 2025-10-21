<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $model;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->model = config('gemini.model', 'gemini-1.5-pro');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent';
    }

    /**
     * Analyze project requirements and extract API needs
     */
    public function analyzeRequirements($description, $context = '')
    {
        try {
            $prompt = $this->buildRequirementsPrompt($description, $context);
            $response = $this->makeRequest($prompt);
            
            return $this->parseRequirementsResponse($response);
        } catch (\Exception $e) {
            Log::error('Gemini requirements analysis error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate API recommendations with explanations
     */
    public function generateRecommendations($requirements, $availableApis)
    {
        try {
            Log::info('Generating recommendations with Gemini', [
                'requirements_project_type' => $requirements['project_type'] ?? 'unknown',
                'available_apis_count' => count($availableApis)
            ]);
            
            $prompt = $this->buildRecommendationsPrompt($requirements, $availableApis);
            $response = $this->makeRequest($prompt);
            
            $parsed = $this->parseRecommendationsResponse($response);
            
            Log::info('Gemini recommendations result', [
                'recommendations_count' => isset($parsed['recommendations']) ? count($parsed['recommendations']) : 0,
                'has_summary' => isset($parsed['summary'])
            ]);
            
            return $parsed;
        } catch (\Exception $e) {
            Log::error('Gemini recommendations error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process uploaded file content for requirements extraction
     */
    public function processFileContent($fileContent, $filename)
    {
        try {
            $prompt = $this->buildFileProcessingPrompt($fileContent, $filename);
            $response = $this->makeRequest($prompt);
            
            return $this->parseFileProcessingResponse($response);
        } catch (\Exception $e) {
            Log::error('Gemini file processing error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Make HTTP request to Gemini API
     */
    private function makeRequest($prompt)
    {
        $response = Http::timeout(120)->withOptions([
            'verify' => false, // Disable SSL verification for Windows environments
        ])->withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '?key=' . $this->apiKey, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 8192,
                'responseMimeType' => 'application/json',
            ]
        ]);

        if (!$response->successful()) {
            Log::error('Gemini API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Gemini API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Build prompt for requirements analysis
     */
    private function buildRequirementsPrompt($description, $context)
    {
        return "You are an expert API consultant. Analyze the following project description and extract specific API requirements.

Project Description: {$description}

Additional Context: {$context}

Please provide a JSON response with the following structure:
{
    \"project_type\": \"string (e.g., web app, mobile app, e-commerce, etc.)\",
    \"required_categories\": [\"array of API categories needed\"],
    \"specific_features\": [\"array of specific features/functionality needed\"],
    \"technical_requirements\": [\"array of technical requirements\"],
    \"priority_level\": \"high/medium/low\",
    \"budget_consideration\": \"free/freemium/paid\",
    \"summary\": \"brief summary of what APIs are needed\"
}

Focus on identifying APIs for: authentication, payments, data storage, external services, analytics, notifications, etc.";
    }

    /**
     * Build prompt for API recommendations
     */
    private function buildRecommendationsPrompt($requirements, $availableApis)
    {
        $apisJson = json_encode($availableApis, JSON_PRETTY_PRINT);
        
        return "You are an expert API consultant. Based on the project requirements, recommend the best APIs from the available options.

Requirements: " . json_encode($requirements, JSON_PRETTY_PRINT) . "

Available APIs: {$apisJson}

IMPORTANT: Use the EXACT api_id and api_name values from the Available APIs list above. Do not modify or abbreviate them.

Please provide a JSON response with the following structure:
{
    \"recommendations\": [
        {
            \"api_id\": \"string (use exact ID from available APIs)\",
            \"api_name\": \"string (use exact name from available APIs)\",
            \"match_score\": \"number (0-100)\",
            \"reasoning\": \"why this API is recommended\",
            \"pros\": [\"array of advantages\"],
            \"cons\": [\"array of disadvantages\"],
            \"integration_tips\": [\"array of integration advice\"]
        }
    ],
    \"summary\": \"overall recommendation summary\",
    \"alternatives\": [\"array of alternative approaches\"]
}

Rank the recommendations by match_score (highest first).";
    }

    /**
     * Build prompt for file processing
     */
    private function buildFileProcessingPrompt($fileContent, $filename)
    {
        return "Analyze this README and extract what APIs are needed. Be concise.

{$fileContent}

Respond in JSON format:
{
    \"project_type\": \"brief type\",
    \"summary\": \"one sentence: what APIs this project needs\",
    \"budget_consideration\": \"free/freemium/paid/unknown\"
}

Keep response short and focused.";
    }

    /**
     * Parse requirements analysis response
     */
    private function parseRequirementsResponse($response)
    {
        // Check for token limit issues
        $finishReason = $response['candidates'][0]['finishReason'] ?? '';
        if ($finishReason === 'MAX_TOKENS') {
            Log::error('Gemini API hit token limit', ['response' => $response]);
            throw new \Exception('Gemini API response was truncated due to token limit. Please try with a shorter prompt.');
        }
        
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        if (empty($text)) {
            Log::error('Empty response from Gemini API', ['response' => $response]);
            throw new \Exception('Empty response from Gemini API');
        }
        
        // Remove markdown code blocks if present
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        
        // Extract JSON from response
        $jsonStart = strpos($text, '{');
        $jsonEnd = strrpos($text, '}') + 1;
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($text, $jsonStart, $jsonEnd - $jsonStart);
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                return $decoded;
            }
            
            Log::error('JSON decode error', [
                'error' => json_last_error_msg(),
                'text' => $text
            ]);
        }
        
        Log::error('Invalid response format from Gemini API', ['text' => $text]);
        throw new \Exception('Invalid response format from Gemini API');
    }

    /**
     * Parse recommendations response
     */
    private function parseRecommendationsResponse($response)
    {
        // Check for token limit issues
        $finishReason = $response['candidates'][0]['finishReason'] ?? '';
        if ($finishReason === 'MAX_TOKENS') {
            Log::error('Gemini API hit token limit', ['response' => $response]);
            throw new \Exception('Gemini API response was truncated due to token limit. Please try with a shorter prompt.');
        }
        
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        if (empty($text)) {
            Log::error('Empty response from Gemini API', ['response' => $response]);
            throw new \Exception('Empty response from Gemini API');
        }
        
        // Remove markdown code blocks if present
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        
        // Extract JSON from response
        $jsonStart = strpos($text, '{');
        $jsonEnd = strrpos($text, '}') + 1;
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($text, $jsonStart, $jsonEnd - $jsonStart);
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                return $decoded;
            }
            
            Log::error('JSON decode error', [
                'error' => json_last_error_msg(),
                'text' => $text
            ]);
        }
        
        Log::error('Invalid response format from Gemini API', ['text' => $text]);
        throw new \Exception('Invalid response format from Gemini API');
    }

    /**
     * Parse file processing response
     */
    private function parseFileProcessingResponse($response)
    {
        // Check for token limit issues
        $finishReason = $response['candidates'][0]['finishReason'] ?? '';
        if ($finishReason === 'MAX_TOKENS') {
            Log::error('Gemini API hit token limit', ['response' => $response]);
            throw new \Exception('Gemini API response was truncated due to token limit. Please try with a shorter prompt.');
        }
        
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        if (empty($text)) {
            Log::error('Empty response from Gemini API', ['response' => $response]);
            throw new \Exception('Empty response from Gemini API');
        }
        
        // Remove markdown code blocks if present
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        
        // Extract JSON from response
        $jsonStart = strpos($text, '{');
        $jsonEnd = strrpos($text, '}') + 1;
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($text, $jsonStart, $jsonEnd - $jsonStart);
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                // Add default fields if missing (for simplified response)
                $decoded['required_categories'] = $decoded['required_categories'] ?? [];
                $decoded['specific_features'] = $decoded['specific_features'] ?? [];
                $decoded['technical_requirements'] = $decoded['technical_requirements'] ?? [];
                $decoded['priority_level'] = $decoded['priority_level'] ?? 'medium';
                
                return $decoded;
            }
            
            Log::error('JSON decode error', [
                'error' => json_last_error_msg(),
                'text' => $text
            ]);
        }
        
        Log::error('Invalid response format from Gemini API', ['text' => $text]);
        throw new \Exception('Invalid response format from Gemini API');
    }
}

