<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // RBAC Routes
    Route::middleware('role:student')->prefix('student')->group(function () {
        Route::get('/dashboard', fn() => response()->json(['m' => 'Student Area']));
    });

    Route::middleware('role:instructor')->prefix('instructor')->group(function () {
        Route::get('/dashboard', fn() => response()->json(['m' => 'Instructor Area']));
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', fn() => response()->json(['m' => 'Admin Area']));
    });
});
