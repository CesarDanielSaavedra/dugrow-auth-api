# Guía de Autenticación para Frontend

Esta guía explica cómo el frontend debe interactuar con la API de Autenticación de Dugrow Dashboard.

---

## � **Concepto clave: JWT y la clave secreta**

> **El frontend NUNCA necesita la clave secreta (`JWT_SECRET`).**

Un token JWT tiene 3 partes separadas por `.`:

```
[Header].[Payload].[Signature]
    ↑          ↑          ↑
no importa   base64    generada con
             legible    JWT_SECRET
             sin key   (solo backend)
```

- El **Payload** es simplemente base64url — cualquiera puede leerlo.
- La **Signature** garantiza que el token es auténtico y no fue manipulado.
- Solo el **backend** puede crear y verificar tokens (porque tiene la clave).
- El **frontend** solo lee el payload (sin ninguna key) y confía en su contenido porque el backend ya lo firmó.

**Regla:** La `JWT_SECRET` vive en el `.env` del backend. No la tenés que tocar desde el front.

---

## 📋 **Base URLs**

```javascript
// app/lib/config.js
export const AUTH_API = process.env.NEXT_PUBLIC_AUTH_API_URL + '/auth/v1';
// Ejemplo: http://localhost:8000/api/auth/v1

// API de negocio (próximamente)
export const STOCK_API = process.env.NEXT_PUBLIC_AUTH_API_URL + '/stock/v1';
```

`.env.local` del frontend:
```
NEXT_PUBLIC_AUTH_API_URL=http://localhost:8000/api
```

---

## 🔐 **1. Login (Obtener Token)**

### **Request:**
```javascript
// POST /api/auth/v1/token
const response = await fetch(`${AUTH_API}/token`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'admin@dugrow.com',
    password: 'Password123!',
    company_id: 1           // ← obligatorio, el backend lo valida
  })
});

const data = await response.json();
// data.access_token → guardarlo en el store
// data.user         → info del usuario (también viene en el token)
```

### **Response Exitosa (200):**
```json
{
  "success": true,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "Admin Dugrow",
    "email": "admin@dugrow.com",
    "company_id": 1,
    "role_id": 1,
    "role": { "id": 1, "name": "admin" }
  }
}
```

### **Contenido del Token JWT (payload):**

El payload del JWT se puede leer en el frontend con base64 — **sin ninguna clave**:
```json
{
  "sub": 1,
  "name": "Admin Dugrow",
  "email": "admin@dugrow.com",
  "company_id": 1,
  "role_id": 1,
  "role_name": "admin",
  "iat": 1704067200,
  "exp": 1704153600
}
```

> ℹ️ El frontend lee estos claims directamente del token (ver sección de utilidades abajo).
> No hace falta llamar a `/user` para saber el rol o el company_id.

---

## 🛠️ **2. Utilidades JWT (sin librerías, sin clave secreta)**

```javascript
// lib/jwt.js

/**
 * Decodifica el payload de un JWT.
 * El payload es base64url — no necesita ninguna clave.
 * La seguridad la garantiza el backend al firmar el token.
 */
export const decodeToken = (token) => {
  try {
    const payload = token.split('.')[1];
    // base64url → base64 estándar → JSON
    const json = atob(payload.replace(/-/g, '+').replace(/_/g, '/'));
    return JSON.parse(json);
  } catch {
    return null;
  }
};

/**
 * Verifica si el token ya expiró (usando el claim `exp`).
 * No valida la firma — eso lo hace el backend en cada request.
 */
export const isTokenExpired = (token) => {
  const decoded = decodeToken(token);
  if (!decoded?.exp) return true;
  return Date.now() / 1000 > decoded.exp;  // exp está en segundos Unix
};
```

**Ejemplo de uso:**
```javascript
import { decodeToken, isTokenExpired } from '@/lib/jwt';

const claims = decodeToken(token);
// claims.role_name  → 'admin' | 'user'
// claims.company_id → 1
// claims.sub        → user_id
// claims.exp        → timestamp de expiración

if (isTokenExpired(token)) {
  // Limpiar sesión y redirigir a login
}
```

---

## � **3. Cliente HTTP con token automático**

En vez de agregar el header `Authorization` en cada request, usá un wrapper global:

```javascript
// lib/apiClient.js
import { AUTH_API } from './config';
import { isTokenExpired } from './jwt';

export const apiClient = async (url, options = {}) => {
  // Leer el store sin hooks (funciona fuera de componentes)
  const { token, logout } = useAuthStore.getState();

  // Si el token ya expiró localmente, hacer logout antes de llamar al backend
  if (token && isTokenExpired(token)) {
    logout();
    window.location.href = '/login';
    return;
  }

  const response = await fetch(url, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...(token && { Authorization: `Bearer ${token}` }),
      ...options.headers, // permite sobrescribir si es necesario
    },
  });

  // El backend rechazó el token (expirado, manipulado, etc.)
  if (response.status === 401) {
    logout();
    window.location.href = '/login';
    return;
  }

  if (response.status === 403) {
    throw new Error('No tenés permisos para esta acción');
  }

  return response.json();
};
```

**Uso:**
```javascript
// Sin preocuparte por el token en cada llamada
const productos = await apiClient(`${STOCK_API}/products`);
const detalle  = await apiClient(`${STOCK_API}/products/5`);
await apiClient(`${STOCK_API}/products`, { method: 'POST', body: JSON.stringify(data) });
```

## 🧠 **4. Store de Autenticación (Zustand)**

```javascript
// stores/authStore.js
import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { decodeToken, isTokenExpired } from '@/lib/jwt';

const useAuthStore = create(
  persist(
    (set, get) => ({
      token: null,
      user: null,
      isAuthenticated: false,

      // Llamar después de un login exitoso
      // Decodifica el token para extraer los claims (role_name, company_id, etc.)
      setAuth: (token, userFromResponse) => {
        const claims = decodeToken(token);
        set({
          token,
          user: {
            ...userFromResponse,
            role_name:  claims?.role_name,   // ← del JWT, sin llamar a /user
            company_id: claims?.company_id,
          },
          isAuthenticated: true,
        });
      },

      logout: () => set({ token: null, user: null, isAuthenticated: false }),

      // Verificar que el token no esté expirado (chequeo local, sin red)
      isTokenValid: () => {
        const { token } = get();
        return token ? !isTokenExpired(token) : false;
      },

      // Verificar rol con jerarquía
      // admin puede hacer todo lo que hace user, pero no al revés
      hasRole: (requiredRole) => {
        const { user } = get();
        const hierarchy = {
          admin: ['admin', 'user'],
          user:  ['user'],
        };
        return hierarchy[user?.role_name]?.includes(requiredRole) ?? false;
      },

      // Útil para filtrar datos por empresa en los requests
      getCompanyId: () => get().user?.company_id ?? null,
    }),
    {
      name: 'dugrow-auth',
      // Persistir token + usuario para que el login sobreviva un F5
      partialize: (state) => ({
        token: state.token,
        user: state.user,
        isAuthenticated: state.isAuthenticated,
      }),
    }
  )
);

export default useAuthStore;
```

---

## 🛡️ **5. Protección de Rutas (AuthGuard)**

```javascript
// components/AuthGuard.jsx
import { useEffect } from 'react';
import { useRouter } from 'next/navigation'; // App Router de Next.js
import useAuthStore from '@/stores/authStore';

const AuthGuard = ({ children, requireRole = null }) => {
  const router = useRouter();
  const { isAuthenticated, isTokenValid, hasRole, logout } = useAuthStore();

  useEffect(() => {
    // Token expirado → limpiar sesión y redirigir
    if (isAuthenticated && !isTokenValid()) {
      logout();
      router.push('/login');
      return;
    }

    // No autenticado → login
    if (!isAuthenticated) {
      router.push('/login');
      return;
    }

    // Rol insuficiente → página de error
    if (requireRole && !hasRole(requireRole)) {
      router.push('/unauthorized');
    }
  }, [isAuthenticated, requireRole]);

  if (!isAuthenticated || !isTokenValid()) return null;

  return children;
};

export default AuthGuard;
```

---

## 🎨 **6. Uso en Componentes**

### **Proteger páginas completas (solo admins):**
```javascript
// app/dashboard/page.jsx
import AuthGuard from '@/components/AuthGuard';

export default function DashboardPage() {
  return (
    <AuthGuard requireRole="admin">
      <h1>Panel de Administración</h1>
      {/* Solo se renderiza si el usuario es admin */}
    </AuthGuard>
  );
}
```

### **Mostrar/ocultar elementos según rol:**
```javascript
import useAuthStore from '@/stores/authStore';

export default function ProductActions({ product }) {
  const { hasRole, getCompanyId } = useAuthStore();

  return (
    <div>
      {/* Todos los usuarios autenticados pueden ver */}
      <button>Ver Detalles</button>

      {/* Solo admins pueden editar o eliminar */}
      {hasRole('admin') && (
        <>
          <button>Editar</button>
          <button>Eliminar</button>
        </>
      )}
    </div>
  );
}
```

### **Filtrar datos por empresa del usuario:**
```javascript
import { apiClient } from '@/lib/apiClient';
import useAuthStore from '@/stores/authStore';

const { getCompanyId } = useAuthStore.getState();

// Los datos siempre se filtran por la empresa del usuario logueado
const productos = await apiClient(
  `${STOCK_API}/products?company_id=${getCompanyId()}`
);
```

---

## 📊 **7. Hook personalizado `useAuth`**

```javascript
// hooks/useAuth.js
import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { AUTH_API } from '@/lib/config';
import useAuthStore from '@/stores/authStore';

export const useAuth = () => {
  const store = useAuthStore();
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [error, setError]     = useState(null);

  const login = async ({ email, password, company_id }) => {
    setLoading(true);
    setError(null);
    try {
      const response = await fetch(`${AUTH_API}/token`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password, company_id }),
      });

      const data = await response.json();

      if (!response.ok) {
        // data.errors viene en errores de validación (422)
        throw new Error(data.message || 'Credenciales inválidas');
      }

      // Guardar token + usuario en el store (y decodificar claims del JWT)
      store.setAuth(data.access_token, data.user);
      router.push('/dashboard');
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    try {
      // TODO: llamar a POST /auth/v1/logout cuando esté implementado
      // await apiClient(`${AUTH_API}/logout`, { method: 'POST' });
    } catch {
      // ignorar errores de red en logout
    } finally {
      store.logout();
      router.push('/login');
    }
  };

  return { ...store, login, logout, loading, error };
};
```

---

## 🚨 **7. Manejo de Errores HTTP**

| Código | Significado | Acción Frontend |
|--------|-------------|-----------------|
| `200` | OK | Procesar datos |
| `201` | Creado | Mostrar éxito, actualizar lista |
| `400` | Bad Request | Mostrar error genérico |
| `401` | Token inválido/expirado | **Logout automático + Redirigir a login** |
| `403` | Sin permisos para la acción | Mostrar "No tiene permisos" |
| `422` | Error de validación | Mostrar errores específicos en formulario |
| `500` | Error interno | Mostrar "Error del servidor" |

---

## ✅ **8. Checklist de Implementación**

### Archivos a crear:
- [ ] `lib/config.js` — URLs base de las APIs + `.env.local`
- [ ] `lib/jwt.js` — `decodeToken()` + `isTokenExpired()`
- [ ] `lib/apiClient.js` — wrapper fetch con Bearer token automático
- [ ] `stores/authStore.js` — estado global con Zustand + métodos de rol
- [ ] `hooks/useAuth.js` — hook con login/logout y manejo de errores
- [ ] `components/AuthGuard.jsx` — HOC para proteger rutas

### Flujo a verificar:
- [ ] Login exitoso → store guarda token + user con claims del JWT
- [ ] F5 en página autenticada → sigue logueado (Zustand persist)
- [ ] Token expirado → logout automático al navegar
- [ ] Ruta con `requireRole="admin"` → redirige si no es admin
- [ ] Request autenticado → Bearer token se incluye automáticamente
- [ ] Response 401 del backend → logout + redirect a login
- [ ] Elementos condicionales por rol → `{hasRole('admin') && <button/>}`

### Estado de los endpoints del backend:
| Endpoint | Estado | Acción frontend |
|---|---|---|
| `POST /auth/v1/token` | ✅ Funcional | Login completo |
| `POST /auth/v1/signup` | ✅ Funcional | Registro completo |
| `GET /auth/v1/user` | ⏳ Pendiente | No necesario por ahora (usar claims del JWT) |
| `POST /auth/v1/logout` | ⏳ Pendiente | Hacer logout local igual, skip al backend |
| `POST /auth/v1/recover` | ⏳ Pendiente | No implementar hasta que esté el backend |

---

## 🧪 **9. Testing**

### **Probar en consola del navegador:**
```javascript
// 1. Hacer login
const response = await fetch('/api/auth/v1/token', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'admin@dugrow.com',
    password: 'Password123!',
    company_id: 1
  })
});

const { access_token } = await response.json();
console.log('Token:', access_token);

// 2. Probar request autenticado (cuando esté la API Stock)
const products = await fetch('/api/stock/v1/products', {
  headers: { 'Authorization': `Bearer ${access_token}` }
});

console.log('Products:', await products.json());
```

---

**¡Con esta guía el frontend estará listo para usar la nueva API de autenticación!** 
