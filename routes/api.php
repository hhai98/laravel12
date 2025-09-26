<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Authentication routes (public)
    Route::post('login', [LoginController::class, 'login']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth user actions
        Route::post('logout', [LoginController::class, 'logout']);
        Route::post('logout-all', [LoginController::class, 'logoutAll']);
        Route::get('me', [LoginController::class, 'me']);
        Route::post('refresh', [LoginController::class, 'refresh']);
        Route::post('change-password', [LoginController::class, 'changePassword']);
        Route::get('sessions', [LoginController::class, 'sessions']);
        Route::delete('sessions', [LoginController::class, 'revokeSession']);
        
        // Roles
        Route::apiResource('roles', RoleController::class);
        
        // Users
        Route::apiResource('users', UserController::class);
        Route::post('users/{id}/restore', [UserController::class, 'restore']);
        Route::delete('users/{id}/force-delete', [UserController::class, 'forceDelete']);
        
        // Shops
        Route::apiResource('shops', ShopController::class);
        Route::post('shops/{id}/restore', [ShopController::class, 'restore']);
        Route::delete('shops/{id}/force-delete', [ShopController::class, 'forceDelete']);
    });
});
