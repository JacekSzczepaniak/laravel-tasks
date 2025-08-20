<?php

use App\Http\Controllers\API\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])
    ->prefix('v1')
    ->group(function () {
        Route::get('/tasks', [TaskController::class, 'index']);
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::get('/tasks/{task}', [TaskController::class, 'show']);
        Route::put('/tasks/{task}', [TaskController::class, 'update']);
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
        Route::post('/tasks/{task}/observers/{user}', [TaskController::class, 'attachObserver']);
        Route::delete('/tasks/{task}/observers/{user}', [TaskController::class, 'detachObserver']);
    });
