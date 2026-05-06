
# Dugrow Auth API - TEST DE DEPLOY EN  PROD

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

<<<<<<< Updated upstream
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
=======
The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Setup local para duGrow Auth API (Laravel + WAMP + HeidiSQL)

### 1. Clona el repositorio

Abre la carpeta `www` de tu WAMP (`C:\wamp64\www`).
Ejecuta el siguiente comando en PowerShell o CMD:

```bash
git clone <url-del-repo>
```

### 2. Instala Composer

Si no tienes Composer instalado en Windows:
- Descarga el instalador oficial desde [getcomposer.org](https://getcomposer.org/)
- Ejecuta el instalador y asegúrate de agregar Composer al PATH
- Prueba la instalación con:

```bash
composer --version
```

### 3. Instala dependencias de Laravel

Navega al directorio del proyecto y corre:

```bash
composer install
```

### 4. Configura la base de datos

Abre HeidiSQL para verificar:
- Host: `127.0.0.1` o `localhost`
- Puerto: `3306` (el default de WAMP)
- Usuario: `root` (por defecto)
- Contraseña: (en WAMP suele estar vacía)
- El nombre de la base de datos (visible en el árbol izquierdo, puedes crearla desde ahí)

Edita el archivo `.env` con estos datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dugrow-auth-api
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Genera la clave de la app

```bash
php artisan key:generate
```

### 6. Levanta el servidor de Laravel

Puedes usar el server embebido (dev):

```bash
php artisan serve
```

O configurar un VirtualHost en WAMP si prefieres que responda directo desde Apache.

### 7. Probar integración con Frontend

Usa la URL local (por ejemplo `http://localhost:8000`) para desarrollar y testear el front y la API juntos.
>>>>>>> Stashed changes
