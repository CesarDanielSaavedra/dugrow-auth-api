<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth API Routes
|--------------------------------------------------------------------------
|
| Rutas de autenticación compatibles con Supabase Auth API.
| Todas las rutas tienen el prefijo /auth/v1
|
| Ejemplos de URLs finales:
| - POST /api/auth/v1/signup
| - POST /api/auth/v1/token
| - GET  /api/auth/v1/user (protegida con auth:sanctum)
|
*/

Route::prefix('auth/v1')->group(function () {

    // ============================================
    // RUTAS PÚBLICAS (sin autenticación)
    // ============================================

    // Registro de nuevo usuario
    Route::post('/signup', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);

    // Login (obtener token)
    Route::post('/token', function () {
        return response()->json([
            'message' => 'Endpoint /token - TODO: Implementar LoginController'
        ]);
    });

    // Recuperar contraseña
    Route::post('/recover', function () {
        return response()->json([
            'message' => 'Endpoint /recover - TODO: Implementar PasswordRecoveryController'
        ]);
    });


    // ============================================
    // RUTAS PROTEGIDAS (requieren token válido)
    // ============================================

    Route::middleware('auth:sanctum')->group(function () {

        // Obtener datos del usuario autenticado
        Route::get('/user', function () {
            return response()->json([
                'message' => 'Endpoint /user - TODO: Implementar UserController',
                'user' => auth()->user()
            ]);
        });

        // Cerrar sesión (invalidar token)
        Route::post('/logout', function () {
            return response()->json([
                'message' => 'Endpoint /logout - TODO: Implementar LogoutController'
            ]);
        });

    });

});
