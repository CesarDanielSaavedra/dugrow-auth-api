
## 📝 Decisiones separation-ready y multi-empresa (Oct 2025)

> **Estado actual (Mayo 2026):** Los controllers `LoginController` y `RegisterController` están implementados pero las rutas en `routes/auth.php` siguen siendo stubs. Conectar rutas a controllers es la próxima tarea urgente.

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
- El endpoint `/auth/v1/refresh` permite renovar el token JWT.
- El endpoint `/auth/v1/user` obtiene los datos del usuario autenticado a partir del JWT.
- El logout invalida el token JWT (si se implementa blacklist, opcional).

**Compatibilidad Supabase:**
- El formato de requests y responses se mantiene compatible con Supabase, pero la autenticación es ahora JWT puro.

- Ver sección "Cambio clave: De Sanctum a JWT puro (tymon/jwt-auth)" en `ARCHITECTURE.md` para detalles técnicos y justificación.

## Objetivo
Esta API replica los endpoints y respuestas de Supabase Auth. El frontend puede apuntar a Supabase o a este backend cambiando solo la variable de entorno `NEXT_PUBLIC_PRODUCTION_API_BASE_URL`.

### Tabla de estado de implementación (Mayo 2026)

| Endpoint | Controller | Conectado en rutas |
|----------|-----------|-------------------|
| POST `/api/auth/v1/token` | `LoginController@login` ✅ | ⚠️ NO — ruta es stub |
| POST `/api/auth/v1/signup` | `RegisterController@register` ✅ | ⚠️ NO — ruta es stub |
| POST `/api/auth/v1/logout` | ⏳ pendiente | ⏳ pendiente |
| GET `/api/auth/v1/user` | ⏳ pendiente | ⏳ pendiente |
| POST `/api/auth/v1/recover` | ⏳ pendiente | ⏳ pendiente |

**Nota sobre el response del login:** El `LoginController` devuelve `access_token` pero NO incluye el objeto `user`. El frontend en `useLogin.ts` espera `data.user` para guardarlo en el store. Hay que actualizar el response.

---
## Endpoint: Login

### POST /auth/v1/token

- **Método:** POST
- **Body (JSON):**
  ```json
  {
    "email": "admin@dugrow.com",
    "password": "Password123!",
    "company_id": 1
  }
  ```

- **Respuesta exitosa (200):**
  ```json
  {
    "success": true,
    "access_token": "<JWT_TOKEN>",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "name": "Admin Dugrow",
      "email": "admin@dugrow.com",
      "company_id": 1,
      "role_id": 1
    }
  }
  ```

- **Error credenciales inválidas (401):**
  ```json
  {
    "success": false,
    "message": "Credenciales inválidas."
  }
  ```

- **Error de validación (422):**
  ```json
  {
    "success": false,
    "message": "Error de validación de datos.",
    "errors": {
      "company_id": [
        "La compañía seleccionada no existe."
      ]
    }
  }
  ```

---

**Notas:**
- Todos los endpoints devuelven respuestas en formato JSON.
- Para acceder a endpoints protegidos, incluye el token JWT en el header:
  ```http
  Authorization: Bearer <JWT_TOKEN>
  ```
- El campo `expires_in` indica el tiempo de validez del token en segundos.



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
