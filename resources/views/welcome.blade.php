<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>APISense - AI-Powered API Discovery</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="nav-glass sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold gradient-text">APISense</h1>
                        <span class="ml-2 text-sm text-gray-500">AI-Powered API Discovery</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ route('dashboard') }}" class="soft-button px-6 py-2 text-white font-medium">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-gray-600 hover:text-purple-600 font-medium transition-colors">Login</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="soft-button px-6 py-2 text-white font-medium">Register</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl md:text-6xl mb-4">
                    Find the Perfect APIs for <span class="gradient-text">Your Project</span>
                </h1>
                <p class="mt-6 max-w-md mx-auto text-base text-gray-600 sm:text-lg md:mt-8 md:text-xl md:max-w-3xl leading-relaxed">
                    APISense uses AI to intelligently match developers with the right APIs for their specific use cases. 
                    Describe your project needs and get personalized recommendations.
                </p>
            </div>

            <!-- Interactive Demo Section -->
            <div class="max-w-4xl mx-auto" x-data="apiRecommendationApp()">
                <div class="soft-card p-8 md:p-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Try It Now</h2>
                    
                    <!-- Tab Selection -->
                    <div class="flex justify-center mb-8 p-1 bg-gray-100 rounded-2xl">
                        <button @click="activeTab = 'text'" 
                                :class="activeTab === 'text' ? 'bg-white shadow-md text-purple-600' : 'text-gray-600 hover:text-gray-800'"
                                class="flex-1 py-3 px-6 rounded-xl font-medium text-sm transition-all duration-300">
                            Describe Your Project
                        </button>
                        <button @click="activeTab = 'upload'" 
                                :class="activeTab === 'upload' ? 'bg-white shadow-md text-purple-600' : 'text-gray-600 hover:text-gray-800'"
                                class="flex-1 py-3 px-6 rounded-xl font-medium text-sm transition-all duration-300">
                            Upload README.md
                        </button>
                    </div>

                    <!-- Text Input Tab -->
                    <div x-show="activeTab === 'text'" class="space-y-6">
                        <div>
                            <label for="project-description" class="block text-sm font-semibold text-gray-700 mb-3">
                                What are you building?
                            </label>
                            <div class="relative">
                                <textarea 
                                    id="project-description"
                                    x-model="projectDescription"
                                    rows="5"
                                    class="soft-input w-full resize-none"
                                    :placeholder="currentPlaceholder"
                                ></textarea>
                            </div>
                        </div>
                        <button 
                            @click="analyzeProject()"
                            :disabled="isLoading || !projectDescription.trim()"
                            :class="(isLoading || !projectDescription.trim()) ? 'opacity-50 cursor-not-allowed' : ''"
                            class="soft-button w-full text-white font-semibold py-4 px-6">
                            <span x-show="!isLoading">✨ Get API Recommendations</span>
                            <span x-show="isLoading">
                                <span class="inline-block animate-pulse">Analyzing...</span>
                                <span class="text-xs ml-2" x-text="'(' + analysisTime + 's)'"></span>
                            </span>
                        </button>
                        <p x-show="isLoading" class="text-xs text-gray-500 mt-2 text-center">
                            This may take 30-60 seconds. Please wait...
                        </p>
                    </div>

                    <!-- File Upload Tab -->
                    <div x-show="activeTab === 'upload'" class="space-y-6">
                        <div>
                            <label for="file-upload" class="block text-sm font-semibold text-gray-700 mb-3">
                                Upload the Projects README.md file
                            </label>
                            <div 
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleFileDrop($event)"
                                :class="isDragging ? 'border-purple-400 bg-purple-50' : 'border-gray-200'"
                                class="mt-1 flex justify-center px-6 pt-8 pb-8 border-2 border-dashed rounded-2xl hover:border-purple-300 transition-all bg-gray-50"
                                style="transition: all 0.3s ease;">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                            <span>Upload a file</span>
                                            <input 
                                                id="file-upload" 
                                                type="file" 
                                                class="sr-only"
                                                @change="handleFileUpload($event)"
                                                accept=".txt,.md"
                                            >
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">README.md or .txt files (first 2000 characters will be analyzed)</p>
                                    <p x-show="selectedFile" class="text-sm font-medium text-blue-600 mt-2" x-text="'Selected: ' + (selectedFile ? selectedFile.name : '')"></p>
                                </div>
                            </div>
                        </div>
                        <button 
                            @click="analyzeFile()"
                            :disabled="isLoading || !selectedFile"
                            :class="(isLoading || !selectedFile) ? 'opacity-50 cursor-not-allowed' : ''"
                            class="soft-button w-full text-white font-semibold py-4 px-6">
                            <span x-show="!isLoading">✨ Analyze File</span>
                            <span x-show="isLoading">
                                <span class="inline-block animate-pulse">Processing...</span>
                                <span class="text-xs ml-2" x-text="'(' + analysisTime + 's)'"></span>
                            </span>
                        </button>
                        <p x-show="isLoading" class="text-xs text-gray-500 mt-2 text-center">
                            This may take 30-60 seconds. Please wait...
                        </p>
                    </div>

                    <!-- Error Message -->
                    <div x-show="errorMessage" class="mt-6 bg-red-50 border border-red-200 rounded-2xl p-4">
                        <p class="text-sm text-red-800" x-text="errorMessage"></p>
                    </div>

                    <!-- Results Section -->
                    <div x-show="recommendations.length > 0" class="mt-10">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">Recommended APIs for Your Project</h3>
                        
                        <!-- Requirements Summary -->
                        <div x-show="requirementsSummary" class="mb-6 bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-2xl p-5">
                            <h4 class="font-semibold text-purple-900 mb-2">📊 Project Analysis</h4>
                            <p class="text-sm text-purple-800" x-text="requirementsSummary"></p>
                        </div>

                        <!-- API Recommendations -->
                        <div class="space-y-5">
                            <template x-for="rec in recommendations" :key="rec.api_id">
                                <div class="recommendation-card p-5">
                                    <div class="flex justify-between items-start mb-3">
                                        <h4 class="text-lg font-bold text-gray-900" x-text="rec.api_name"></h4>
                                        <span class="bg-gradient-to-r from-purple-100 to-indigo-100 text-purple-800 text-xs font-semibold px-3 py-1.5 rounded-full" x-text="rec.match_score + '% Match'"></span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-3" x-text="rec.reasoning"></p>
                                    
                                    <!-- Pros and Cons -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                        <div x-show="rec.pros && rec.pros.length > 0">
                                            <h5 class="text-xs font-semibold text-green-700 mb-1">Pros:</h5>
                                            <ul class="text-xs text-gray-600 space-y-1">
                                                <template x-for="pro in rec.pros" :key="pro">
                                                    <li class="flex items-start">
                                                        <svg class="h-4 w-4 text-green-500 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span x-text="pro"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                        <div x-show="rec.cons && rec.cons.length > 0">
                                            <h5 class="text-xs font-semibold text-red-700 mb-1">Cons:</h5>
                                            <ul class="text-xs text-gray-600 space-y-1">
                                                <template x-for="con in rec.cons" :key="con">
                                                    <li class="flex items-start">
                                                        <svg class="h-4 w-4 text-red-500 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span x-text="con"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Integration Tips -->
                                    <div x-show="rec.integration_tips && rec.integration_tips.length > 0" class="bg-gradient-to-r from-gray-50 to-purple-50 rounded-xl p-4 mb-3">
                                        <h5 class="text-xs font-semibold text-gray-700 mb-2">💡 Integration Tips:</h5>
                                        <ul class="text-xs text-gray-600 space-y-1">
                                            <template x-for="tip in rec.integration_tips" :key="tip">
                                                <li class="flex items-start">
                                                    <span class="text-blue-500 mr-1">•</span>
                                                    <span x-text="tip"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                    
                                    <!-- API Links -->
                                    <div class="flex gap-3 mt-4">
                                        <a x-show="rec.website_url" 
                                           :href="rec.website_url" 
                                           target="_blank"
                                           class="inline-flex items-center px-4 py-2.5 text-xs font-semibold text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 rounded-xl transition-all shadow-md hover:shadow-lg">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                            Visit Website
                                        </a>
                                        <a x-show="rec.documentation_url && rec.documentation_url !== rec.website_url" 
                                           :href="rec.documentation_url" 
                                           target="_blank"
                                           class="inline-flex items-center px-4 py-2.5 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-xl transition-all">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Documentation
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Call to Action (only for non-logged-in users) -->
                        @guest
                        <div class="mt-8 text-center bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl p-6">
                            <p class="text-sm text-gray-700 mb-4 font-medium">Want to save these recommendations and explore more features?</p>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="soft-button inline-block text-white font-semibold py-3 px-8 text-sm">
                                    Create Free Account
                                </a>
                            @endif
                        </div>
                        @endguest

                        <!-- Save confirmation for logged-in users -->
                        @auth
                        <div class="mt-8 text-center bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-200">
                            <p class="text-sm text-green-700 font-semibold">✓ Your recommendations have been saved to your account!</p>
                            <a href="{{ route('dashboard') }}" class="mt-3 inline-block text-purple-600 hover:text-purple-800 font-semibold">
                                View Saved Recommendations →
                            </a>
                        </div>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <script>
            function apiRecommendationApp() {
                return {
                    activeTab: 'text',
                    projectDescription: '',
                    selectedFile: null,
                    isLoading: false,
                    errorMessage: '',
                    recommendations: [],
                    requirementsSummary: '',
                    analysisTime: 0,
                    analysisTimer: null,
                    isDragging: false,
                    currentPlaceholder: '',
                    placeholderIndex: 0,
                    charIndex: 0,
                    isDeleting: false,
                    typingSpeed: 50,
                    deletingSpeed: 30,
                    pauseBeforeDelete: 2000,
                    pauseBeforeType: 500,
                    placeholderExamples: [
                        "I'm building a travel booking app that needs weather forecasting, payment processing, and real-time notifications...",
                        "I need APIs for a fitness app with workout tracking, nutrition data, and user authentication...",
                        "Building an e-commerce platform requiring payment gateways, shipping tracking, and inventory management...",
                        "Creating a social media dashboard with analytics, content scheduling, and multi-platform posting...",
                        "Developing a smart home app that needs IoT device integration, weather data, and voice control...",
                        "I'm building a fintech app requiring cryptocurrency prices, stock market data, and currency conversion...",
                        "Need APIs for a food delivery app with restaurant listings, maps, GPS tracking, and payment processing...",
                        "Creating a music streaming service with song metadata, lyrics, and playlist recommendations..."
                    ],

                    init() {
                        this.startTypingAnimation();
                    },

                    startTypingAnimation() {
                        this.typeText();
                    },

                    typeText() {
                        const currentText = this.placeholderExamples[this.placeholderIndex];
                        
                        if (this.isDeleting) {
                            // Deleting characters
                            this.currentPlaceholder = currentText.substring(0, this.charIndex - 1);
                            this.charIndex--;
                            
                            if (this.charIndex === 0) {
                                this.isDeleting = false;
                                this.placeholderIndex = (this.placeholderIndex + 1) % this.placeholderExamples.length;
                                setTimeout(() => this.typeText(), this.pauseBeforeType);
                                return;
                            }
                            
                            setTimeout(() => this.typeText(), this.deletingSpeed);
                        } else {
                            // Typing characters
                            this.currentPlaceholder = currentText.substring(0, this.charIndex + 1);
                            this.charIndex++;
                            
                            if (this.charIndex === currentText.length) {
                                this.isDeleting = true;
                                setTimeout(() => this.typeText(), this.pauseBeforeDelete);
                                return;
                            }
                            
                            setTimeout(() => this.typeText(), this.typingSpeed);
                        }
                    },

                    async analyzeProject() {
                        if (!this.projectDescription.trim()) return;
                        
                        this.isLoading = true;
                        this.errorMessage = '';
                        this.recommendations = [];
                        this.requirementsSummary = '';
                        this.analysisTime = 0;
                        
                        // Start timer
                        this.analysisTimer = setInterval(() => {
                            this.analysisTime++;
                        }, 1000);

                        try {
                            console.log('Starting API analysis...');
                            const response = await fetch('/api/public/analyze', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    description: this.projectDescription
                                })
                            });

                            console.log('Response status:', response.status);

                            if (!response.ok) {
                                const errorText = await response.text();
                                console.error('Error response:', errorText);
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            let data;
                            try {
                                const responseText = await response.text();
                                console.log('Raw response:', responseText.substring(0, 500));
                                data = JSON.parse(responseText);
                            } catch (parseError) {
                                console.error('JSON parse error:', parseError);
                                throw new Error('Invalid response from server. Please check the server logs.');
                            }
                            console.log('Response data:', data);

                            if (data.success) {
                                this.recommendations = data.recommendations.recommendations || [];
                                this.requirementsSummary = data.requirements.summary || '';
                                console.log('Recommendations loaded:', this.recommendations.length);
                                
                                if (this.recommendations.length === 0) {
                                    this.errorMessage = 'No matching APIs found. Try a different description.';
                                }
                            } else {
                                this.errorMessage = data.error || 'Failed to analyze your project. Please try again.';
                            }
                        } catch (error) {
                            console.error('Full error:', error);
                            this.errorMessage = `An error occurred: ${error.message}. This may be due to a timeout or connection issue. Please try with a shorter description.`;
                        } finally {
                            // Clear timer
                            if (this.analysisTimer) {
                                clearInterval(this.analysisTimer);
                                this.analysisTimer = null;
                            }
                            this.isLoading = false;
                        }
                    },

                    handleFileUpload(event) {
                        this.selectedFile = event.target.files[0];
                        this.errorMessage = '';
                    },

                    handleFileDrop(event) {
                        this.isDragging = false;
                        const files = event.dataTransfer.files;
                        
                        if (files.length > 0) {
                            const file = files[0];
                            
                            // Check file type
                            if (file.name.endsWith('.md') || file.name.endsWith('.txt')) {
                                this.selectedFile = file;
                                this.errorMessage = '';
                                
                                // Update the file input element
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(file);
                                document.getElementById('file-upload').files = dataTransfer.files;
                            } else {
                                this.errorMessage = 'Please upload a .md or .txt file';
                            }
                        }
                    },

                    async analyzeFile() {
                        if (!this.selectedFile) return;

                        this.isLoading = true;
                        this.errorMessage = '';
                        this.recommendations = [];
                        this.requirementsSummary = '';
                        this.analysisTime = 0;
                        
                        // Start timer
                        this.analysisTimer = setInterval(() => {
                            this.analysisTime++;
                        }, 1000);

                        try {
                            console.log('Starting file analysis...');
                            const formData = new FormData();
                            formData.append('file', this.selectedFile);

                            const response = await fetch('/api/public/analyze-file', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: formData
                            });

                            console.log('Response status:', response.status);

                            if (!response.ok) {
                                const errorText = await response.text();
                                console.error('Error response:', errorText);
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            let data;
                            try {
                                const responseText = await response.text();
                                console.log('Raw response:', responseText.substring(0, 500));
                                data = JSON.parse(responseText);
                            } catch (parseError) {
                                console.error('JSON parse error:', parseError);
                                throw new Error('Invalid response from server. Please check the server logs.');
                            }
                            console.log('Response data:', data);

                            if (data.success) {
                                this.recommendations = data.recommendations.recommendations || [];
                                this.requirementsSummary = data.extracted_requirements.summary || '';
                                console.log('Recommendations loaded:', this.recommendations.length);
                                
                                if (this.recommendations.length === 0) {
                                    this.errorMessage = 'No matching APIs found. Try a different file.';
                                }
                            } else {
                                this.errorMessage = data.error || 'Failed to analyze your file. Please try again.';
                            }
                        } catch (error) {
                            console.error('Full error:', error);
                            this.errorMessage = `An error occurred: ${error.message}. This may be due to a timeout or connection issue. Please try with a smaller file.`;
                        } finally {
                            // Clear timer
                            if (this.analysisTimer) {
                                clearInterval(this.analysisTimer);
                                this.analysisTimer = null;
                            }
                            this.isLoading = false;
                        }
                    }
                }
            }
        </script>

        <!-- Features Section -->
        <div class="py-20 bg-gradient-to-b from-white to-purple-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <h2 class="text-base gradient-text font-bold tracking-wide uppercase">Features</h2>
                    <p class="mt-4 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        AI-Powered API Discovery
                    </p>
                    <p class="mt-6 max-w-2xl text-xl text-gray-600 lg:mx-auto leading-relaxed">
                        Discover, compare, and integrate APIs with the power of artificial intelligence.
                    </p>
                </div>

                <div class="mt-16">
                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="soft-card p-6 hover:shadow-2xl transition-all duration-300">
                            <div class="feature-icon flex items-center justify-center h-14 w-14 mb-4">
                                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <p class="text-lg leading-6 font-bold text-gray-900 mb-2">Natural Language Queries</p>
                            <p class="text-base text-gray-600 leading-relaxed">
                                Describe your project needs in plain English and get intelligent API recommendations.
                            </p>
                        </div>

                        <div class="soft-card p-6 hover:shadow-2xl transition-all duration-300">
                            <div class="feature-icon flex items-center justify-center h-14 w-14 mb-4">
                                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="text-lg leading-6 font-bold text-gray-900 mb-2">README.md File Analysis</p>
                            <p class="text-base text-gray-600 leading-relaxed">
                                Upload your project's README.md file for automatic requirement extraction from the first 2000 characters.
                            </p>
                        </div>

                        <div class="soft-card p-6 hover:shadow-2xl transition-all duration-300">
                            <div class="feature-icon flex items-center justify-center h-14 w-14 mb-4">
                                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <p class="text-lg leading-6 font-bold text-gray-900 mb-2">Smart Recommendations</p>
                            <p class="text-base text-gray-600 leading-relaxed">
                                Get AI-powered explanations of why each API fits your use case with pros, cons, and integration tips.
                            </p>
                        </div>

                        <div class="soft-card p-6 hover:shadow-2xl transition-all duration-300">
                            <div class="feature-icon flex items-center justify-center h-14 w-14 mb-4">
                                <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <p class="text-lg leading-6 font-bold text-gray-900 mb-2">Comprehensive Search</p>
                            <p class="text-base text-gray-600 leading-relaxed">
                                Browse and search through a comprehensive database of APIs with advanced filtering options.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-600/20 to-indigo-600/20"></div>
            <div class="relative max-w-2xl mx-auto text-center py-20 px-4 sm:py-24 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-extrabold text-white sm:text-4xl mb-6">
                    <span class="block">Ready to find your perfect APIs?</span>
                </h2>
                <p class="mt-6 text-lg leading-7 text-white/90">
                    Join developers who use APISense to discover and integrate the right APIs for their projects.
                </p>
            @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="mt-10 inline-flex items-center justify-center px-8 py-4 border-2 border-white text-base font-bold rounded-2xl text-white bg-white/10 backdrop-blur hover:bg-white/20 transition-all duration-300 sm:w-auto">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="mt-10 inline-flex items-center justify-center px-8 py-4 border-2 border-white text-base font-bold rounded-2xl text-purple-600 bg-white hover:bg-gray-50 transition-all duration-300 shadow-lg hover:shadow-xl sm:w-auto">
                            Get Started
                        </a>
                    @endauth
            @endif
            </div>
                </div>

        <!-- Footer -->
        <footer class="bg-gradient-to-b from-white to-gray-50">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-base text-gray-500">
                        &copy; 2025 APISense. Built with <span class="gradient-text font-semibold">Laravel, Elasticsearch, and Google Gemini AI</span>.
                    </p>
                </div>
            </div>
        </footer>
        </div>
    </body>
</html>