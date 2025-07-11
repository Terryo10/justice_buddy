<?php

// routes/api.php - Add these routes to your existing api.php file

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LawInfoItemController;
use App\Http\Controllers\Api\LawyerController;
use App\Http\Controllers\Api\LetterGenerationController;
use App\Http\Controllers\Api\ChatController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Categories API routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{slug}', [CategoryController::class, 'show']);
});

// Law Info Items API routes
Route::prefix('law-info-items')->group(function () {
    Route::get('/', [LawInfoItemController::class, 'index']);
    Route::get('/{slug}', [LawInfoItemController::class, 'show']);
    Route::get('/{slug}/related', [LawInfoItemController::class, 'related']);
});

// Lawyers API routes
Route::prefix('lawyers')->group(function () {
    Route::get('/', [LawyerController::class, 'index']);
    Route::get('/featured', [LawyerController::class, 'featured']);
    Route::get('/specializations', [LawyerController::class, 'specializations']);
    Route::get('/locations', [LawyerController::class, 'locations']);
    Route::get('/specialization/{specialization}', [LawyerController::class, 'bySpecialization']);
    Route::post('/near', [LawyerController::class, 'nearLocation']);
    Route::get('/{slug}', [LawyerController::class, 'show']);
});


// Letter Generation API routes
Route::prefix('letter-generation')->name('letter-generation.')->group(function () {
    Route::get('/templates', [LetterGenerationController::class, 'getTemplates']);
    Route::get('/templates/{templateId}', [LetterGenerationController::class, 'getTemplate']);
    Route::post('/generate', [LetterGenerationController::class, 'generateLetter']);
    Route::get('/categories', [LetterGenerationController::class, 'getCategories']);
});

// Letter Request Status and Management
Route::prefix('letter-requests')->name('api.letter-requests.')->group(function () {
    Route::get('/status/{requestId}', [LetterGenerationController::class, 'checkStatus'])->name('status');
    Route::get('/download/{requestId}', [LetterGenerationController::class, 'downloadDocument'])->name('download');
    Route::get('/history', [LetterGenerationController::class, 'getHistory']);
    Route::get('/history/device', [LetterGenerationController::class, 'getHistoryByDevice']);
});

// Chat API routes
Route::prefix('chat')->name('api.chat.')->group(function () {
    Route::get('/rules', [ChatController::class, 'getRules'])->name('rules');
    Route::post('/message', [ChatController::class, 'chat'])->name('message');
});

// API Documentation routes (for testing)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Justice Buddy API is running',
        'timestamp' => now(),
        'endpoints' => [
            'categories' => '/api/categories',
            'law-info-items' => '/api/law-info-items',
            'lawyers' => '/api/lawyers',
        ]
    ]);
});
