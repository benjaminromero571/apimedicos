# CRUD Historial Cuidador - Documentación

## 📋 Descripción

Sistema CRUD completo para gestión de historiales de cuidadores siguiendo arquitectura limpia con PHP 8.2+.

## 🏗️ Arquitectura

El sistema sigue el patrón Repository con separación clara de capas:

```
┌─────────────────┐
│   Endpoints     │  ← Rutas HTTP
└────────┬────────┘
         │
┌────────▼────────┐
│  Controllers    │  ← Validación HTTP, Códigos de estado
└────────┬────────┘
         │
┌────────▼────────┐
│   Services      │  ← Lógica de negocio, Validaciones
└────────┬────────┘
         │
┌────────▼────────┐
│  Repositories   │  ← Acceso a datos, Queries SQL
└────────┬────────┘
         │
┌────────▼────────┐
│   Entities      │  ← Modelos de datos puros
└─────────────────┘
         │
┌────────▼────────┐
│      DTOs       │  ← Transferencia y validación de datos
└─────────────────┘
```

## 📁 Estructura de Archivos

```
api/
├── entities/
│   └── HistorialCuidadorEntity.php          # Modelo de datos
├── dto/
│   ├── CreateHistorialCuidadorDto.php       # DTO para crear
│   ├── HistorialCuidadorDto.php             # DTO básico
│   ├── HistorialCuidadorDetailDto.php       # DTO detallado
│   └── HistorialCuidadorSearchDto.php       # DTO para búsquedas
├── repositories/
│   └── HistorialCuidadorRepository.php      # Acceso a datos
├── services/
│   └── HistorialCuidadorService.php         # Lógica de negocio
├── controllers/
│   └── HistorialCuidadorController.php      # Controlador REST
├── endpoints/
│   └── historiales_cuidador.php             # Rutas
└── tests/
    ├── HistorialCuidadorRepositoryTest.php  # Tests Repository
    └── HistorialCuidadorServiceTest.php     # Tests Service
```

## 🔐 Permisos

| Acción | Administrador | Cuidador | Otros Roles |
|--------|--------------|----------|-------------|
| **Leer** (GET) | ✅ | ✅ | ✅ |
| **Crear** (POST) | ✅ | ✅ | ❌ |
| **Editar** (PUT) | ✅ | ✅ | ❌ |
| **Eliminar** (DELETE) | ✅ | ❌ | ❌ |

## 🚀 API Endpoints

### Base URL
```
http://tu-servidor/api
```

### Autenticación
Todos los endpoints requieren autenticación JWT en el header:
```
Authorization: Bearer {token}
```

---

### 1. **Listar Todos los Historiales**
```http
GET /historiales-cuidador?limit=10&offset=0
```

**Query Parameters:**
- `limit` (opcional): Número de registros por página
- `offset` (opcional): Desplazamiento para paginación

**Response 200:**
```json
{
  "success": true,
  "message": "Historiales obtenidos correctamente",
  "data": [
    {
      "id": 1,
      "fecha_historial": "2024-01-15 10:30:00",
      "detalle": "Paciente mostró mejoría en movilidad",
      "id_paciente": 5,
      "id_cuidador": 3,
      "nombre_paciente": "Juan Pérez García",
      "nombre_cuidador": "María López",
      "created_at": "2024-01-15 10:30:00"
    }
  ],
  "total": 25
}
```

---

### 2. **Obtener Historial por ID**
```http
GET /historiales-cuidador/{id}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Historial obtenido correctamente",
  "data": {
    "id": 1,
    "fecha_historial": "2024-01-15 10:30:00",
    "detalle": "Paciente mostró mejoría en movilidad",
    "paciente": {
      "id": 5,
      "nombre": "Juan Pérez García"
    },
    "cuidador": {
      "id": 3,
      "nombre": "María López Martínez"
    },
    "auditoria": {
      "created_at": "2024-01-15 10:30:00",
      "created_by": {
        "id": 3,
        "nombre": "María López"
      },
      "updated_at": "2024-01-15 10:30:00",
      "updated_by": {
        "id": 3,
        "nombre": "María López"
      }
    }
  }
}
```

---

### 3. **Obtener Historiales por Paciente**
```http
GET /historiales-cuidador/paciente/{id}?limit=10&offset=0
```

---

### 4. **Obtener Historiales por Cuidador**
```http
GET /historiales-cuidador/cuidador/{id}?limit=10&offset=0
```

---

### 5. **Buscar con Filtros**
```http
GET /historiales-cuidador/buscar?id_paciente=5&fecha_desde=2024-01-01&fecha_hasta=2024-12-31
```

**Query Parameters:**
- `id_paciente` (opcional): Filtrar por paciente
- `id_cuidador` (opcional): Filtrar por cuidador
- `fecha_desde` (opcional): Fecha inicio (Y-m-d)
- `fecha_hasta` (opcional): Fecha fin (Y-m-d)
- `detalle` (opcional): Búsqueda parcial en detalle
- `limit` (opcional, default: 50)
- `offset` (opcional, default: 0)
- `order_by` (opcional, default: fecha_historial)
- `order_direction` (opcional, default: DESC)

**Response 200:**
```json
{
  "success": true,
  "message": "Búsqueda realizada correctamente",
  "data": [...],
  "pagination": {
    "limit": 10,
    "offset": 0,
    "total": 45
  }
}
```

---

### 6. **Crear Historial**
```http
POST /historiales-cuidador
Content-Type: application/json
```

**Permisos:** Administrador, Cuidador

**Request Body:**
```json
{
  "detalle": "Paciente realizó ejercicios de rehabilitación sin complicaciones",
  "id_paciente": 5,
  "id_cuidador": 3,
  "fecha_historial": "2024-01-15 10:30:00"  // Opcional, usa timestamp actual si se omite
}
```

**Validaciones:**
- `detalle`: Requerido, 5-255 caracteres
- `id_paciente`: Requerido, debe existir en BD
- `id_cuidador`: Requerido, debe existir en BD
- `fecha_historial`: Opcional, formato Y-m-d o Y-m-d H:i:s

**Response 201:**
```json
{
  "success": true,
  "message": "Historial creado exitosamente",
  "data": {
    "id": 123,
    ...
  }
}
```

---

### 7. **Actualizar Historial**
```http
PUT /historiales-cuidador/{id}
Content-Type: application/json
```

**Permisos:** Administrador, Cuidador

**Request Body:**
```json
{
  "detalle": "Actualización: Paciente mostró excelente progreso",
  "fecha_historial": "2024-01-15 11:00:00"
}
```

**Campos editables:**
- `detalle`
- `fecha_historial`

**Response 200:**
```json
{
  "success": true,
  "message": "Historial actualizado exitosamente",
  "data": {...}
}
```

---

### 8. **Eliminar Historial**
```http
DELETE /historiales-cuidador/{id}
```

**Permisos:** Solo Administrador

**Response 200:**
```json
{
  "success": true,
  "message": "Historial eliminado exitosamente"
}
```

---

### 9. **Estadísticas por Paciente**
```http
GET /historiales-cuidador/estadisticas/paciente/{id}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Estadísticas obtenidas correctamente",
  "data": {
    "total_registros": 15,
    "total_cuidadores": 3,
    "ultimo_registro": {
      "id": 123,
      "fecha_historial": "2024-01-15 10:30:00",
      "detalle": "...",
      ...
    }
  }
}
```

---

## 🧪 Pruebas Unitarias

### Ejecutar Tests del Repository
```bash
php tests/HistorialCuidadorRepositoryTest.php
```

### Ejecutar Tests del Service
```bash
php tests/HistorialCuidadorServiceTest.php
```

**Nota:** Los tests requieren:
- Base de datos configurada
- Al menos un paciente y un usuario/cuidador con ID 1
- Permisos de escritura en la BD

---

## 💾 Tabla de Base de Datos

```sql
CREATE TABLE `historial_cuidador` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_historial` date NOT NULL DEFAULT current_timestamp(),
  `detalle` varchar(255) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_cuidador` int(11) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  `updated_at` date NOT NULL DEFAULT current_timestamp(),
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 📝 Ejemplos de Uso

### Ejemplo 1: Crear un registro desde el frontend
```javascript
const response = await fetch('http://api/historiales-cuidador', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify({
    detalle: 'Paciente realizó sesión de fisioterapia',
    id_paciente: 5,
    id_cuidador: 3
  })
});

const result = await response.json();
console.log(result);
```

### Ejemplo 2: Buscar historiales de un paciente
```javascript
const response = await fetch(
  'http://api/historiales-cuidador/paciente/5?limit=10',
  {
    headers: { 'Authorization': `Bearer ${token}` }
  }
);

const result = await response.json();
console.log(result.data); // Array de historiales
```

---

## 🔧 Características Técnicas

- ✅ **PHP 8.2+** con tipado estricto
- ✅ **Arquitectura limpia** (Repository Pattern)
- ✅ **Separación de capas** (Controller → Service → Repository)
- ✅ **DTOs** para validación y transferencia de datos
- ✅ **Entidades** con getters/setters
- ✅ **Manejo de errores** personalizado
- ✅ **Respuestas JSON** estructuradas
- ✅ **Códigos HTTP** correctos
- ✅ **Inyección de dependencias**
- ✅ **Pruebas unitarias** básicas
- ✅ **Comentarios** explicativos

---

## 🔒 Manejo de Errores

Todos los endpoints retornan respuestas estructuradas:

**Error de Validación (400):**
```json
{
  "success": false,
  "message": "Error de validación: El detalle debe tener al menos 5 caracteres",
  "data": null
}
```

**No Encontrado (404):**
```json
{
  "success": false,
  "message": "Historial no encontrado",
  "data": null
}
```

**Sin Permisos (403):**
```json
{
  "success": false,
  "message": "No tiene permisos para crear historiales",
  "data": null
}
```

---

## 👨‍💻 Mantenimiento

Para agregar nuevas funcionalidades:

1. **Entity**: Agregar propiedades y getters/setters
2. **DTOs**: Crear DTOs para nuevos casos de uso
3. **Repository**: Agregar queries necesarias
4. **Service**: Implementar lógica de negocio
5. **Controller**: Crear endpoints HTTP
6. **Endpoints**: Registrar rutas
7. **Tests**: Agregar tests unitarios

---

## 📚 Recursos

- [PHP 8.2 Documentation](https://www.php.net/releases/8.2/en.php)
- [Repository Pattern](https://martinfowler.com/eaaCatalog/repository.html)
- [Clean Architecture](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [REST API Best Practices](https://restfulapi.net/)
