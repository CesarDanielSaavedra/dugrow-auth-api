# 📑 Documentación: API de Autenticación Compatible Supabase

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
