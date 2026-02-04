# Cambio Temporal de Versión PHP en PowerShell

Este documento explica cómo ejecutar comandos Artisan usando PHP 8.2+ en una consola PowerShell de forma temporal, cuando el sistema tiene PHP 7.4 como versión predeterminada (WAMP).

---

## 🚨 **Descripción del Problema**

Al ejecutar comandos de Artisan con PHP 7.4, aparece el siguiente error:

```
Fatal error: Uncaught RuntimeException: Composer detected issues in your platform: 
Your Composer dependencies require a PHP version ">= 8.2.0". You are running 7.4.33.
```

Esto ocurre porque:
- El proyecto Laravel 11 requiere **PHP 8.2+**
- WAMP está configurado con **PHP 7.4** como versión predeterminada
- Composer verifica la compatibilidad de versiones antes de ejecutar

---

## 📋 **Requisitos Previos**

- ✅ Tener PHP 8.2+ instalado en: `C:\wamp64\bin\php\php8.2.26\`
- ✅ Consola PowerShell
- ✅ Proyecto Laravel en funcionamiento

---

## 🔧 **Procedimiento Paso a Paso**

### **1. Abrir PowerShell en la raíz del proyecto**
```powershell
cd C:\wamp64\www\dugrow-dashboard
```

### **2. Verificar versión actual de PHP**
```powershell
php -v
```
**Resultado esperado:**
```
PHP 7.4.33 (cli) (built: Nov  9 2022 08:09:12) ( NTS Visual C++ 2017 x64 )
```

### **3. Modificar temporalmente la variable PATH**
```powershell
$env:PATH="C:\wamp64\bin\php\php8.2.26;$env:PATH"
```

> ⚠️ **Importante:** Reemplazar `php8.2.26` por la versión exacta que tengas instalada.

### **4. Verificar que el cambio funcionó**
```powershell
php -v
```
**Resultado esperado:**
```
PHP 8.2.26 (cli) (built: Dec 10 2024 17:17:15) ( NTS Visual C++ 2019 x64 )
```

### **5. Ejecutar comandos Artisan normalmente**
```powershell
# Limpiar configuración
php artisan config:clear

# Generar cache de configuración  
php artisan config:cache

# Verificar configuración JWT
php artisan config:show jwt

# Otros comandos Artisan
php artisan migrate
php artisan db:seed
php artisan route:list
```

---

## 🎯 **Verificación de Funcionamiento**

### **Comando de prueba:**
```powershell
php artisan --version
```

### **Resultado exitoso:**
```
Laravel Framework 11.x.x
```

### **Si sigue fallando:**
```powershell
# Verificar qué PHP se está usando
where php

# Resultado esperado:
# C:\wamp64\bin\php\php8.2.26\php.exe
```

---

## ⚠️ **Consideraciones Importantes**

### **Alcance del Cambio**
- ✅ **Temporal**: Solo afecta la consola actual
- ✅ **Reversible**: Se pierde al cerrar PowerShell  
- ✅ **No invasivo**: No modifica configuración del sistema
- ✅ **Seguro**: No afecta otros proyectos PHP 7.4

### **Limitaciones**
- ❌ Se debe repetir en cada nueva consola PowerShell
- ❌ No afecta otros terminales (CMD, Git Bash, etc.)
- ❌ No persiste después de reiniciar

---

## 🛠️ **Troubleshooting**

### **Problema: Sigue usando PHP 7.4**
**Causa:** PATH no se modificó correctamente

**Solución:**
```powershell
# Verificar PATH actual
$env:PATH

# Debe mostrar al inicio:
# C:\wamp64\bin\php\php8.2.26;C:\wamp64\bin\php\php7.4.33;...

# Si no aparece, ejecutar nuevamente:
$env:PATH="C:\wamp64\bin\php\php8.2.26;$env:PATH"
```

### **Problema: PHP 8.2 no está instalado**
**Síntoma:**
```
'C:\wamp64\bin\php\php8.2.26\php.exe' is not recognized
```

**Solución:**
1. Verificar carpetas disponibles:
   ```powershell
   ls C:\wamp64\bin\php\
   ```
2. Usar la versión correcta en el comando PATH

### **Problema: Error de extensiones PHP**
**Síntoma:**
```
PHP Warning: PHP Startup: Unable to load dynamic library
```

**Solución:**
```powershell
# Usar configuración específica de WAMP
$env:PATH="C:\wamp64\bin\php\php8.2.26;$env:PATH"
$env:PHPRC="C:\wamp64\bin\apache\apache2.4.x\bin\php.ini"
```

---

## 📚 **Comandos Útiles de Referencia**

### **Configuración Laravel**
```powershell
php artisan config:clear    # Limpiar cache configuración
php artisan config:cache    # Generar cache configuración  
php artisan route:clear     # Limpiar cache rutas
php artisan route:cache     # Generar cache rutas
php artisan view:clear      # Limpiar cache vistas
```

### **Base de Datos**
```powershell
php artisan migrate         # Ejecutar migraciones
php artisan migrate:fresh   # Resetear DB + migraciones
php artisan db:seed         # Ejecutar seeders
php artisan migrate:fresh --seed # Reset completo + seeders
```

### **JWT**
```powershell
php artisan config:show jwt # Ver configuración JWT
php artisan jwt:secret      # Generar nueva secret key (cuidado!)
```

---

## 📝 **Script de Automatización (Opcional)**

Crear archivo `setup-php82.ps1` en la raíz:

```powershell
# setup-php82.ps1
Write-Host "🔧 Configurando PHP 8.2 para esta sesión..." -ForegroundColor Green

# Verificar versión actual
Write-Host "📋 Versión actual:" -ForegroundColor Yellow
php -v

# Cambiar PATH
$env:PATH="C:\wamp64\bin\php\php8.2.26;$env:PATH"

# Verificar cambio
Write-Host "`n✅ Nueva versión:" -ForegroundColor Green  
php -v

Write-Host "`n🚀 ¡Listo! Ahora puedes ejecutar comandos Artisan." -ForegroundColor Cyan
```

**Uso:**
```powershell
.\setup-php82.ps1
```

---

## ✅ **Checklist de Verificación**

- [ ] PHP 8.2+ instalado en WAMP
- [ ] PowerShell abierto en directorio del proyecto
- [ ] Variable PATH modificada correctamente  
- [ ] `php -v` muestra versión 8.2+
- [ ] `php artisan --version` funciona sin errores
- [ ] Comandos Artisan ejecutan correctamente

---

**Última actualización:** Febrero 2026  
**Proyecto:** Dugrow Dashboard API  
**Autor:** Equipo de Desarrollo
