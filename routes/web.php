<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LetterGenerationController;

Route::get('/', function () {
    return view('welcome');
});

// Admin panel routes for letter management
Route::prefix('letter-requests')->name('letter-requests.')->group(function () {
    Route::get('/download/{requestId}', [LetterGenerationController::class, 'streamDocument'])->name('download');
    Route::get('/status/{requestId}', [LetterGenerationController::class, 'checkStatus'])->name('status');
});
