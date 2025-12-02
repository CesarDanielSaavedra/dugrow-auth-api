
# Dugrow Auth API

API de autenticación separation-ready para Dugrow, basada en Laravel y JWT.

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

- POST `/api/register` — Registro de usuario
- POST `/api/login` — Login y obtención de JWT

## Notas

- El proyecto está preparado para integración y pruebas separation-ready.
- Los endpoints protegidos requieren autenticación JWT.

---

Desarrollado por Dugrow Team.
