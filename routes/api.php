<?php

use App\Http\Controllers\AdminStatsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\PlatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [UserController::class, 'me']);
    Route::get('user', [UserController::class, 'me']);

    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::get('categories/{category}/plates', [CategoryController::class, 'plates']);
    Route::get('categories/{category}/plats', [CategoryController::class, 'plates']);

    Route::get('plates', [PlatController::class, 'index']);
    Route::get('plates/{plate}', [PlatController::class, 'show']);
    Route::get('plats', [PlatController::class, 'index']);
    Route::get('plats/{plate}', [PlatController::class, 'show']);

    Route::middleware('admin')->group(function () {
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        Route::post('plates', [PlatController::class, 'store']);
        Route::put('plates/{plate}', [PlatController::class, 'update']);
        Route::delete('plates/{plate}', [PlatController::class, 'destroy']);
        Route::post('plats', [PlatController::class, 'store']);
        Route::put('plats/{plate}', [PlatController::class, 'update']);
        Route::delete('plats/{plate}', [PlatController::class, 'destroy']);

        Route::get('ingredients', [IngredientController::class, 'index']);
        Route::post('ingredients', [IngredientController::class, 'store']);
        Route::put('ingredients/{ingredient}', [IngredientController::class, 'update']);
        Route::delete('ingredients/{ingredient}', [IngredientController::class, 'destroy']);

        Route::get('admin/stats', [AdminStatsController::class, 'show']);
    });
});




