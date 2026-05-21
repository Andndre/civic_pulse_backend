<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\LearningMaterialController;
use App\Http\Controllers\Api\ScoreController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::middleware('throttle:auth')->prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // User info (legacy, keep for compatibility if needed)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Auth routes requiring authentication
    Route::middleware('throttle:auth')->prefix('v1/auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'user']);
    });

    // API version 1 protected endpoints
    Route::prefix('v1')->group(function () {
        // User profile
        Route::get('/users/me', [UserController::class, 'me']);

        // Students CRUD, Restore
        Route::post('/students/{student}/restore', [StudentController::class, 'restore']);
        Route::apiResource('students', StudentController::class);

        // Teachers CRUD
        Route::apiResource('teachers', TeacherController::class);

        // Classes CRUD
        Route::apiResource('classes', ClassController::class);

        // Activities CRUD & Status Update
        Route::patch('/activities/{activity}/status', [ActivityController::class, 'updateStatus'])->middleware('role:teacher');
        Route::apiResource('activities', ActivityController::class);

        // Scores CRUD
        Route::apiResource('scores', ScoreController::class);

        // Materials CRUD
        Route::apiResource('materials', LearningMaterialController::class);
    });
});
