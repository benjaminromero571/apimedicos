# API REST - Recetas Médicas

## Descripción General

Se ha implementado un módulo completo para la gestión de **Recetas Médicas** siguiendo arquitectura limpia con el patrón Repository. Este módulo permite a los médicos crear y gestionar recetas médicas con control de permisos estricto.

## 🔒 Reglas de Seguridad y Permisos

### Permisos por Rol:

1. **Médicos (rol: "Medico")**:
   - ✅ Pueden **crear** recetas, pero SOLO a su propio nombre (id_medico = su user_id)
   - ✅ Pueden **editar** sus propias recetas
   - ✅ Pueden **leer** todas las recetas
   - ❌ NO pueden eliminar recetas
   - ❌ NO pueden crear recetas para otros médicos
   - ❌ NO pueden editar recetas de otros médicos

2. **Administradores (rol: "Administrador")**:
   - ✅ Pueden **crear** recetas para cualquier médico
   - ✅ Pueden **editar** cualquier receta
   - ✅ Pueden **eliminar** cualquier receta
   - ✅ Pueden **leer** todas las recetas

3. **Otros roles** (Profesional, Cuidador):
   - ✅ Pueden **leer** recetas
   - ❌ NO pueden crear, editar o eliminar recetas

## 📁 Estructura de Archivos Creados

```
api/
├── entities/
│   └── RecetaMedicaEntity.php          # Entidad pura de datos
├── dto/
│   ├── RecetaMedicaDto.php             # DTO básico para listados
│   ├── RecetaMedicaDetailDto.php       # DTO detallado con joins
│   ├── CreateRecetaMedicaDto.php       # DTO para creación con validaciones
│   └── RecetaMedicaSearchDto.php       # DTO para búsquedas con filtros
├── repositories/
│   └── RecetaMedicaRepository.php      # Capa de acceso a datos
├── services/
│   └── RecetaMedicaService.php         # Lógica de negocio y validaciones
├── controllers/
│   └── RecetaMedicaController.php      # Manejo de peticiones HTTP
├── endpoints/
│   └── recetas_medicas.php             # Definición de rutas
└── tests/
    ├── RecetaMedicaRepositoryTest.php  # Tests de repositorio
    └── RecetaMedicaServiceTest.php     # Tests de servicio
```

## 🛠️ Endpoints API

### 1. Obtener todas las recetas
```http
GET /recetas-medicas?limit=10&offset=0
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Recetas obtenidas correctamente",
  "data": {
    "recetas": [
      {
        "id": 1,
        "detalle": "Paracetamol 500mg cada 8 horas por 5 días",
        "fecha": "2024-01-15",
        "id_medico": 5,
        "nombre_medico": "Dr. Juan Pérez",
        "created_at": "2024-01-15 10:30:00"
      }
    ],
    "total": 25,
    "showing": 10
  }
}
```

### 2. Obtener receta por ID
```http
GET /recetas-medicas/123
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Receta obtenida correctamente",
  "data": {
    "id": 123,
    "detalle": "Paracetamol 500mg cada 8 horas por 5 días",
    "fecha": "2024-01-15",
    "medico": {
      "id": 5,
      "nombre": "Dr. Juan Pérez",
      "email": "juan.perez@hospital.com"
    },
    "auditoria": {
      "created_at": "2024-01-15 10:30:00",
      "created_by": 5,
      "created_by_name": "Dr. Juan Pérez",
      "updated_at": "2024-01-15 10:30:00",
      "updated_by": 5,
      "updated_by_name": "Dr. Juan Pérez"
    }
  }
}
```

### 3. Obtener recetas de un médico específico
```http
GET /recetas-medicas/medico/5?limit=20&offset=0
Authorization: Bearer {token}
```

### 4. Obtener mis recetas (médico autenticado)
```http
GET /recetas-medicas/mis-recetas?limit=20&offset=0
Authorization: Bearer {token}
```
**Nota:** Solo accesible para usuarios con rol "Medico"

### 5. Buscar recetas con filtros
```http
GET /recetas-medicas/buscar?id_medico=5&fecha_desde=2024-01-01&fecha_hasta=2024-12-31&detalle=paracetamol&limit=10
Authorization: Bearer {token}
```

**Filtros disponibles:**
- `id_medico`: ID del médico
- `fecha_desde`: Fecha inicial (YYYY-MM-DD)
- `fecha_hasta`: Fecha final (YYYY-MM-DD)
- `detalle`: Búsqueda parcial en el detalle
- `limit`: Número de resultados
- `offset`: Paginación

### 6. Crear receta médica
```http
POST /recetas-medicas
Authorization: Bearer {token}
Content-Type: application/json

{
  "detalle": "Paracetamol 500mg cada 8 horas por 5 días",
  "fecha": "2024-01-15",
  "id_medico": 5
}
```

**Permisos:**
- Médicos: Solo pueden crear con `id_medico` igual a su propio user_id
- Administradores: Pueden crear para cualquier médico

**Validaciones:**
- `detalle`: Requerido, mínimo 10 caracteres, máximo 255
- `fecha`: Opcional (default: hoy), formato YYYY-MM-DD, no puede ser futura
- `id_medico`: Requerido, debe existir y tener rol "Medico"

### 7. Actualizar receta
```http
PUT /recetas-medicas/123
Authorization: Bearer {token}
Content-Type: application/json

{
  "detalle": "Paracetamol 500mg cada 6 horas por 7 días",
  "fecha": "2024-01-16"
}
```

**Permisos:**
- Solo el médico propietario puede editar su receta
- Los administradores pueden editar cualquier receta

**Campos actualizables:**
- `detalle`: Opcional
- `fecha`: Opcional

### 8. Eliminar receta
```http
DELETE /recetas-medicas/123
Authorization: Bearer {token}
```

**Permisos:**
- Solo administradores pueden eliminar recetas

### 9. Estadísticas por médico
```http
GET /recetas-medicas/estadisticas/medico/5
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Estadísticas obtenidas correctamente",
  "data": {
    "id_medico": 5,
    "total_recetas": 150,
    "recetas_ultimo_mes": 25
  }
}
```

## 🔐 Autenticación

Todos los endpoints requieren un token JWT válido en el header:
```
Authorization: Bearer {token}
```

El token debe contener:
- `user_id`: ID del usuario
- `rol`: Rol del usuario (Medico, Administrador, etc.)
- `email`: Email del usuario

## ⚠️ Códigos de Estado HTTP

- `200 OK`: Operación exitosa
- `201 Created`: Recurso creado exitosamente
- `400 Bad Request`: Datos inválidos o faltantes
- `401 Unauthorized`: Token inválido o faltante
- `403 Forbidden`: Sin permisos para realizar la operación
- `404 Not Found`: Recurso no encontrado
- `500 Internal Server Error`: Error del servidor

## 🧪 Pruebas Unitarias

### Ejecutar tests del repositorio:
```bash
php tests/RecetaMedicaRepositoryTest.php
```

**Tests incluidos:**
- ✅ Crear receta
- ✅ Obtener por ID
- ✅ Obtener todas con paginación
- ✅ Obtener por médico
- ✅ Búsqueda con filtros
- ✅ Actualizar receta
- ✅ Verificar existencia
- ✅ Contar recetas
- ✅ Obtener médico propietario
- ✅ Eliminar receta

### Ejecutar tests del servicio:
```bash
php tests/RecetaMedicaServiceTest.php
```

**Tests incluidos:**
- ✅ Crear receta como médico
- ✅ Denegar creación a cuidador
- ✅ Denegar creación para otro médico
- ✅ Obtener todas las recetas
- ✅ Obtener por ID y por médico
- ✅ Búsqueda con filtros
- ✅ Actualizar como propietario
- ✅ Denegar actualización a no propietario
- ✅ Permitir actualización a administrador
- ✅ Estadísticas por médico
- ✅ Denegar eliminación a médico
- ✅ Permitir eliminación a administrador

## 📊 Arquitectura Implementada

### Capa de Entidad (Entity)
- `RecetaMedicaEntity.php`: Representación pura de datos
- Sin lógica de negocio ni acceso a datos
- Getters y setters con tipado estricto

### Capa de DTO (Data Transfer Object)
- `RecetaMedicaDto`: Vista resumida para listados
- `RecetaMedicaDetailDto`: Vista completa con joins
- `CreateRecetaMedicaDto`: Validaciones para creación
- `RecetaMedicaSearchDto`: Filtros de búsqueda

### Capa de Repositorio (Repository)
- `RecetaMedicaRepository`: CRUD y consultas SQL
- Queries optimizados con prepared statements
- Joins con tablas relacionadas (users)
- Sin lógica de negocio

### Capa de Servicio (Service)
- `RecetaMedicaService`: Lógica de negocio y validaciones
- Control de permisos por rol
- Transformación Entity ↔ DTO
- Orquestación de operaciones

### Capa de Controlador (Controller)
- `RecetaMedicaController`: Manejo de peticiones HTTP
- Extracción de parámetros
- Validación de JWT
- Respuestas JSON estandarizadas

## 🔄 Flujo de una Petición

```
1. Cliente → POST /recetas-medicas + JWT
2. Router → RecetaMedicaController@crear
3. Controller:
   - Valida JWT y extrae usuario
   - Valida datos de entrada
   - Crea CreateRecetaMedicaDto
4. Service:
   - Valida permisos (solo médico/admin)
   - Valida que médico cree a su nombre
   - Valida que médico exista
5. Repository:
   - Ejecuta INSERT en base de datos
   - Retorna ID generado
6. Service:
   - Obtiene receta creada
   - Transforma a DTO detallado
7. Controller:
   - Formatea respuesta JSON
   - Retorna 201 Created
8. Cliente ← JSON con receta creada
```

## 📝 Ejemplos de Uso

### Médico crea su propia receta:
```javascript
// Usuario autenticado: ID=5, rol=Medico
fetch('/recetas-medicas', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    detalle: 'Amoxicilina 500mg cada 8 horas por 7 días',
    id_medico: 5 // Su propio ID
  })
});
// ✅ ÉXITO
```

### Médico intenta crear para otro médico:
```javascript
// Usuario autenticado: ID=5, rol=Medico
fetch('/recetas-medicas', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    detalle: 'Receta para otro médico',
    id_medico: 10 // Otro médico
  })
});
// ❌ ERROR 403: "Solo puede crear recetas a su propio nombre"
```

### Cuidador intenta crear receta:
```javascript
// Usuario autenticado: ID=20, rol=Cuidador
fetch('/recetas-medicas', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    detalle: 'Intento de receta',
    id_medico: 5
  })
});
// ❌ ERROR 403: "Solo los médicos pueden crear recetas"
```

## 🎯 Características Destacadas

1. **Tipado estricto PHP 8.2+**: Uso de `declare(strict_types=1)` en todos los archivos
2. **Validaciones robustas**: En DTO y Service
3. **Control de permisos granular**: Por rol y propiedad
4. **Prepared statements**: Prevención de inyección SQL
5. **Auditoría completa**: created_by, updated_by, timestamps
6. **Paginación**: En todos los listados
7. **Búsqueda avanzada**: Múltiples filtros combinables
8. **Documentación en código**: PHPDoc en todas las clases y métodos
9. **Tests unitarios**: Cobertura de Repository y Service
10. **Respuestas estandarizadas**: Formato JSON consistente

## 🚀 Próximos Pasos Sugeridos

1. Integrar el archivo `endpoints/recetas_medicas.php` en el router principal
2. Actualizar `AuthorizationMiddleware` si es necesario para los nuevos permisos
3. Ejecutar los tests unitarios para verificar funcionamiento
4. Crear colección de Postman para pruebas manuales
5. Actualizar documentación general de la API

## 📞 Soporte

Para dudas o problemas con este módulo, revisar:
- Logs de errores de PHP
- Tests unitarios para ejemplos de uso
- Comentarios en el código fuente
