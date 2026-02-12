# Indicaciones Médicas - Documentación de Endpoints

## Descripción

Módulo CRUD para gestionar las indicaciones médicas de los pacientes. Las indicaciones son instrucciones o recomendaciones emitidas por médicos o profesionales de salud para los pacientes del sistema.

## Tabla de Base de Datos

```sql
CREATE TABLE `indicaciones_medicas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `indicaciones` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
```

## Permisos por Rol

| Acción | Administrador | Médico | Profesional | Cuidador |
|--------|:------------:|:------:|:-----------:|:--------:|
| **Crear** | ✅ | ✅ | ✅ | ❌ |
| **Leer** | ✅ (todas) | ✅ (todas) | ✅ (todas) | ✅ (solo pacientes asignados) |
| **Actualizar** | ✅ (todas) | ✅ (solo propias) | ✅ (solo propias) | ❌ |
| **Eliminar** | ✅ (todas) | ✅ (solo propias) | ✅ (solo propias) | ❌ |

> **Nota sobre Cuidadores**: Los cuidadores solo pueden ver indicaciones de los pacientes que tienen asignados a través de la tabla `asignaciones`.

> **Nota sobre `user_id`**: El campo `user_id` se asigna automáticamente desde el token JWT del usuario autenticado al crear una indicación. No es necesario enviarlo en el body de la petición.

---

## Endpoints

### Base URL

```
/apimedicos/indicaciones-medicas
```

---

### 1. Listar todas las indicaciones

```
GET /indicaciones-medicas
```

**Descripción**: Obtiene todas las indicaciones médicas con paginación opcional. Los cuidadores solo ven indicaciones de sus pacientes asignados.

**Query Parameters**:

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|:---------:|-------------|
| `limit` | int | No | Número máximo de resultados |
| `offset` | int | No | Desplazamiento (default: 0) |

**Respuesta exitosa** (200):

```json
{
  "success": true,
  "message": "Indicaciones obtenidas correctamente",
  "data": {
    "indicaciones": [
      {
        "id": 1,
        "paciente_id": 10,
        "user_id": 5,
        "indicaciones": "Tomar medicamento cada 8 horas...",
        "nombre_paciente": "Juan Pérez",
        "nombre_user": "Dr. García",
        "created_at": "2026-02-12 10:30:00"
      }
    ],
    "total": 50,
    "showing": 10
  }
}
```

---

### 2. Obtener indicación por ID

```
GET /indicaciones-medicas/{id}
```

**Descripción**: Obtiene una indicación médica con todos sus detalles, incluyendo datos del paciente, usuario y auditoría. Los cuidadores solo pueden acceder si el paciente está asignado a ellos.

**Path Parameters**:

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `id` | int | ID de la indicación |

**Respuesta exitosa** (200):

```json
{
  "success": true,
  "message": "Indicación obtenida correctamente",
  "data": {
    "id": 1,
    "indicaciones": "Tomar medicamento cada 8 horas con alimentos...",
    "paciente": {
      "id": 10,
      "nombre": "Juan Pérez"
    },
    "usuario": {
      "id": 5,
      "nombre": "Dr. García",
      "email": "garcia@hospital.cl"
    },
    "auditoria": {
      "created_at": "2026-02-12 10:30:00",
      "created_by": 5,
      "created_by_name": "Dr. García",
      "updated_at": "2026-02-12 10:30:00",
      "updated_by": 5,
      "updated_by_name": "Dr. García"
    }
  }
}
```

**Errores**:
- `400` — ID inválido
- `403` — Acceso denegado (cuidador sin asignación al paciente)
- `404` — Indicación no encontrada

---

### 3. Obtener indicaciones por paciente

```
GET /indicaciones-medicas/paciente/{id}
```

**Descripción**: Obtiene todas las indicaciones de un paciente específico. Los cuidadores solo pueden consultar pacientes que tengan asignados.

**Path Parameters**:

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `id` | int | ID del paciente |

**Query Parameters**:

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|:---------:|-------------|
| `limit` | int | No | Número máximo de resultados |
| `offset` | int | No | Desplazamiento (default: 0) |

**Respuesta exitosa** (200):

```json
{
  "success": true,
  "message": "Indicaciones del paciente obtenidas correctamente",
  "data": {
    "indicaciones": [...],
    "total": 15,
    "showing": 10
  }
}
```

**Errores**:
- `400` — ID inválido
- `403` — Acceso denegado (cuidador sin asignación al paciente)
- `404` — Paciente no existe

---

### 4. Buscar indicaciones

```
GET /indicaciones-medicas/buscar
```

**Descripción**: Búsqueda avanzada con múltiples filtros. Los cuidadores solo ven resultados de sus pacientes asignados.

**Query Parameters**:

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|:---------:|-------------|
| `paciente_id` | int | No | Filtrar por ID de paciente |
| `user_id` | int | No | Filtrar por ID de usuario que creó la indicación |
| `fecha_desde` | string | No | Fecha inicio (formato: `YYYY-MM-DD`) |
| `fecha_hasta` | string | No | Fecha fin (formato: `YYYY-MM-DD`) |
| `indicaciones` | string | No | Búsqueda parcial en el texto de indicaciones |
| `limit` | int | No | Número máximo de resultados |
| `offset` | int | No | Desplazamiento (default: 0) |

**Ejemplo**:

```
GET /indicaciones-medicas/buscar?paciente_id=10&fecha_desde=2026-01-01&limit=20
```

**Respuesta exitosa** (200):

```json
{
  "success": true,
  "message": "Búsqueda completada correctamente",
  "data": {
    "indicaciones": [...],
    "total": 5,
    "showing": 5
  }
}
```

**Errores**:
- `400` — Error de validación en filtros (formato de fecha, enteros inválidos, rango de fechas)
- `403` — Acceso denegado (cuidador intentando filtrar paciente no asignado)

---

### 5. Crear indicación médica

```
POST /indicaciones-medicas
```

**Descripción**: Crea una nueva indicación médica. El campo `user_id` se asigna automáticamente desde el JWT del usuario autenticado. Solo disponible para Administrador, Médico y Profesional.

**Headers**:

```
Authorization: Bearer <token>
Content-Type: application/json
```

**Body**:

| Campo | Tipo | Requerido | Descripción |
|-------|------|:---------:|-------------|
| `paciente_id` | int | ✅ | ID del paciente |
| `indicaciones` | string | ✅ | Texto de las indicaciones (mínimo 10 caracteres) |

**Ejemplo de body**:

```json
{
  "paciente_id": 10,
  "indicaciones": "Tomar paracetamol 500mg cada 8 horas durante 5 días. Reposo relativo."
}
```

**Respuesta exitosa** (201):

```json
{
  "success": true,
  "message": "Indicación médica creada exitosamente",
  "data": {
    "id": 1,
    "indicaciones": "Tomar paracetamol 500mg cada 8 horas durante 5 días. Reposo relativo.",
    "paciente": {
      "id": 10,
      "nombre": "Juan Pérez"
    },
    "usuario": {
      "id": 5,
      "nombre": "Dr. García",
      "email": "garcia@hospital.cl"
    },
    "auditoria": {
      "created_at": "2026-02-12 10:30:00",
      "created_by": 5,
      "created_by_name": "Dr. García",
      "updated_at": "2026-02-12 10:30:00",
      "updated_by": 5,
      "updated_by_name": "Dr. García"
    }
  }
}
```

**Errores**:
- `400` — Error de validación (campos faltantes, indicaciones muy cortas, paciente no existe)
- `403` — Acceso denegado (rol sin permisos de creación)

---

### 6. Actualizar indicación médica

```
PUT /indicaciones-medicas/{id}
```

**Descripción**: Actualiza una indicación existente. El Administrador puede editar cualquier indicación. Médicos y Profesionales solo pueden editar las indicaciones que ellos crearon (`user_id` coincide).

**Path Parameters**:

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `id` | int | ID de la indicación |

**Body** (todos los campos son opcionales):

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `indicaciones` | string | Nuevo texto de las indicaciones (mínimo 10 caracteres) |
| `paciente_id` | int | Nuevo ID de paciente |

**Ejemplo de body**:

```json
{
  "indicaciones": "Actualización: Tomar ibuprofeno 400mg cada 12 horas. Suspender paracetamol."
}
```

**Respuesta exitosa** (200):

```json
{
  "success": true,
  "message": "Indicación actualizada exitosamente",
  "data": { ... }
}
```

**Errores**:
- `400` — Error de validación o sin datos para actualizar
- `403` — Acceso denegado (no es propietario ni administrador)
- `404` — Indicación no encontrada

---

### 7. Eliminar indicación médica

```
DELETE /indicaciones-medicas/{id}
```

**Descripción**: Elimina una indicación médica. El Administrador puede eliminar cualquier indicación. Médicos y Profesionales solo pueden eliminar las indicaciones que ellos crearon.

**Path Parameters**:

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `id` | int | ID de la indicación |

**Respuesta exitosa** (200):

```json
{
  "success": true,
  "message": "Indicación eliminada exitosamente",
  "data": null
}
```

**Errores**:
- `400` — ID inválido
- `403` — Acceso denegado (no es propietario ni administrador, o rol Cuidador)
- `404` — Indicación no encontrada

---

## Arquitectura de Archivos

| Archivo | Descripción |
|---------|-------------|
| `sql/migration_indicaciones_medicas.sql` | Script de creación de la tabla |
| `entities/IndicacionMedicaEntity.php` | Entidad pura de datos |
| `dto/CreateIndicacionMedicaDto.php` | DTO de validación para creación |
| `dto/IndicacionMedicaDto.php` | DTO para listados |
| `dto/IndicacionMedicaDetailDto.php` | DTO detallado con joins |
| `dto/IndicacionMedicaSearchDto.php` | DTO de filtros de búsqueda |
| `repositories/IndicacionMedicaRepository.php` | Acceso a datos (queries SQL) |
| `services/IndicacionMedicaService.php` | Lógica de negocio y autorización |
| `controllers/IndicacionMedicaController.php` | Controlador HTTP REST |
| `endpoints/indicaciones_medicas.php` | Registro de rutas |

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| `200` | Operación exitosa |
| `201` | Recurso creado exitosamente |
| `400` | Error de validación o datos inválidos |
| `401` | No autenticado |
| `403` | Sin permisos para la operación |
| `404` | Recurso no encontrado |
| `500` | Error interno del servidor |
