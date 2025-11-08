# API de Productos - Roelplant

## Tabla de Contenidos
- [Autenticación](#autenticación)
  - [Login](#1-login)
  - [Renovar Token](#2-renovar-token-refresh)
  - [Logout](#3-logout-cerrar-sesión)
- [Endpoints de Productos](#endpoints-de-productos)
  - [Buscar Producto](#1-buscar-producto-por-nombre)
  - [Listar Disponibles](#2-listar-productos-disponibles)
- [Cálculo de Stock](#cálculo-de-stock-disponible)

---

## Autenticación

La API utiliza tokens JWT almacenados en la base de datos. Existen dos tipos de usuarios:
- **Usuarios internos**: Login con `username` (tabla `usuarios`)
- **Clientes**: Login con `email` (tabla `clientes`)

### Flujo de Autenticación

1. **Login** → Obtener `access_token` y `refresh_token`
2. **Usar access_token** en todas las peticiones (válido 24 horas)
3. **Renovar con refresh_token** cuando expire el access_token (válido 30 días)
4. **Logout** para revocar todos los tokens

---

## Endpoints de Autenticación

### 1. Login

**Endpoint:** `/api/login.php`

**Método:** `POST`

**Body (JSON):**
```json
{
  "username": "admin",
  "password": "tu_password"
}
```

O para clientes:
```json
{
  "username": "cliente@email.com",
  "password": "password_cliente"
}
```

**Ejemplo de petición:**
```bash
curl -X POST "https://control.roelplant.cl/api/login.php" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "SergioVM"
  }'
```

**Respuesta exitosa (Usuario interno):**
```json
{
  "status": "ok",
  "message": "Login exitoso",
  "user": {
    "id": 1,
    "username": "admin",
    "nombre_real": "Administrador",
    "tipo_usuario": 1
  },
  "user_type": "usuario",
  "tokens": {
    "access_token": "a1b2c3d4e5f6...",
    "refresh_token": "f6e5d4c3b2a1...",
    "access_expires_at": "2025-01-10 15:30:00",
    "refresh_expires_at": "2025-02-08 15:30:00",
    "token_type": "Bearer"
  }
}
```

**Respuesta exitosa (Cliente):**
```json
{
  "status": "ok",
  "message": "Login exitoso",
  "user": {
    "id": 123,
    "nombre": "Cliente Ejemplo",
    "email": "cliente@email.com"
  },
  "user_type": "cliente",
  "tokens": {
    "access_token": "...",
    "refresh_token": "...",
    "access_expires_at": "2025-01-10 15:30:00",
    "refresh_expires_at": "2025-02-08 15:30:00",
    "token_type": "Bearer"
  }
}
```

**Errores posibles:**
```json
// Credenciales incorrectas
{
  "status": "error",
  "message": "Credenciales inválidas"
}

// Usuario inhabilitado
{
  "status": "error",
  "message": "Usuario inhabilitado"
}

// Cliente inactivo
{
  "status": "error",
  "message": "Cliente inactivo"
}
```

---

### 2. Renovar Token (Refresh)

**Endpoint:** `/api/refresh.php`

**Método:** `POST`

**Body (JSON):**
```json
{
  "refresh_token": "f6e5d4c3b2a1..."
}
```

**Ejemplo de petición:**
```bash
curl -X POST "https://control.roelplant.cl/api/refresh.php" \
  -H "Content-Type: application/json" \
  -d '{
    "refresh_token": "f6e5d4c3b2a1..."
  }'
```

**Respuesta exitosa:**
```json
{
  "status": "ok",
  "message": "Token renovado exitosamente",
  "tokens": {
    "access_token": "nuevo_access_token...",
    "access_expires_at": "2025-01-11 16:00:00",
    "token_type": "Bearer"
  }
}
```

**Errores posibles:**
```json
// Refresh token inválido
{
  "status": "error",
  "message": "Refresh token inválido"
}

// Refresh token expirado
{
  "status": "error",
  "message": "Refresh token expirado"
}

// Refresh token revocado
{
  "status": "error",
  "message": "Refresh token revocado"
}
```

---

### 3. Logout (Cerrar Sesión)

**Endpoint:** `/api/logout.php`

**Método:** `POST`

**Headers:**
```
Authorization: Bearer {access_token}
```

**Ejemplo de petición:**
```bash
curl -X POST "https://control.roelplant.cl/api/logout.php" \
  -H "Authorization: Bearer a1b2c3d4e5f6..."
```

**Respuesta exitosa:**
```json
{
  "status": "ok",
  "message": "Sesión cerrada exitosamente",
  "tokens_revoked": 2
}
```

**Nota:** Revoca TODOS los tokens (access y refresh) del usuario.

---

## Uso de Tokens en Peticiones

Una vez obtenido el `access_token`, inclúyelo en el header `Authorization` de todas las peticiones:

```
Authorization: Bearer {access_token}
```

**Ejemplo:**
```bash
curl -X GET "https://control.roelplant.cl/api/producto.php?nombre=dolar" \
  -H "Authorization: Bearer a1b2c3d4e5f6..."
```

### Validación de Tokens

El sistema verifica que:
- El token exista en la tabla `auth_tokens`
- Sea de tipo `access` (no `refresh`)
- No esté revocado (`revoked = 0`)
- No esté expirado (`expires_at > NOW()`)

### Respuestas de Error de Autenticación

**401 - No autorizado:**
```json
{
  "status": "error",
  "message": "Token de autenticación no proporcionado"
}
```

```json
{
  "status": "error",
  "message": "Token no válido"
}
```

```json
{
  "status": "error",
  "message": "Token expirado"
}
```

```json
{
  "status": "error",
  "message": "Token revocado"
}
```

---

## Endpoints de Productos

## Endpoints

### 1. Buscar Producto por Nombre

**Endpoint:** `/api/producto.php`

**Método:** `GET` o `POST`

**Parámetros:**
- `nombre` (requerido): Nombre del producto a buscar
- `limit` (opcional): Número de resultados por página (1-50, default: 10)
- `offset` (opcional): Offset para paginación (default: 0)
- `debug` (opcional): Activar modo debug (`?debug=1`)

**Ejemplo de petición:**
```bash
curl -X GET "https://control.roelplant.cl/api/producto.php?nombre=dolar&limit=10" \
  -H "Authorization: Bearer tu_token_aqui"
```

**Respuesta exitosa:**
```json
{
  "status": "ok",
  "total": 3,
  "limit": 10,
  "offset": 0,
  "productos": [
    {
      "id_variedad": 1300,
      "nombre": "DOLAR VARIEGADO",
      "referencia": "E458",
      "tipo_planta": "INTERIOR",
      "descripcion": "Planta de interior...",
      "imagen_url": "https://control.roelplant.cl/uploads/variedades/variedad_1300_abc123.jpeg",
      "precios": {
        "detalle": {
          "neto": 2500,
          "iva": 475,
          "bruto": 2975
        },
        "mayorista": {
          "neto": 2000,
          "iva": 380,
          "bruto": 2380
        }
      },
      "stock": {
        "disponible_para_reservar": 319,
        "unidad": "plantines"
      },
      "coincidencia": {
        "criterio": "exacta",
        "buscado": "dolar"
      }
    }
  ],
  "producto": {
    // ... primer producto (para compatibilidad)
  }
}
```

**Respuesta sin resultados:**
```json
{
  "status": "not_found",
  "total": 0,
  "limit": 10,
  "offset": 0,
  "productos": []
}
```

---

### 2. Listar Productos Disponibles

**Endpoint:** `/api/disponibles.php`

**Método:** `GET`

**Parámetros:**
- `tipo` (opcional): Filtrar por tipo (`interior`, `exterior`)
- `q` (opcional): Búsqueda por nombre
- `limit` (opcional): Número de resultados (1-1000, default: 200, o `all` para 1000)
- `offset` (opcional): Offset para paginación (default: 0)
- `debug` (opcional): Activar modo debug (`?debug=1`)

**Ejemplo de petición:**
```bash
curl -X GET "https://control.roelplant.cl/api/disponibles.php?tipo=interior&limit=50" \
  -H "Authorization: Bearer tu_token_aqui"
```

**Respuesta exitosa:**
```json
{
  "status": "ok",
  "tipo": "interior",
  "count": 50,
  "items": [
    {
      "id_variedad": 1300,
      "nombre": "DOLAR VARIEGADO",
      "referencia": "E458",
      "tipo_planta": "INTERIOR",
      "descripcion": "Planta de interior...",
      "precios": {
        "detalle_bruto": 2975,
        "mayorista_bruto": 2380
      },
      "stock": 319,
      "imagen_url": "https://control.roelplant.cl/uploads/variedades/nombre_archivo.jpeg"
    }
  ]
}
```

---

## Cálculo de Stock Disponible

El stock disponible para reservar se calcula como:

```
disponible_para_reservar = stock_total - reservas_pendientes - entregas_completadas
```

Donde:
- **stock_total**: Suma de todas las cantidades en `stock_productos` para la variedad
- **reservas_pendientes**: Suma de reservas con estado 0 o 1 (pendiente o en proceso)
- **entregas_completadas**: Suma de entregas de reservas con estado 2 (entregado)

Este cálculo garantiza que:
1. No se reserve más de lo disponible
2. Se consideren tanto las reservas activas como las entregas ya realizadas
3. Los valores coincidan exactamente con el sistema de gestión interna

---

## Códigos de Estado HTTP

- `200 OK`: Petición exitosa
- `400 Bad Request`: Parámetros incorrectos
- `401 Unauthorized`: Token inválido, expirado o no proporcionado
- `500 Internal Server Error`: Error del servidor

---

## Conexión a Base de Datos

La API usa el mismo sistema de conexión que el resto de la aplicación mediante `class_lib/class_conecta_mysql.php`, que lee la configuración del archivo `.env`.

---

## Modo Debug

Agregar `?debug=1` a cualquier endpoint para obtener mensajes de error detallados (útil en desarrollo):

```bash
curl -X GET "https://control.roelplant.cl/api/producto.php?nombre=test&debug=1" \
  -H "Authorization: Bearer tu_token_aqui"
```

**Nota:** En producción, el modo debug debería estar deshabilitado por seguridad.
