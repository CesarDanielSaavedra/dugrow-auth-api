# 🏗️ ARQUITECTURA SEPARATION-READY

## 📋 Índice
1. [Visión General](#-visión-general)
2. [Arquitectura Actual](#-arquitectura-actual)
3. [Estructura del Proyecto](#-estructura-del-proyecto)
4. [Convenciones OBLIGATORIAS](#️-convenciones-obligatorias)
5. [Guía de Separación Futura](#-guía-de-separación-futura)
6. [Checklist de Verificación](#-checklist-de-verificación)

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
    ├── auth.php                         ← Rutas Auth (/auth/v1/*)
    └── business.php                     ← Rutas Business (/api/v1/*)
```

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

#### **Día 2: Adaptadores de comunicación (4-6 horas)**

**3. En Business API, crear adaptador HTTP:**

```php
// app/Services/AuthService.php (en Business API)

class AuthService {
    private static $authApiUrl = 'http://auth-api.com';
    
    public static function validateToken($token) {
        $response = Http::withToken($token)
            ->post(self::$authApiUrl . '/api/validate');
        
        if ($response->successful()) {
            return $response->json();
        }
        
        throw new UnauthorizedException();
    }
    
    public static function getUserById($id) {
        $response = Http::get(self::$authApiUrl . "/api/users/{$id}");
        return $response->json();
    }
}
```

**4. Crear endpoint de validación en Auth API:**

```php
// En Auth API - routes/auth.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/validate', [AuthController::class, 'validateToken']);
    Route::get('/users/{id}', [UserController::class, 'show']);
});
```

#### **Día 3: Testing y Deploy (4-6 horas)**

**5. Actualizar frontend config:**

```javascript
// config/api.js

// ANTES (un solo backend):
const API_URL = 'http://localhost:8000';

// DESPUÉS (backends separados):
const AUTH_API = 'http://auth-api.com';
const BUSINESS_API = 'http://business-api.com';

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

**6. Testing completo:**
- ✅ Login funciona
- ✅ Token se valida correctamente
- ✅ Business API puede obtener info de usuarios
- ✅ Frontend funciona sin cambios en lógica

**7. Deploy:**
- Deploy Auth API en servidor 1
- Deploy Business API en servidor 2
- Actualizar DNS/URLs en frontend
- Monitorear logs

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
