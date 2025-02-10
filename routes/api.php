<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{task}/update-status', [TaskController::class, 'updateStatus'])->name('api.tasks.update-status');
    Route::get('tasks/stats', [TaskController::class, 'getStats'])->name('api.tasks.stats');
});
