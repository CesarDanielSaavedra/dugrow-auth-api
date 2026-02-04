# Guía de Autenticación para Frontend

Esta guía explica cómo el frontend debe interactuar con la API de Autenticación de Dugrow Dashboard.

---

## 🚀 **Cambios Principales**

### ✅ **Qué cambió:**
- El **JWT ahora contiene el rol** y datos del usuario
- **Menos requests** a la API para validar permisos
- **Mejor performance** en arquitectura distribuida

### ⚠️ **Qué NO cambió:**
- Las URLs de los endpoints siguen iguales
- El flujo de login sigue igual
- El manejo de errores 401/403 sigue igual

---

## 📋 **Base URLs**

```javascript
// API de Autenticación
const AUTH_API = 'https://tu-servidor.com/api/auth/v1';

// API de Stock (próximamente)
const STOCK_API = 'https://tu-servidor.com/api/stock/v1';
```

---

## 🔐 **1. Login (Obtener Token)**

### **Request:**
```javascript
const login = async (credentials) => {
  const response = await fetch(`${AUTH_API}/token`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      email: 'admin@dugrow.com',
      password: 'Password123!',
      company_id: 1
    })
  });

  if (!response.ok) {
    throw new Error('Credenciales inválidas');
  }

  return response.json();
};
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
    "role": {
      "id": 1,
      "name": "admin"
    }
  }
}
```

### **¡IMPORTANTE! Nuevo contenido del Token JWT:**

El token ahora incluye automáticamente:
```json
{
  "sub": 1,              // user_id
  "name": "Admin Dugrow",
  "email": "admin@dugrow.com", 
  "company_id": 1,
  "role_id": 1,
  "role_name": "admin",  // ← NUEVO: rol incluido
  "iat": 1704067200,     // issued at
  "exp": 1704153600      // expires at
}
```

---

## 🎯 **2. Uso del Token en Requests**

### **Ejemplo con Fetch:**
```javascript
const getProducts = async (token) => {
  const response = await fetch(`${STOCK_API}/products`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`, // ← Incluir siempre
    },
  });

  // Manejar errores de autenticación/autorización
  if (response.status === 401) {
    // Token inválido/expirado → Logout automático
    logout();
    window.location.href = '/login';
    return;
  }

  if (response.status === 403) {
    // Sin permisos → Mostrar mensaje
    throw new Error('No tiene permisos para esta acción');
  }

  return response.json();
};
```

### **Ejemplo con Axios:**
```javascript
// Configurar interceptor global
axios.defaults.baseURL = 'https://tu-servidor.com/api';

// Agregar token automáticamente
axios.interceptors.request.use((config) => {
  const token = getTokenFromStorage();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Manejar errores automáticamente  
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      logout();
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

---

## 🧠 **3. Gestión del Estado (Zustand/Redux)**

### **Store de Autenticación:**
```javascript
// authStore.js
import { create } from 'zustand';
import { persist } from 'zustand/middleware';

const useAuthStore = create(
  persist(
    (set, get) => ({
      // Estado inicial
      token: null,
      user: null,
      isAuthenticated: false,

      // Acciones
      setAuth: (token, user) => set({ 
        token, 
        user, 
        isAuthenticated: true 
      }),

      logout: () => set({ 
        token: null, 
        user: null, 
        isAuthenticated: false 
      }),

      // ✨ NUEVO: Obtener rol desde el usuario
      getUserRole: () => {
        const { user } = get();
        return user?.role?.name || 'guest';
      },

      // ✨ NUEVO: Verificar si tiene permiso
      hasRole: (requiredRole) => {
        const { user } = get();
        const userRole = user?.role?.name;
        
        // Definir jerarquía de roles
        const roleHierarchy = {
          'admin': ['admin', 'user'],
          'user': ['user']
        };
        
        return roleHierarchy[userRole]?.includes(requiredRole) || false;
      },
    }),
    {
      name: 'auth-storage',
      // Solo persistir datos esenciales (no el token por seguridad)
      partialize: (state) => ({ 
        user: state.user,
        isAuthenticated: state.isAuthenticated 
      }),
    }
  )
);

export default useAuthStore;
```

---

## 🛡️ **4. Componente de Protección de Rutas**

```javascript
// components/AuthGuard.jsx
import { useEffect } from 'react';
import { useRouter } from 'next/router';
import useAuthStore from '../stores/authStore';

const AuthGuard = ({ children, requireRole = null }) => {
  const router = useRouter();
  const { isAuthenticated, hasRole } = useAuthStore();

  useEffect(() => {
    // Si no está autenticado, redirigir a login
    if (!isAuthenticated) {
      router.push('/login');
      return;
    }

    // Si requiere un rol específico y no lo tiene
    if (requireRole && !hasRole(requireRole)) {
      router.push('/unauthorized');
      return;
    }
  }, [isAuthenticated, requireRole, router, hasRole]);

  // Mostrar loading mientras verifica
  if (!isAuthenticated) {
    return <div>Cargando...</div>;
  }

  // Mostrar contenido si está autenticado y tiene permisos
  return children;
};

export default AuthGuard;
```

---

## 🎨 **5. Uso en Componentes**

### **Proteger páginas completas:**
```javascript
// pages/products.jsx
import AuthGuard from '../components/AuthGuard';

const ProductsPage = () => {
  return (
    <AuthGuard requireRole="admin">
      <div>
        <h1>Gestión de Productos</h1>
        {/* Contenido solo para admins */}
      </div>
    </AuthGuard>
  );
};

export default ProductsPage;
```

### **Mostrar/ocultar elementos según rol:**
```javascript
// components/ProductActions.jsx
import useAuthStore from '../stores/authStore';

const ProductActions = ({ product }) => {
  const { hasRole } = useAuthStore();

  return (
    <div>
      {/* Todos pueden ver */}
      <button>Ver Detalles</button>
      
      {/* Solo admins pueden editar/eliminar */}
      {hasRole('admin') && (
        <>
          <button>Editar</button>
          <button>Eliminar</button>
        </>
      )}
    </div>
  );
};
```

---

## 📊 **6. Ejemplo Completo de Hook Personalizado**

```javascript
// hooks/useAuth.js
import { useState, useEffect } from 'react';
import useAuthStore from '../stores/authStore';

export const useAuth = () => {
  const store = useAuthStore();
  const [loading, setLoading] = useState(true);

  // Función de login
  const login = async (credentials) => {
    try {
      setLoading(true);
      const response = await fetch(`${AUTH_API}/token`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(credentials)
      });

      if (!response.ok) {
        throw new Error('Credenciales inválidas');
      }

      const data = await response.json();
      
      // Guardar en store
      store.setAuth(data.access_token, data.user);
      
      return data;
    } catch (error) {
      throw error;
    } finally {
      setLoading(false);
    }
  };

  // Función de logout
  const logout = async () => {
    try {
      // Opcional: Invalidar token en el backend
      await fetch(`${AUTH_API}/logout`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${store.token}`,
        },
      });
    } catch (error) {
      console.warn('Error al hacer logout:', error);
    } finally {
      // Limpiar store local
      store.logout();
    }
  };

  useEffect(() => {
    setLoading(false);
  }, []);

  return {
    ...store,
    login,
    logout,
    loading,
  };
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

- [ ] Actualizar función de login para manejar nueva response
- [ ] Configurar interceptors HTTP para incluir token automáticamente  
- [ ] Implementar manejo de errores 401/403
- [ ] Crear componente AuthGuard para proteger rutas
- [ ] Actualizar store de autenticación con métodos de rol
- [ ] Probar flujo completo: login → request → logout
- [ ] Verificar que el token se incluye en todos los requests a APIs

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

¿Necesitas que detalle algo específico o prefieres que sigamos con la implementación de la API de Stock?
