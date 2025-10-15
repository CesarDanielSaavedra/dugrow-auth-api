<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware global para forzar el header Accept a application/json en todas las peticiones.
 *
 * Esto asegura que Laravel trate todas las requests como API y devuelva respuestas JSON,
 * evitando redirecciones a rutas web (como 'login') y errores inesperados en clientes que no envían el header.
 *
 * Útil como segundo escudo de robustez en APIs públicas o desacopladas, complementando la validación en el frontend.
 */
class ForceJsonResponse
{
    /**
     * Forza el header Accept a application/json en cada request entrante.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        return $next($request);
    }
}
