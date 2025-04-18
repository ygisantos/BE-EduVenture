<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\ActivityLogsController;
use App\Http\Controllers\API\MinigameController;
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

    // Activity Logs Routes
    Route::prefix('activity-logs')->group(function () {
        Route::post('/create', [ActivityLogsController::class, 'createLog']);
        Route::get('/get', [ActivityLogsController::class, 'getLogs']);
    });

    // Minigame Routes
    Route::prefix('minigames')->group(function () {
        Route::get('/get', [MinigameController::class, 'index']);
        Route::post('/create', [MinigameController::class, 'store']);
        Route::get('/get/{id}', [MinigameController::class, 'show']);
        Route::put('/update/{id}', [MinigameController::class, 'update']);
        Route::delete('/delete/{id}', [MinigameController::class, 'destroy']);

        // Minigame Content Routes
        Route::post('/contents/create/{minigameId}', [MinigameController::class, 'storeContent']);
        Route::put('/contents/update', [MinigameController::class, 'updateContent']);
        Route::delete('/contents/delete/{contentId}', [MinigameController::class, 'destroyContent']);
        Route::get('/contents/{minigameId}', [MinigameController::class, 'getContents']);

        // Minigame History Routes
        Route::post('/history/create', [MinigameController::class, 'storeHistory']);
        Route::get('/history/{minigameId}/{studentId?}', [MinigameController::class, 'getHistory']);
    });
});

// EXAMPLES

// minigame content create
/*
{
  "contents": [
    {
      "question": "What is 5 + 7?",
      "correct_answer": 2,
      "option_1": "10",
      "option_2": "12",
      "option_3": "13",
      "option_4": "15",
      "points": 100,
      "timer": 30
    },
    {
      "question": "Which planet is known as the Red Planet?",
      "correct_answer": 3,
      "option_1": "Earth",
      "option_2": "Venus",
      "option_3": "Mars",
      "option_4": "Jupiter",
      "points": 100,
      "timer": 30
    }
  ]
}


*/
