<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Auth API Routes
|--------------------------------------------------------------------------
|
| Rutas de autenticación. Todas con el prefijo /auth/v1.
| La autenticación la maneja Laravel Sanctum (guard 'api' → driver sanctum).
|
| Ejemplos de URLs finales:
| - POST /api/auth/v1/signup
| - POST /api/auth/v1/token
| - GET  /api/auth/v1/user   (protegida)
|
*/

Route::prefix('auth/v1')->group(function () {

    // ============================================
    // RUTAS PÚBLICAS (sin autenticación)
    // ============================================

    Route::post('/signup', [AuthController::class, 'register']);
    Route::post('/token', [AuthController::class, 'login']);
    Route::post('/recover', [AuthController::class, 'recover']);

    // ============================================
    // RUTAS PROTEGIDAS (requieren token válido)
    // ============================================

    Route::middleware('auth:api')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

});
