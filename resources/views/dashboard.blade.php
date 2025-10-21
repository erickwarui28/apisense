<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Welcome Message and API Recommendations Link -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">{{ __("Welcome back!") }}</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Looking for the perfect APIs for your project? Try our AI-powered API recommendation tool.
                    </p>
                    <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Get API Recommendations
                    </a>
                </div>
            </div>

            <!-- Saved Recommendations -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ __('Your Saved Recommendations') }}
                    </h3>
                    
                    @php
                        $savedRecommendations = auth()->user()->savedRecommendations()->latest()->get();
                    @endphp

                    @if($savedRecommendations->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ __("You haven't saved any recommendations yet.") }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __("Try the API recommendation tool to get started!") }}
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($savedRecommendations as $saved)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow" x-data="{ expanded: false }">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                @if($saved->source_type === 'file')
                                                    <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">File Upload</span>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $saved->original_filename }}</span>
                                                @else
                                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Text Description</span>
                                                @endif
                                                <span class="text-xs text-gray-500">{{ $saved->created_at->diffForHumans() }}</span>
                                            </div>
                                            
                                            @if($saved->requirements_summary)
                                                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                    {{ Str::limit($saved->requirements_summary, 150) }}
                                                </p>
                                            @elseif($saved->project_description)
                                                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                    {{ Str::limit($saved->project_description, 150) }}
                                                </p>
                                            @endif
                                        </div>
                                        
                                        <button @click="expanded = !expanded" class="ml-4 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            <span x-show="!expanded">View Details</span>
                                            <span x-show="expanded">Hide Details</span>
                                        </button>
                                    </div>

                                    <!-- Expanded Details -->
                                    <div x-show="expanded" x-collapse class="mt-4">
                                        @if($saved->requirements_summary)
                                            <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                                <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Project Analysis</h4>
                                                <p class="text-sm text-blue-800 dark:text-blue-200">{{ $saved->requirements_summary }}</p>
                                            </div>
                                        @endif

                                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Recommended APIs:</h4>
                                        <div class="space-y-3">
                                            @foreach($saved->recommendations_data['recommendations'] ?? [] as $rec)
                                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-gray-50 dark:bg-gray-900">
                                                    <div class="flex justify-between items-start mb-2">
                                                        <h5 class="font-semibold text-gray-900 dark:text-gray-100">{{ $rec['api_name'] ?? 'N/A' }}</h5>
                                                        @if(isset($rec['match_score']))
                                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                                {{ $rec['match_score'] }}% Match
                                                            </span>
                                                        @endif
                                                    </div>
                                                    
                                                    @if(isset($rec['reasoning']))
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $rec['reasoning'] }}</p>
                                                    @endif

                                                    <!-- API Links -->
                                                    <div class="flex gap-2 mt-2">
                                                        @if(isset($rec['website_url']) && $rec['website_url'])
                                                            <a href="{{ $rec['website_url'] }}" target="_blank" 
                                                               class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition-colors">
                                                                Visit Website
                                                            </a>
                                                        @endif
                                                        @if(isset($rec['documentation_url']) && $rec['documentation_url'] && $rec['documentation_url'] !== ($rec['website_url'] ?? ''))
                                                            <a href="{{ $rec['documentation_url'] }}" target="_blank" 
                                                               class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 rounded transition-colors">
                                                                Documentation
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
