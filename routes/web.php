<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicApiController;
use App\Http\Controllers\ApiQueryController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\ApiRepositoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public API endpoints (no authentication required)
Route::get('/api/public/health', function() {
    return response()->json(['status' => 'ok', 'message' => 'API is working']);
});
Route::post('/api/public/analyze', [PublicApiController::class, 'analyzeDescription'])->name('api.public.analyze');
Route::post('/api/public/analyze-file', [PublicApiController::class, 'analyzeFile'])->name('api.public.analyze-file');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // API Query routes
    Route::post('/api/query', [ApiQueryController::class, 'processQuery'])->name('api.query');
    Route::get('/api/conversation-history', [ApiQueryController::class, 'getConversationHistory'])->name('api.conversation-history');
    
    // File upload routes
    Route::post('/api/upload', [FileUploadController::class, 'upload'])->name('api.upload');
    Route::get('/api/uploads', [FileUploadController::class, 'getUserUploads'])->name('api.uploads');
    Route::get('/api/uploads/{id}', [FileUploadController::class, 'getUploadDetails'])->name('api.upload-details');
    Route::delete('/api/uploads/{id}', [FileUploadController::class, 'deleteUpload'])->name('api.upload-delete');
    
    // API Repository routes
    Route::get('/api/repository', [ApiRepositoryController::class, 'index'])->name('api.repository.index');
    Route::get('/api/repository/{id}', [ApiRepositoryController::class, 'show'])->name('api.repository.show');
    Route::post('/api/repository/ingest', [ApiRepositoryController::class, 'ingest'])->name('api.repository.ingest');
    Route::put('/api/repository/{id}', [ApiRepositoryController::class, 'update'])->name('api.repository.update');
    Route::delete('/api/repository/{id}', [ApiRepositoryController::class, 'destroy'])->name('api.repository.destroy');
    Route::get('/api/repository/categories', [ApiRepositoryController::class, 'getCategories'])->name('api.repository.categories');
    Route::post('/api/repository/search', [ApiRepositoryController::class, 'search'])->name('api.repository.search');
});

require __DIR__.'/auth.php';
