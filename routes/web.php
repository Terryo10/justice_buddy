<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LetterGenerationController;
use App\Http\Controllers\Api\DocumentController;

Route::get('/', function () {
    return view('welcome');
});

// Admin panel routes for letter management
Route::prefix('letter-requests')->name('letter-requests.')->group(function () {
    Route::get('/download/{requestId}', [LetterGenerationController::class, 'streamDocument'])->name('download');
    Route::get('/status/{requestId}', [LetterGenerationController::class, 'checkStatus'])->name('status');
});

// Admin panel routes for document management
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/download/{slug}', [DocumentController::class, 'download'])->name('download');
});
