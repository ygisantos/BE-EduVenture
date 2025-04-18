<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function() {
    Route::post('login', [AuthController::class, 'Login']);
    Route::post('logout', [AuthController::class, 'Logout']);
    Route::get('get', [AuthController::class, 'getCurrentUser']);
});
