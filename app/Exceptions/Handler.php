<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    // =============================
    // 🟢 PROPÓSITO GENERAL
    // =============================

    /**
     * Excepciones que no se reportan.
     */
    protected $dontReport = [
        // ...existing code...
    ];

    /**
     * Campos que nunca se muestran en errores de validación.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Registrar callbacks globales de manejo de excepciones.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // ...existing code... (kept intentionally minimal)
        });
    }

    /**
     * Manejo de excepción de autenticación fallida.
     * Siempre responde con JSON 401, sin importar la ruta o el header.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return new JsonResponse([
            'message' => $exception->getMessage() ?: 'Unauthenticated.'
        ], 401);
    }

}
