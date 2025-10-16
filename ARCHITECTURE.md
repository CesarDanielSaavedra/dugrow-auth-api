# 🏗️ ARQUITECTURA SEPARATION-READY

## 📋 Índice
1. [Visión General](#-visión-general)
2. [Configuración Base Laravel API](#-configuración-base-laravel-api)
3. [Arquitectura Actual](#-arquitectura-actual)
4. [Estructura del Proyecto](#-estructura-del-proyecto)
5. [Convenciones OBLIGATORIAS](#️-convenciones-obligatorias)
6. [Guía de Separación Futura](#-guía-de-separación-futura)
7. [Checklist de Verificación](#-checklist-de-verificación)

---

## 🎯 Visión General

### **Arquitectura Actual (Fase 1: MVP)**
```
📱 Frontend (Next.js/React)
        ↕️ HTTP/JSON
📦 Backend API (Laravel - Un solo repo)
├── 🔐 Auth Endpoints (/auth/v1/*)
├── 🍷 Business Endpoints (/api/v1/*)
└── 💾 Una base de datos MySQL
```

**Características:**
- ✅ Backend y frontend **completamente desacoplados**
- ✅ Comunicación solo por HTTP/JSON
- ✅ Frontend puede cambiar de tecnología sin afectar backend
- ✅ Backend preparado para separación futura

### **Arquitectura Futura (Fase 2: Microservicios)**
```
📱 Frontend (Next.js/React)
        ↕️                    ↕️
🔐 Auth API              🍷 Business API
(Laravel - Repo 1)       (Laravel - Repo 2)
├── Users                ├── Business logic
├── Tokens               ├── Consulta Auth API
└── BD Auth              └── BD Business
```

**Objetivo:** Separación sin dolor cuando sea necesario (5+ clientes activos).

---

## ⚙️ Configuración Base Laravel API

### **🚨 CRÍTICO: Laravel debe configurarse como API pura, NO híbrido (web+api)**

**¿Por qué es importante?**

Laravel por defecto viene configurado para aplicaciones WEB (con vistas, sesiones, cookies). Si no lo configuramos correctamente como API, tendremos problemas:
- ❌ Intentará redirigir a rutas `login` inexistentes
- ❌ Cargará middleware innecesario (sesiones, CSRF)
- ❌ Sanctum funcionará en modo "stateful" (para SPAs en mismo dominio)
- ❌ Mayor consumo de recursos

**Nosotros necesitamos API stateless:** Token JWT en cada request, sin sesiones, sin cookies.

---

### **📦 Instalación correcta: `php artisan install:api`**

**Si estás creando el proyecto DESDE CERO:**
```bash
composer create-project laravel/laravel dugrow-auth-api
cd dugrow-auth-api
php artisan install:api  # ← ESTE COMANDO ES CRÍTICO
```

**Si ya tenés el proyecto iniciado (nuestro caso):**
```bash
cd dugrow-dashboard
php artisan install:api  # ← Reconfigura Laravel a API-only
```

**¿Qué hace `install:api`?**
1. ✅ Instala Laravel Sanctum
2. ✅ Publica `config/sanctum.php` con configuración stateless
3. ✅ Modifica `bootstrap/app.php` para remover comportamiento web
4. ✅ Configura middleware API-only (sin sesiones, sin cookies, sin CSRF)
5. ✅ Crea migración de `personal_access_tokens`

---

### **✅ Verificación: `bootstrap/app.php` correcto**

**ANTES de `install:api` (híbrido - ❌ INCORRECTO):**
```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',      // ← NO lo necesitamos
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware híbrido (sesiones, cookies, CSRF)
    })
    ->create();
```

**DESPUÉS de `install:api` (API pura - ✅ CORRECTO):**
```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',      // Solo API
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // NOTA: NO hay 'web'
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware API-only (stateless)
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Manejar errores de autenticación devolviendo JSON
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        });
    })
    ->create();
```

---

### **🔍 Diferencias clave:**

| Aspecto | Híbrido (web+api) ❌ | API pura ✅ |
|---------|---------------------|-------------|
| **Rutas web** | `web: routes/web.php` | NO existe |
| **Sesiones** | Sí (en cookies) | NO |
| **CSRF tokens** | Sí | NO |
| **Sanctum mode** | Stateful (cookies) | Stateless (JWT) |
| **Error auth** | Redirige a /login | JSON 401 |
| **Peso** | ~40 MB en memoria | ~25 MB |

---

### **⚠️ Problemas comunes si NO configurás como API:**

**Problema 1: "Route [login] not defined"**
```
Symfony\Component\Routing\Exception\RouteNotFoundException
Route [login] not defined.
```

**Causa:** Laravel intenta redirigir a `/login` cuando detecta usuario no autenticado.

**Solución:** Ejecutar `php artisan install:api` y agregar manejador de excepciones.

---

**Problema 2: CORS no funciona**

**Causa:** Middleware de sesiones interfiere con headers CORS.

**Solución:** Configurar como API pura (sin sesiones).

---

**Problema 3: Tokens no se validan**

**Causa:** Sanctum está en modo "stateful" esperando cookies, no tokens Bearer.

**Solución:** `install:api` configura Sanctum en modo stateless.

---

### **📝 Checklist de configuración correcta:**

Después de `install:api`, verificá:

```bash
# 1. Sanctum instalado
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 2. Migraciones ejecutadas
php artisan migrate

# 3. Rutas API registradas
php artisan route:list --path=api

# 4. Config correcta
cat config/sanctum.php  # Debe tener stateful = []
```

**Resultado esperado:**
- ✅ Archivo `config/sanctum.php` existe
- ✅ Tabla `personal_access_tokens` en BD
- ✅ Rutas `/api/*` visibles en `route:list`
- ✅ NO hay rutas web (solo api, console, health)

---

### **🎯 Resumen:**

**SIEMPRE ejecutar `php artisan install:api` al crear proyectos de API con Laravel.**

Es la diferencia entre:
- ❌ Configuración híbrida con parches y problemas
- ✅ Configuración profesional API-first

**Este comando es la BASE del proyecto. Sin él, todo lo demás tendrá problemas.**

---

## 🏛️ Arquitectura Actual

### **Principios de diseño:**

1. **Backend = APIs REST puras**
   - Sin vistas Blade (excepto documentación)
   - Solo respuestas JSON
   - Stateless (sin sesiones)

2. **Frontend = Cliente HTTP**
   - Consume APIs vía fetch/axios
   - Maneja autenticación con tokens
   - Completamente independiente del backend

3. **Separación lógica desde día 1**
   - Código Auth aislado
   - Código Business aislado
   - Sin dependencias cruzadas

---

## 📁 Estructura del Proyecto

### **Organización de carpetas:**

```
dugrow-auth-api/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                    ← 🔐 TODO Auth aquí
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   ├── LogoutController.php
│   │   │   │   └── UserController.php
│   │   │   │
│   │   │   └── Business/                ← 🍷 TODO Business aquí
│   │   │       ├── WineController.php
│   │   │       └── GymController.php
│   │   │
│   │   └── Middleware/
│   │       └── ValidateToken.php        ← Reutilizable
│   │
│   ├── Models/
│   │   ├── User.php                     ← Auth models
│   │   ├── Role.php                     ← Auth models
│   │   ├── Wine.php                     ← Business models
│   │   └── GymMember.php                ← Business models
│   │
│   └── Services/
│       ├── AuthService.php              ← Lógica Auth aislada
│       └── BusinessService.php          ← Lógica Business aislada
│
├── database/
│   └── migrations/
│       ├── auth/                        ← Migraciones Auth
│       │   ├── create_users_table.php
│       │   ├── create_roles_table.php
│       │   └── create_personal_access_tokens_table.php
│       │
│       └── business/                    ← Migraciones Business
│           ├── create_wines_table.php
│           └── create_gym_members_table.php
│
└── routes/
    ├── api.php                          ← Archivo INDEX (incluye auth.php y business.php)
    ├── auth.php                         ← Rutas Auth (/auth/v1/*)
    └── business.php                     ← Rutas Business (/api/v1/*)
```

### 📂 **Detalles: Estructura de Rutas**

**¿Por qué 3 archivos de rutas?**

Laravel 11 no incluye `routes/api.php` por defecto. Debemos crearlo manualmente y organizarlo como un **archivo orquestador** que incluye las rutas de cada dominio.

**Flujo de carga de rutas:**

```
1. Laravel carga bootstrap/app.php
2. bootstrap/app.php registra routes/api.php (prefijo /api)
3. routes/api.php incluye require __DIR__.'/auth.php'
4. routes/api.php incluye require __DIR__.'/business.php'
```

**Contenido de cada archivo:**

**routes/api.php** - Archivo INDEX (orquestador)
```php
<?php
// Este archivo actúa como "índice" que incluye las rutas de cada dominio

use Illuminate\Support\Facades\Route;

// Incluir rutas de autenticación (/auth/v1/*)
require __DIR__.'/auth.php';

// Incluir rutas de negocio (/api/v1/*) - cuando existan
// require __DIR__.'/business.php';
```

**routes/auth.php** - Rutas de autenticación
```php
<?php
// Todas las rutas de Auth van aquí, separadas de Business

use Illuminate\Support\Facades\Route;

Route::prefix('auth/v1')->group(function () {
    // Rutas públicas (sin middleware)
    Route::post('/signup', function() { /* TODO: controller */ });
    Route::post('/token', function() { /* TODO: controller */ });
    
    // Rutas protegidas (con middleware auth:sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function() { /* TODO: controller */ });
        Route::post('/logout', function() { /* TODO: controller */ });
    });
});
```

**routes/business.php** - Rutas de lógica de negocio (futuro)
```php
<?php
// Todas las rutas de Business van aquí, separadas de Auth

use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    // Ejemplo: gestión de vinos
    Route::get('/wines', function() { /* TODO: controller */ });
    Route::post('/wines', function() { /* TODO: controller */ });
});
```

**Ventajas de esta estructura:**
- ✅ **Separación clara**: Cada dominio tiene su archivo
- ✅ **Separation-ready**: Copiar `auth.php` al Auth API será trivial
- ✅ **Escalable**: Agregar `business.php`, `admin.php`, etc. es simple
- ✅ **Mantenible**: No hay un archivo gigante con 500 rutas mezcladas

---

## 🛡️ Convenciones OBLIGATORIAS

### **⚠️ Reglas que NO se pueden romper:**

#### **1. Separación estricta de carpetas**

```php
✅ CORRECTO:
app/Http/Controllers/Auth/LoginController.php
app/Http/Controllers/Business/WineController.php

❌ INCORRECTO:
app/Http/Controllers/LoginController.php  // ¿Auth o Business?
app/Http/Controllers/MixedController.php  // ¡NUNCA!
```

#### **2. No mezclar lógica Auth con Business**

```php
❌ MAL (acoplado):
class WineController {
    public function index() {
        $user = User::find(auth()->id());  // Dependencia directa
        $wines = Wine::where('user_id', $user->id)->get();
    }
}

✅ BIEN (desacoplado):
class WineController {
    public function index(Request $request) {
        $userId = $request->get('user_id');  // Del middleware
        $wines = Wine::where('user_id', $userId)->get();
    }
}
```

#### **3. Usar IDs, no relaciones Eloquent directas**

```php
❌ MAL:
$wine->user;  // Relación Eloquent entre dominios

✅ BIEN:
$wine->user_id;  // Solo el ID
// Luego: $user = AuthService::getUserById($wine->user_id);
```

#### **4. Services como única fuente de verdad**

```php
✅ CORRECTO:
// En cualquier parte del código:
$user = AuthService::getUserById($id);

// Cuando separes en repos:
// AuthService cambia internamente a HTTP call
// El resto del código NO cambia
```

#### **5. Rutas organizadas por dominio**

```php
// routes/auth.php
Route::prefix('auth/v1')->group(function () {
    Route::post('/signup', [RegisterController::class, 'register']);
    Route::post('/token', [LoginController::class, 'login']);
    Route::post('/logout', [LogoutController::class, 'logout']);
});

// routes/business.php
Route::prefix('api/v1')->group(function () {
    Route::get('/wines', [WineController::class, 'index']);
});
```

#### **6. Respuestas JSON estandarizadas**

```php
// Éxito:
return response()->json([
    'success' => true,
    'data' => $data
], 200);

// Error:
return response()->json([
    'success' => false,
    'error' => 'Message here'
], 400);
```

---

## Estructura de modelos y carpetas

- Los modelos se agrupan por contexto en subdirectorios dentro de `app/Models`.
  - Ejemplo: `app/Models/Auth/User.php`, `app/Models/Auth/Role.php`

## Convención de nombres

- Los modelos se nombran en singular y con mayúscula inicial (ej: `User`, `Role`).
- Las tablas en la base de datos se nombran en plural y minúsculas (ej: `users`, `roles`).
- Los modelos siguen el namespace correspondiente a su carpeta.

---

## 🔄 Guía de Separación Futura

### **¿Cuándo separar?**

**Señales de que es momento:**
- ✅ 5+ clientes activos pagando
- ✅ El proyecto genera ingresos estables
- ✅ Necesidad de escalar Auth y Business independientemente
- ✅ Equipo más grande (2+ desarrolladores)

**NO separes si:**
- ❌ Aún estás en MVP
- ❌ Menos de 3 clientes
- ❌ No hay problemas de performance

---

### **Proceso de Separación (2-3 días)**

#### **Día 1: Preparación (2-3 horas)**

**1. Crear nuevo repositorio:**
```bash
git clone dugrow-auth-api dugrow-business-api
cd dugrow-business-api
git remote set-url origin https://github.com/tu-usuario/dugrow-business-api.git
```

**2. Limpiar repositorios:**
```bash
# En dugrow-auth-api:
- Borrar app/Http/Controllers/Business/
- Borrar database/migrations/business/
- Borrar routes/business.php

# En dugrow-business-api:
- Borrar app/Http/Controllers/Auth/
- Borrar database/migrations/auth/
- Borrar routes/auth.php
```

#### **Día 2: Configurar BDs separadas (2-3 horas)**

**3. Crear bases de datos independientes:**

```sql
-- En servidor MySQL
CREATE DATABASE dugrow_auth;
CREATE DATABASE dugrow_business;
```

**4. Migrar datos:**

```bash
# Auth API: Solo tablas de autenticación
php artisan migrate --path=database/migrations/auth

# Business API: Solo tablas de negocio
php artisan migrate --path=database/migrations/business
```

**5. Configurar .env de cada API:**

```env
# Auth API (.env)
DB_DATABASE=dugrow_auth
APP_URL=https://auth-api.dugrow.com

# Business API (.env)
DB_DATABASE=dugrow_business
APP_URL=https://business-api.dugrow.com
AUTH_API_URL=https://auth-api.dugrow.com  ← URL del Auth API
```

---

#### **Día 3: Middleware de validación HTTP (1-2 horas)**

**⚠️ PASO CRÍTICO: Validación de tokens entre microservicios**

En el monolito, Laravel valida tokens automáticamente con `auth:sanctum` porque ambas APIs comparten la BD.

Después de separar, **Business API NO tiene acceso** a la tabla `personal_access_tokens` del Auth API.

**Solución:** Business API hace HTTP request al Auth API para validar cada token.

**6. Crear middleware en Business API:**

```php
<?php
// Business API: app/Http/Middleware/ValidateTokenViaAuthAPI.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ValidateTokenViaAuthAPI
{
    /**
     * Valida el token JWT haciendo un request al Auth API.
     * Reemplaza el middleware 'auth:sanctum' después de la separación.
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtener token del header Authorization
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'error' => 'Token missing'
            ], 401);
        }
        
        // Llamar al Auth API para validar el token
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->get(config('services.auth_api.url') . '/auth/v1/user');
        
        // Si el Auth API devuelve error, el token es inválido
        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired token'
            ], 401);
        }
        
        // Token válido: adjuntar datos del usuario al request
        // Esto permite usar $request->get('authenticated_user') en los controllers
        $request->merge([
            'authenticated_user' => $response->json()
        ]);
        
        return $next($request);
    }
}
```

**7. Registrar el middleware en Business API:**

```php
// Business API: bootstrap/app.php

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrar alias para el middleware de validación remota
        $middleware->alias([
            'auth.remote' => \App\Http\Middleware\ValidateTokenViaAuthAPI::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

**8. Configurar URL del Auth API:**

```php
// Business API: config/services.php

return [
    // ... otros servicios
    
    'auth_api' => [
        'url' => env('AUTH_API_URL', 'http://localhost:8000'),
    ],
];
```

**9. Reemplazar middleware en rutas Business:**

```php
// Business API: routes/business.php

// ❌ ANTES (monolito - NO funciona después de separar):
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/wines', [WineController::class, 'index']);
    Route::get('/gyms', [GymController::class, 'index']);
});

// ✅ DESPUÉS (microservicio - valida vía HTTP):
Route::middleware('auth.remote')->group(function () {
    Route::get('/wines', [WineController::class, 'index']);
    Route::get('/gyms', [GymController::class, 'index']);
});
```

**Ventajas de este enfoque:**
- ✅ Auth API sigue siendo la única fuente de verdad para autenticación
- ✅ Si revocás un token en Auth API, Business API lo detecta al instante
- ✅ Separación real: cada API tiene su propia BD
- ✅ Código simple: ~30 líneas de middleware

**Desventajas:**
- ⚠️ Cada request al Business API hace otro request al Auth API (latencia adicional ~50-100ms)
- ⚠️ Si Auth API está caído, Business API no puede validar tokens

**Alternativas (para considerar en el futuro):**
- JWT auto-contenido (no necesita validar en BD, pero no se pueden revocar)
- Caché de validaciones (validar cada 5 minutos en vez de cada request)
- BD compartida solo para tokens (no recomendado, rompe separación)

---

#### **Día 4: Testing y Deploy (3-4 horas)**

**10. Actualizar frontend config:**

```javascript
// config/api.js

// ANTES (un solo backend):
const API_URL = 'http://localhost:8000';

// DESPUÉS (backends separados):
const AUTH_API = 'https://auth-api.dugrow.com';
const BUSINESS_API = 'https://business-api.dugrow.com';

export const apiConfig = {
    auth: {
        login: `${AUTH_API}/auth/v1/token`,
        register: `${AUTH_API}/auth/v1/signup`,
        user: `${AUTH_API}/auth/v1/user`,
    },
    business: {
        wines: `${BUSINESS_API}/api/v1/wines`,
        gym: `${BUSINESS_API}/api/v1/gym`,
    }
};
```

**11. Testing completo:**
- ✅ Login en Auth API funciona
- ✅ Token se genera correctamente
- ✅ Business API valida tokens vía HTTP al Auth API
- ✅ Business API devuelve datos correctamente
- ✅ Frontend funciona sin cambios en lógica (solo cambio de URLs)

**12. Deploy:**
- Deploy Auth API en servidor 1 (o Heroku, Railway, etc.)
- Deploy Business API en servidor 2
- Actualizar DNS/URLs en frontend
- Configurar CORS en ambas APIs
- Monitorear logs y tiempos de respuesta

---

### **Resumen de tiempos:**

| Día | Tarea | Tiempo estimado |
|-----|-------|-----------------|
| 1 | Copiar carpetas, limpiar repos | 2-3 horas |
| 2 | Configurar BDs separadas | 2-3 horas |
| 3 | Crear middleware de validación HTTP | 1-2 horas |
| 4 | Testing, deploy, monitoreo | 3-4 horas |
| **TOTAL** | | **8-12 horas (3-4 días)** |

---

### **Plan de Rollback**

Si algo falla:

**Opción A: Rollback completo (15 minutos)**
```javascript
// Volver config a un solo backend:
const API_URL = 'http://old-backend.com';
```

**Opción B: Rollback parcial**
- Mantener Auth API separada
- Devolver Business al repo original temporalmente
- Debuggear problema
- Reintentar migración

---

## 🛡️ Estrategia de Manejo de Excepciones y Planificación para Microservicios

Actualmente, el proyecto utiliza un único Handler de excepciones (`app/Exceptions/Handler.php`) de propósito general, que responde siempre con JSON 401 ante errores de autenticación, sin importar el tipo de endpoint ni el header recibido. Esta decisión se tomó porque:
- Todas las APIs (auth y business) requieren el mismo comportamiento ante autenticación fallida.
- Se evita la duplicación de lógica y se simplifica el mantenimiento.
- El Handler es reutilizable en cualquier microservicio futuro, manteniendo coherencia y robustez.

**Nota:**
En la planificación inicial se consideró separar la lógica por tipo de API, pero al adoptar un enfoque API-only y respuestas universales, se determinó que un solo Handler es suficiente y óptimo para el proyecto y su escalabilidad.

---

## ✅ Checklist de Verificación

### **Antes de cada commit, verificar:**

- [ ] ¿Los controladores están en carpetas Auth/ o Business/?
- [ ] ¿No hay `use` statements cruzados entre Auth y Business?
- [ ] ¿Los Services están aislados (AuthService vs BusinessService)?
- [ ] ¿Las rutas están en archivos separados (auth.php vs business.php)?
- [ ] ¿Uso IDs en lugar de relaciones Eloquent directas?
- [ ] ¿Las respuestas JSON son estandarizadas?

### **Antes de separar en microservicios:**

- [ ] ¿Todas las reglas de separación se respetaron?
- [ ] ¿Services usan métodos estáticos para fácil adaptación?
- [ ] ¿No hay código duplicado entre Auth y Business?
- [ ] ¿Frontend consume APIs solo por HTTP (sin dependencias)?
- [ ] ¿Hay tests para endpoints críticos?
- [ ] ¿Documentación de APIs está actualizada?

---

## 📝 Notas Finales

### **Filosofía del proyecto:**

> "Empezar simple, pero arquitecturar para complejidad futura"

**Esto significa:**
- ✅ Código organizado desde día 1
- ✅ Sin sobre-ingeniería prematura
- ✅ Preparado para escalar cuando sea necesario
- ✅ Sin vendor lock-in

### **Recursos útiles:**

- [Laravel Docs - API Resources](https://laravel.com/docs/eloquent-resources)
- [Supabase Auth API](https://supabase.com/docs/guides/auth)
- [Microservices Patterns](https://microservices.io/patterns/index.html)

---

**Última actualización:** Octubre 2025  
**Versión:** 1.0  
**Autor:** Daniel Saavedra (DuGrow)
