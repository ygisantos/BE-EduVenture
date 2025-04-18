<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function() {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('get', [AuthController::class, 'getCurrentUser']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Book Routes
    Route::prefix('books')->group(function () {
        Route::get('/get', [BookController::class, 'index']);
        Route::post('/create', [BookController::class, 'store']);
        Route::get('/get/{id}', [BookController::class, 'show']);
        Route::put('/update/{id}', [BookController::class, 'update']);
        Route::delete('/delete/{id}', [BookController::class, 'destroy']);

        // Book Content Routes
        Route::post('/contents/create/{bookId}', [BookController::class, 'storeContent']);
        Route::put('/contents/update', [BookController::class, 'updateContent']);
        Route::delete('/contents/delete/{contentId}', [BookController::class, 'destroyContent']);
    });
});
