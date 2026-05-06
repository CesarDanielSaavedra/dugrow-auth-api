
# dugrow-auth-api

> **Nota para el asistente:** Leer `ARCHITECTURE.md` y `AUTH_API.md` para contexto completo. El resumen de estado está en `AUTH_API.md`.

API de autenticación separation-ready para DuGrow, basada en Laravel + JWT puro (tymon/jwt-auth).
Compatible con la firma de Supabase Auth — el frontend puede cambiar de proveedor solo cambiando la variable de entorno.

## Estado actual (Mayo 2026)

- ✅ `LoginController` implementado (JWT, valida company_id)
- ✅ `RegisterController` implementado
- ✅ Modelos `User`, `Role`, `Company` con relaciones y SoftDeletes
- ✅ 4 migraciones en `database/migrations/auth/`
- ⚠️ `routes/auth.php` — rutas son stubs, controllers NO conectados
- ⏳ `LogoutController` y `UserController` pendientes

**Lo primero a hacer al retomar:** Conectar las rutas a los controllers existentes.

## Características

- Registro y login con validaciones avanzadas
- Protección por roles (admin, usuario común)
- Autenticación JWT estándar (tymon/jwt-auth)
- Arquitectura separation-ready (modelos, seeders, migraciones, controladores separados)

## Requisitos

- PHP >= 8.1
- Composer
- WAMP/XAMPP o MySQL/SQLite

## Instalación

1. Instala dependencias:
	```powershell
	composer install
	npm install
	```

2. Copia y configura tu archivo `.env`:
	```powershell
	cp .env.example .env
	# Edita .env con tus credenciales de base de datos
	```

3. Genera la clave de la aplicación:
	```powershell
	php artisan key:generate
	```

## ⚠️ PHP en WAMP — Usar PHP 8.2 en la terminal

WAMP usa PHP 7.4 por defecto en la CLI. Laravel 11 requiere PHP 8.2+.
Al abrir una terminal nueva, correr esto **antes de cualquier comando artisan**:

```powershell
$env:PATH="C:\wamp64\bin\php\php8.2.26;$env:PATH"
```

Verificar con `php --version` — debe mostrar `8.2.x`.
Ver detalles completos en [`docs/PHP_CLI_VERSION.md`](./docs/PHP_CLI_VERSION.md).

---

## Levantar el servidor

Para iniciar el servidor de desarrollo:
```powershell
php artisan serve
```
Por defecto estará disponible en http://localhost:8000

## Resetear Base de Datos y Poblar Datos Iniciales

Para limpiar la base de datos y cargar los datos iniciales, sigue estos pasos:

1. Ejecuta las migraciones principales (esto borra y recrea todas las tablas):
	```powershell
	php artisan migrate:fresh
	```

2. Ejecuta las migraciones de subcarpetas (por ejemplo, migraciones de autenticación):
	```powershell
	php artisan migrate --path=database/migrations/auth
	```

3. Corre los seeders para poblar los datos iniciales:
	```powershell
	php artisan db:seed
	```

Si necesitas ejecutar un seeder específico, usa:
```powershell
php artisan db:seed --class=Database\Seeders\Auth\UserSeeder
```

Esto dejará la base de datos lista para pruebas y desarrollo separation-ready.

## Datos de prueba

Usuarios generados por los seeders:

- **Admin**
  - Email: admin@dugrow.com
  - Password: Password123!
  - Rol: admin

- **Usuario común**
  - Email: usuario@dugrow.com
  - Password: Password123!
  - Rol: user

## Endpoints principales

- POST `/api/auth/v1/signup` — Registro de usuario
- POST `/api/auth/v1/token` — Login y obtención de token
- GET  `/api/auth/v1/user` — Usuario autenticado (requiere Bearer token)
- POST `/api/auth/v1/logout` — Cerrar sesión
- POST `/api/auth/v1/recover` — Recuperar contraseña

## Notas

- El proyecto está preparado para integración y pruebas separation-ready.
- Los endpoints protegidos requieren autenticación Bearer token (Sanctum).
- Compatible con la firma de Supabase Auth — el frontend puede apuntar a cualquiera de los dos.

---

Desarrollado por Dugrow Team.
