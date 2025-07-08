<?php

// routes/api.php - Add these routes to your existing api.php file

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LawInfoItemController;
use App\Http\Controllers\Api\LawyerController;

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