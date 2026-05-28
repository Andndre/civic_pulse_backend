<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AnecdotalNoteController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\LearningMaterialController;
use App\Http\Controllers\Api\PulseInstrumentController;
use App\Http\Controllers\Api\QuestionController;
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
        Route::patch('/users/me', [UserController::class, 'update']);

        // Students CRUD, Restore
        Route::get('/dashboard/analytics', [StudentController::class, 'getAnalytics']);
        Route::post('/students/{student}/restore', [StudentController::class, 'restore']);
        Route::apiResource('students.anecdotal-notes', AnecdotalNoteController::class)->middleware('role:teacher,admin');
        Route::apiResource('students', StudentController::class);

        // Teachers CRUD
        Route::apiResource('teachers', TeacherController::class);

        // Classes: Join & Leave
        Route::post('/classes/join', [ClassController::class, 'join'])->middleware('role:student');
        Route::delete('/classes/{class}/leave', [ClassController::class, 'leave'])->middleware('role:student');

        // Classes CRUD
        Route::apiResource('classes', ClassController::class);

        // Activities CRUD & Status Update
        Route::patch('/activities/{activity}/status', [ActivityController::class, 'updateStatus'])->middleware('role:teacher');
        Route::apiResource('activities', ActivityController::class);

        // Scores CRUD
        Route::apiResource('scores', ScoreController::class);

        // Materials CRUD
        Route::apiResource('materials', LearningMaterialController::class)->only(['index', 'show']);
        Route::apiResource('materials', LearningMaterialController::class)->only(['store', 'update', 'destroy'])->middleware('role:teacher,admin');

        // Student learning path endpoints
        Route::prefix('materials/{material}')->group(function () {
            Route::get('/questions', [LearningMaterialController::class, 'getQuestions']);
            Route::post('/test-response', [LearningMaterialController::class, 'submitTestResponse']);
            Route::get('/pulse-statements', [LearningMaterialController::class, 'getPulseStatements']);
            Route::post('/pulse-response', [LearningMaterialController::class, 'submitPulseResponse']);
        });

        // Quiz Builder & Pulse Instrument Builder
        Route::post('questions/import', [QuestionController::class, 'import'])->middleware('role:teacher,admin');
        Route::apiResource('questions', QuestionController::class)->middleware('role:teacher,admin');
        Route::post('pulse-instruments/import', [PulseInstrumentController::class, 'import'])->middleware('role:teacher,admin');
        Route::apiResource('pulse-instruments', PulseInstrumentController::class)->middleware('role:teacher,admin');

        // Admin-only operations
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);
            Route::post('/users/{user}/verify-email', [AdminUserController::class, 'verifyEmail']);
            Route::patch('/users/{user}/status', [AdminUserController::class, 'toggleStatus']);
            Route::apiResource('users', AdminUserController::class);
        });
    });
});
