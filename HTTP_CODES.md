# Códigos de respuesta HTTP más usados en APIs REST

## 2xx: Éxito
- **200 OK**: Respuesta estándar para peticiones exitosas (GET, PUT, DELETE, etc.).
- **201 Created**: Recurso creado exitosamente (POST de registro, creación de entidad).
- **204 No Content**: Petición exitosa, pero sin contenido en la respuesta (usado en DELETE o actualizaciones sin retorno).

## 4xx: Errores del cliente
- **400 Bad Request**: La petición es inválida o malformada.
- **401 Unauthorized**: No autenticado o token inválido.
- **403 Forbidden**: Autenticado pero sin permisos suficientes.
- **404 Not Found**: El recurso solicitado no existe.
- **409 Conflict**: Conflicto de datos (por ejemplo, email ya registrado).
- **422 Unprocessable Entity**: Error de validación de datos (usado por Laravel para validaciones fallidas).

## 5xx: Errores del servidor
- **500 Internal Server Error**: Error inesperado en el servidor.
- **502 Bad Gateway**: El servidor recibió una respuesta inválida de otro servidor.
- **503 Service Unavailable**: El servidor no está disponible temporalmente (mantenimiento, sobrecarga).

---

## Ejemplo de uso en endpoints
- **POST /users** → 201 Created
- **GET /users/1** → 200 OK o 404 Not Found
- **POST /login** → 200 OK (login exitoso), 401 Unauthorized (credenciales inválidas)
- **POST /register** → 201 Created, 409 Conflict (email ya registrado), 422 Unprocessable Entity (validación)
- **DELETE /users/1** → 204 No Content, 404 Not Found

---

> Siempre usa el código más específico posible para que el frontend y los integradores puedan manejar correctamente cada caso.
