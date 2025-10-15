<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Index/Orchestrator)
|--------------------------------------------------------------------------
|
| Incluye los archivos de rutas específicos de cada dominio (auth, business, etc).
|
| Aquí se pueden agregar middlewares o prefijos comunes a todas las rutas de la API.
|
*/

// Rutas de autenticación (/auth/v1/*)
require __DIR__.'/auth.php';

// Incluir rutas de negocio (futuro)
// require __DIR__.'/business.php';
