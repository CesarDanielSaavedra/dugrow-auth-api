---

## 📝 Decisiones separation-ready y multi-empresa (Oct 2025)

Durante la implementación de la API de autenticación, se tomaron decisiones clave para garantizar una base separation-ready, compatible con escenarios multi-empresa y alineada con la integración Supabase-like:

- **Incorporación de companies:** Se agregó la tabla y modelo `companies` para soportar usuarios asociados a una empresa (`company_id`), permitiendo escenarios multi-tenant y segmentación de datos desde el inicio.
- **Relaciones explícitas:** Cada usuario pertenece a un rol y a una empresa, y estas relaciones están reflejadas en los modelos y migraciones, facilitando la exposición de datos relevantes en los endpoints.
- **Soft deletes en Auth:** Se implementó borrado lógico en users, roles y companies, asegurando trazabilidad y recuperación de datos, fundamental para auditoría y gestión multi-empresa.
- **Seeders orquestados:** Los seeders de Auth (incluyendo empresas, roles y usuarios admin) están orquestados para garantizar integridad referencial y facilidad de pruebas.
- **Ejecución modular de migraciones:** La API Auth puede reconstruirse desde cero ejecutando solo las migraciones de su subcarpeta, sin depender de lógica de negocio, lo que facilita la separación futura y el mantenimiento.

Estas prácticas aseguran que la API Auth no solo replica la interfaz de Supabase, sino que está preparada para escalar, desacoplarse y soportar múltiples empresas y roles de manera robusta y mantenible.

# 📑 Documentación: API de Autenticación Compatible Supabase

## 🔄 Cambio importante: De Sanctum a JWT puro (tymon/jwt-auth)

**Octubre 2025:** Se migró la autenticación de Laravel Sanctum a JWT puro usando el paquete `tymon/jwt-auth`.

**Motivación y ventajas:**
- JWT es un estándar abierto, ampliamente soportado y portable entre lenguajes, frameworks y microservicios.
- Permite una arquitectura 100% stateless, sin dependencias de base de datos para tokens ni cookies.
- Facilita la integración con frontend, apps móviles y otros servicios externos.
- Replica el flujo de autenticación de Supabase (login, refresh, logout, user info) de forma transparente.
- Es el método recomendado para APIs separation-ready y multi-empresa.

**¿Por qué no Sanctum?**
- Sanctum está pensado para SPAs en el mismo dominio o APIs simples, pero requiere almacenamiento de tokens y no es tan portable para microservicios.
- JWT permite validar tokens en cualquier servicio, sin acceso a la base de datos central.

**Paquete utilizado:**
- [`tymon/jwt-auth`](https://github.com/tymondesigns/jwt-auth)

**Impacto en los endpoints:**
- Todos los endpoints protegidos requieren ahora un header `Authorization: Bearer <token>` con un JWT válido.
- El login (`/auth/v1/token`) devuelve un JWT estándar, compatible con cualquier cliente.
- El endpoint `/auth/v1/refresh` permite renovar el token JWT.
- El endpoint `/auth/v1/user` obtiene los datos del usuario autenticado a partir del JWT.
- El logout invalida el token JWT (si se implementa blacklist, opcional).

**Compatibilidad Supabase:**
- El formato de requests y responses se mantiene compatible con Supabase, pero la autenticación es ahora JWT puro.

**Ver también:**
- Ver sección "Cambio clave: De Sanctum a JWT puro (tymon/jwt-auth)" en `ARCHITECTURE.md` para detalles técnicos y justificación.

## Objetivo
Esta API replica los endpoints y respuestas de Supabase Auth, permitiendo que el frontend funcione con Supabase o con este backend simplemente cambiando el endpoint.

---

## Endpoints principales

### 1. Registro de usuario
- **POST /auth/v1/signup**
- **Request:**
  - email
  - password
- **Response:**
  - Usuario creado, datos básicos, mensaje de éxito o error

### 2. Login (token)
- **POST /auth/v1/token**
- **Request:**
  - email
  - password
- **Response:**
  - access_token
  - refresh_token (opcional)
  - usuario
  - mensaje de éxito o error

### 3. Obtener usuario autenticado
- **GET /auth/v1/user**
- **Headers:**
  - Authorization: Bearer <token>
- **Response:**
  - Datos del usuario
  - mensaje de éxito o error

### 4. Logout
- **POST /auth/v1/logout**
- **Headers:**
  - Authorization: Bearer <token>
- **Response:**
  - mensaje de éxito o error

### 5. Recuperar contraseña
- **POST /auth/v1/recover**
- **Request:**
  - email
- **Response:**
  - mensaje de éxito o error

### 6. Verificar email
- **POST /auth/v1/verify**
- **Request:**
  - token de verificación
- **Response:**
  - mensaje de éxito o error

### 7. Refrescar token
- **POST /auth/v1/refresh**
- **Request:**
  - refresh_token
- **Response:**
  - access_token nuevo
  - mensaje de éxito o error

---

## Relación entre User y Role

- Cada usuario (`User`) pertenece a un rol (`Role`) mediante el campo `role_id`.
- La relación se define en el modelo User: `public function role()`
- Un rol puede tener muchos usuarios: `public function users()` en el modelo Role.

## Uso de SoftDeletes

- Se utiliza el trait `SoftDeletes` en ambos modelos para permitir borrado lógico.
- Las migraciones agregan el campo `deleted_at` en las tablas correspondientes.

## Exposición del rol en la API

- El rol del usuario se expondrá como string en las respuestas de la API, usando la relación con el modelo Role.
- Ejemplo de respuesta:
  ```json
  {
    "id": 1,
    "name": "Juan",
    "email": "juan@email.com",
    "role": "admin"
  }
  ```

## Decisiones pendientes

- Implementar verificación de email (`MustVerifyEmail`) en el modelo User.
- Definir el flujo de registro y verificación en endpoints futuros.

---

## Notas de compatibilidad
- Los formatos de request y response deben ser lo más similares posible a Supabase.
- Los errores y códigos de estado deben seguir el estándar REST (200, 201, 400, 401, 403, etc).
- Documentar cualquier diferencia relevante.

---

## Ejemplos y casos de uso
(Completar a medida que se implementan los endpoints)
