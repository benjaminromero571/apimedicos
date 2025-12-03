# API Medical System - Documentación

## Tabla de Contenidos
- [Autenticación](#autenticación)
- [Historiales de Cuidador](#historiales-de-cuidador)
- [Asignaciones](#asignaciones)
- [Pacientes](#pacientes)
- [Profesionales](#profesionales)
- [Usuarios](#usuarios)
- [Recetas Médicas](#recetas-médicas)

---

## Autenticación

### Login
Autentica un usuario y devuelve un token JWT.

**Endpoint:** `POST /auth/login`

**Request:**
```json
{
  "email": "usuario@ejemplo.com",
  "password": "contraseña123"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "usuario@ejemplo.com",
      "rol": "Administrador"
    }
  }
}
```

---

## Historiales de Cuidador

### Listar Historiales (Sin Paginación)
**Endpoint:** `GET /historiales-cuidador`

**Query Parameters:**
- `limit` (opcional): Número de registros
- `offset` (opcional): Desplazamiento

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "fecha_historial": "2025-12-03 10:30:00",
      "fecha_historial_timestamp": "2025-12-03 10:30:15",
      "detalle": "Paciente presenta mejoría notable",
      "registro": {
        "texto": "Observaciones generales",
        "modalidad": "Día",
        "categorias": [...],
        "riesgos": {...}
      },
      "id_paciente": 12,
      "id_cuidador": 3,
      "nombre_paciente": "María García",
      "nombre_cuidador": "Alberto Ramírez",
      "created_at": "2025-12-03"
    }
  ],
  "message": "Historiales obtenidos correctamente"
}
```

### Obtener Historial por ID
**Endpoint:** `GET /historiales-cuidador/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "fecha_historial": "2025-12-03 10:30:00",
    "fecha_historial_timestamp": "2025-12-03 10:30:15",
    "detalle": "Paciente presenta mejoría notable",
    "registro": {...},
    "id_paciente": 12,
    "id_cuidador": 3,
    "nombre_paciente": "María García",
    "nombre_cuidador": "Alberto Ramírez",
    "created_at": "2025-12-03",
    "updated_at": "2025-12-03",
    "created_by_name": "Admin",
    "updated_by_name": "Admin"
  },
  "message": "Historial obtenido correctamente"
}
```

### Crear Historial de Cuidador
**Endpoint:** `POST /historiales-cuidador`

**Permisos:** Administrador, Cuidador

**Request:**
```json
{
  "detalle": "Observaciones del día",
  "id_paciente": 12,
  "id_cuidador": 3,
  "fecha_historial": "2025-12-03 10:30:00",
  "registro": {
    "texto": "Paciente estable",
    "modalidad": "Día",
    "categorias": [
      {
        "categoria": "Alimentación",
        "accion": "Realizado",
        "observacion": "Comió todo el almuerzo"
      },
      {
        "categoria": "Sueño",
        "accion": "Observado",
        "observacion": "Durmió bien durante la noche"
      }
    ],
    "riesgos": {
      "fuga": "Sin riesgo",
      "caida": "Riesgo bajo",
      "agresion": "Sin riesgo",
      "suicidio": "Sin riesgo",
      "consumoDrogas": "Sin riesgo",
      "promiscuidad": "Sin riesgo"
    }
  }
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 7,
    "fecha_historial": "2025-12-03 10:30:00",
    "fecha_historial_timestamp": "2025-12-03 10:35:22",
    "detalle": "Observaciones del día",
    "registro": {...},
    "id_paciente": 12,
    "id_cuidador": 3,
    "nombre_paciente": "María García",
    "nombre_cuidador": "Alberto Ramírez",
    "created_at": "2025-12-03"
  },
  "message": "Historial creado exitosamente"
}
```

### Actualizar Historial
**Endpoint:** `PUT /historiales-cuidador/{id}`

**Permisos:** Administrador, Cuidador

**Request:**
```json
{
  "detalle": "Observaciones actualizadas",
  "fecha_historial": "2025-12-03 11:00:00",
  "registro": {
    "texto": "Actualización de estado",
    "categorias": [...],
    "riesgos": {...}
  }
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 7,
    "fecha_historial": "2025-12-03 11:00:00",
    "detalle": "Observaciones actualizadas",
    ...
  },
  "message": "Historial actualizado exitosamente"
}
```

### Obtener Historiales por Paciente
**Endpoint:** `GET /historiales-cuidador/paciente/{id}`

**Query Parameters:**
- `limit` (opcional): Número de registros
- `offset` (opcional): Desplazamiento

**Response (200 OK):**
```json
{
  "success": true,
  "data": [...],
  "message": "Historiales del paciente obtenidos correctamente"
}
```

### Obtener Historiales por Cuidador
**Endpoint:** `GET /historiales-cuidador/cuidador/{id}`

**Query Parameters:**
- `limit` (opcional): Número de registros
- `offset` (opcional): Desplazamiento

**Response (200 OK):**
```json
{
  "success": true,
  "data": [...],
  "message": "Historiales del cuidador obtenidos correctamente"
}
```

### Obtener Historiales de Pacientes Asignados (Con Paginación)
**Endpoint:** `GET /historiales-cuidador/cuidador-asignado/{id}`

**Query Parameters:**
- `limit` (opcional): Número de registros por página
- `offset` (opcional): Desplazamiento (default: 0)
- `id_paciente` (opcional): Filtrar por paciente específico

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 6,
      "fecha_historial": "2025-12-03 17:00:00",
      "fecha_historial_timestamp": "2025-12-03 17:00:42",
      "detalle": "",
      "registro": {
        "texto": "Observaciones generales",
        "modalidad": "Noche",
        "categorias": [...],
        "riesgos": {...}
      },
      "id_paciente": 6,
      "id_cuidador": 3,
      "nombre_paciente": "Juan López",
      "nombre_cuidador": "Alberto Ramírez",
      "created_at": "2025-12-03"
    }
  ],
  "pagination": {
    "limit": 20,
    "offset": 0,
    "total": 6,
    "page": 1,
    "total_pages": 1
  },
  "message": "Historiales de pacientes asignados obtenidos correctamente"
}
```

### Buscar Historiales
**Endpoint:** `GET /historiales-cuidador/buscar`

**Query Parameters:**
- `id_paciente` (opcional): ID del paciente
- `id_cuidador` (opcional): ID del cuidador
- `fecha_desde` (opcional): Fecha inicio (Y-m-d)
- `fecha_hasta` (opcional): Fecha fin (Y-m-d)
- `detalle` (opcional): Búsqueda parcial en detalle
- `limit` (opcional): Registros por página (default: 50)
- `offset` (opcional): Desplazamiento (default: 0)
- `order_by` (opcional): Campo de ordenamiento (default: fecha_historial)
- `order_direction` (opcional): ASC o DESC (default: DESC)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "limit": 50,
    "offset": 0,
    "total": 25,
    "page": 1,
    "total_pages": 1
  },
  "message": "Búsqueda realizada correctamente"
}
```

### Eliminar Historial
**Endpoint:** `DELETE /historiales-cuidador/{id}`

**Permisos:** Solo Administrador

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Historial eliminado exitosamente"
}
```

---

## Asignaciones

### Listar Todas las Asignaciones (Sin Paginación)
**Endpoint:** `GET /asignaciones`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "paciente_id": 12,
      "fecha_asignacion": "2025-12-01 10:00:00",
      "user_name": "Carlos Medina",
      "user_rol": "Cuidador",
      "nompaciente": "María García",
      "rutpaciente": "12345678-9"
    }
  ]
}
```

### Listar Asignaciones con Paginación
**Endpoint:** `GET /asignaciones/paginated`

**Query Parameters:**
- `limit` (opcional): Número de registros por página
- `offset` (opcional): Desplazamiento (default: 0)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "paciente_id": 12,
      "fecha_asignacion": "2025-12-01 10:00:00",
      "user_name": "Carlos Medina",
      "user_rol": "Cuidador",
      "nompaciente": "María García",
      "rutpaciente": "12345678-9"
    }
  ],
  "pagination": {
    "limit": 20,
    "offset": 0,
    "total": 45,
    "page": 1,
    "total_pages": 3
  },
  "message": "Asignaciones obtenidas correctamente"
}
```

### Obtener Asignaciones por Usuario
**Endpoint:** `GET /asignaciones/user/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [...]
}
```

### Obtener Asignaciones por Paciente
**Endpoint:** `GET /asignaciones/paciente/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [...]
}
```

### Crear Asignación
**Endpoint:** `POST /asignaciones`

**Request:**
```json
{
  "user_id": 5,
  "paciente_id": 12
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "user_id": 5,
    "paciente_id": 12,
    "fecha_asignacion": "2025-12-03 14:30:00"
  },
  "message": "Asignación creada exitosamente"
}
```

### Eliminar Asignación
**Endpoint:** `DELETE /asignaciones/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Asignación eliminada exitosamente"
}
```

### Obtener Estadísticas de Asignaciones
**Endpoint:** `GET /asignaciones/estadisticas`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "total_asignaciones": 45,
    "usuarios_con_asignaciones": 12,
    "pacientes_asignados": 38,
    "fecha_generacion": "2025-12-03 15:30:00"
  }
}
```

---

## Pacientes

### Listar Todos los Pacientes (Sin Paginación)
**Endpoint:** `GET /pacientes`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "idpaciente": 12,
      "rutpaciente": "12345678-9",
      "nompaciente": "María García",
      "edadpaciente": 45,
      "telpaciente": "+56912345678",
      "dirpaciente": "Av. Principal 123"
    }
  ]
}
```

### Listar Pacientes con Paginación
**Endpoint:** `GET /pacientes/paginated`

**Query Parameters:**
- `limit` (opcional): Número de registros por página
- `offset` (opcional): Desplazamiento (default: 0)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "idpaciente": 12,
      "rutpaciente": "12345678-9",
      "nompaciente": "María García",
      "edadpaciente": 45,
      "telpaciente": "+56912345678",
      "dirpaciente": "Av. Principal 123"
    }
  ],
  "pagination": {
    "limit": 20,
    "offset": 0,
    "total": 150,
    "page": 1,
    "total_pages": 8
  },
  "message": "Pacientes obtenidos correctamente"
}
```

### Obtener Paciente por ID
**Endpoint:** `GET /pacientes/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "idpaciente": 12,
    "rutpaciente": "12345678-9",
    "nompaciente": "María García",
    "edadpaciente": 45,
    "telpaciente": "+56912345678",
    "dirpaciente": "Av. Principal 123"
  }
}
```

### Obtener Paciente por RUT
**Endpoint:** `GET /pacientes/rut/{rut}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "idpaciente": 12,
    "rutpaciente": "12345678-9",
    "nompaciente": "María García",
    "edadpaciente": 45,
    "telpaciente": "+56912345678",
    "dirpaciente": "Av. Principal 123"
  }
}
```

### Crear Paciente
**Endpoint:** `POST /pacientes`

**Request:**
```json
{
  "rutpaciente": "12345678-9",
  "nompaciente": "María García",
  "edadpaciente": 45,
  "telpaciente": "+56912345678",
  "dirpaciente": "Av. Principal 123"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "idpaciente": 12,
    "rutpaciente": "12345678-9",
    "nompaciente": "María García",
    "edadpaciente": 45,
    "telpaciente": "+56912345678",
    "dirpaciente": "Av. Principal 123"
  },
  "message": "Paciente creado exitosamente"
}
```

### Actualizar Paciente
**Endpoint:** `PUT /pacientes/{id}`

**Request:**
```json
{
  "nompaciente": "María García López",
  "edadpaciente": 46,
  "telpaciente": "+56987654321",
  "dirpaciente": "Av. Secundaria 456"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "idpaciente": 12,
    "rutpaciente": "12345678-9",
    "nompaciente": "María García López",
    "edadpaciente": 46,
    "telpaciente": "+56987654321",
    "dirpaciente": "Av. Secundaria 456"
  },
  "message": "Paciente actualizado exitosamente"
}
```

### Eliminar Paciente
**Endpoint:** `DELETE /pacientes/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Paciente eliminado exitosamente"
}
```

### Buscar Pacientes
**Endpoint:** `GET /pacientes/buscar?q=maria`

**Query Parameters:**
- `q`: Término de búsqueda

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "idpaciente": 12,
      "rutpaciente": "12345678-9",
      "nompaciente": "María García",
      "edadpaciente": 45,
      "telpaciente": "+56912345678",
      "dirpaciente": "Av. Principal 123"
    }
  ]
}
```

---

## Profesionales

### Listar Todos los Profesionales (Sin Paginación)
**Endpoint:** `GET /profesionales`

**Query Parameters:**
- `orderBy` (opcional): Campo de ordenamiento (default: nombre ASC)
- `limit` (opcional): Número de registros
- `offset` (opcional): Desplazamiento

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Dr. Juan Pérez",
      "telefono": "+56912345678",
      "documento": "12345678-9",
      "especialidad": "Cardiología",
      "id_user": 5
    }
  ],
  "message": "Profesionales obtenidos correctamente"
}
```

### Listar Profesionales con Paginación
**Endpoint:** `GET /profesionales/paginated`

**Query Parameters:**
- `limit` (opcional): Número de registros por página
- `offset` (opcional): Desplazamiento (default: 0)
- `orderBy` (opcional): Campo de ordenamiento (default: nombre ASC)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Dr. Juan Pérez",
      "telefono": "+56912345678",
      "documento": "12345678-9",
      "especialidad": "Cardiología",
      "id_user": 5
    }
  ],
  "pagination": {
    "limit": 20,
    "offset": 0,
    "total": 75,
    "page": 1,
    "total_pages": 4
  },
  "message": "Profesionales obtenidos correctamente"
}
```

### Obtener Profesional por ID
**Endpoint:** `GET /profesionales/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Dr. Juan Pérez",
    "telefono": "+56912345678",
    "documento": "12345678-9",
    "especialidad": "Cardiología",
    "id_user": 5
  },
  "message": "Profesional obtenido correctamente"
}
```

### Obtener Profesional por Documento
**Endpoint:** `GET /profesionales/documento/{documento}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Dr. Juan Pérez",
    "telefono": "+56912345678",
    "documento": "12345678-9",
    "especialidad": "Cardiología",
    "id_user": 5
  },
  "message": "Profesional encontrado"
}
```

### Obtener Especialidades
**Endpoint:** `GET /profesionales/especialidades`

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    "Cardiología",
    "Neurología",
    "Pediatría",
    "Psiquiatría",
    "Medicina General"
  ],
  "message": "Especialidades obtenidas correctamente"
}
```

### Obtener Profesionales por Especialidad
**Endpoint:** `GET /profesionales/especialidad/{especialidad}`

**Query Parameters:**
- `limit` (opcional): Número de registros
- `offset` (opcional): Desplazamiento

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Dr. Juan Pérez",
      "telefono": "+56912345678",
      "documento": "12345678-9",
      "especialidad": "Cardiología",
      "id_user": 5
    }
  ],
  "message": "Profesionales obtenidos correctamente"
}
```

### Crear Profesional
**Endpoint:** `POST /profesionales`

**Request:**
```json
{
  "nombre": "Dr. Juan Pérez",
  "telefono": "+56912345678",
  "documento": "12345678-9",
  "especialidad": "Cardiología",
  "id_user": 5
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Dr. Juan Pérez",
    "telefono": "+56912345678",
    "documento": "12345678-9",
    "especialidad": "Cardiología",
    "id_user": 5
  },
  "message": "Profesional creado exitosamente"
}
```

### Actualizar Profesional
**Endpoint:** `PUT /profesionales/{id}`

**Request:**
```json
{
  "nombre": "Dr. Juan Pérez García",
  "telefono": "+56987654321",
  "especialidad": "Cardiología Intervencionista"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Dr. Juan Pérez García",
    "telefono": "+56987654321",
    "documento": "12345678-9",
    "especialidad": "Cardiología Intervencionista",
    "id_user": 5
  },
  "message": "Profesional actualizado exitosamente"
}
```

### Eliminar Profesional
**Endpoint:** `DELETE /profesionales/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Profesional eliminado exitosamente"
}
```

---

## Usuarios

### Listar Usuarios
**Endpoint:** `GET /users`

**Query Parameters:**
- `limit` (opcional): Número de registros
- `offset` (opcional): Desplazamiento

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@ejemplo.com",
      "rol": "Administrador"
    }
  ]
}
```

### Obtener Usuario por ID
**Endpoint:** `GET /users/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "rol": "Administrador"
  }
}
```

### Crear Usuario
**Endpoint:** `POST /users`

**Request:**
```json
{
  "name": "Carlos López",
  "email": "carlos@ejemplo.com",
  "password": "contraseña123",
  "rol": "Cuidador"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "name": "Carlos López",
    "email": "carlos@ejemplo.com",
    "rol": "Cuidador"
  },
  "message": "Usuario creado exitosamente"
}
```

### Actualizar Usuario
**Endpoint:** `PUT /users/{id}`

**Request:**
```json
{
  "name": "Carlos López García",
  "email": "carlos.lopez@ejemplo.com",
  "rol": "Administrador"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "name": "Carlos López García",
    "email": "carlos.lopez@ejemplo.com",
    "rol": "Administrador"
  },
  "message": "Usuario actualizado exitosamente"
}
```

### Eliminar Usuario
**Endpoint:** `DELETE /users/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Usuario eliminado exitosamente"
}
```

---

## Recetas Médicas

### Listar Recetas Médicas
**Endpoint:** `GET /recetas-medicas`

**Query Parameters:**
- `limit` (opcional): Número de registros
- `offset` (opcional): Desplazamiento

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_historial": 5,
      "id_paciente": 12,
      "id_profesional": 3,
      "fecha_receta": "2025-12-03",
      "medicamentos": "Paracetamol 500mg",
      "dosis": "1 comprimido cada 8 horas",
      "duracion": "7 días",
      "indicaciones": "Tomar con alimentos",
      "nombre_paciente": "María García",
      "nombre_profesional": "Dr. Juan Pérez"
    }
  ],
  "message": "Recetas médicas obtenidas correctamente"
}
```

### Obtener Receta por ID
**Endpoint:** `GET /recetas-medicas/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "id_historial": 5,
    "id_paciente": 12,
    "id_profesional": 3,
    "fecha_receta": "2025-12-03",
    "medicamentos": "Paracetamol 500mg",
    "dosis": "1 comprimido cada 8 horas",
    "duracion": "7 días",
    "indicaciones": "Tomar con alimentos",
    "nombre_paciente": "María García",
    "nombre_profesional": "Dr. Juan Pérez"
  },
  "message": "Receta médica obtenida correctamente"
}
```

### Crear Receta Médica
**Endpoint:** `POST /recetas-medicas`

**Request:**
```json
{
  "id_historial": 5,
  "id_paciente": 12,
  "id_profesional": 3,
  "fecha_receta": "2025-12-03",
  "medicamentos": "Paracetamol 500mg",
  "dosis": "1 comprimido cada 8 horas",
  "duracion": "7 días",
  "indicaciones": "Tomar con alimentos"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "id_historial": 5,
    "id_paciente": 12,
    "id_profesional": 3,
    "fecha_receta": "2025-12-03",
    "medicamentos": "Paracetamol 500mg",
    "dosis": "1 comprimido cada 8 horas",
    "duracion": "7 días",
    "indicaciones": "Tomar con alimentos"
  },
  "message": "Receta médica creada exitosamente"
}
```

### Actualizar Receta Médica
**Endpoint:** `PUT /recetas-medicas/{id}`

**Request:**
```json
{
  "medicamentos": "Paracetamol 1000mg",
  "dosis": "1 comprimido cada 12 horas",
  "duracion": "5 días"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "medicamentos": "Paracetamol 1000mg",
    "dosis": "1 comprimido cada 12 horas",
    "duracion": "5 días",
    ...
  },
  "message": "Receta médica actualizada exitosamente"
}
```

### Eliminar Receta Médica
**Endpoint:** `DELETE /recetas-medicas/{id}`

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Receta médica eliminada exitosamente"
}
```

---

## Paginación

Todos los endpoints con soporte de paginación devuelven un objeto `pagination` con la siguiente estructura:

```json
{
  "pagination": {
    "limit": 20,
    "offset": 0,
    "total": 150,
    "page": 1,
    "total_pages": 8
  }
}
```

**Campos:**
- `limit`: Número de registros por página
- `offset`: Desplazamiento actual
- `total`: Total de registros disponibles
- `page`: Página actual
- `total_pages`: Total de páginas disponibles

---

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Solicitud exitosa |
| 201 | Created - Recurso creado exitosamente |
| 400 | Bad Request - Datos inválidos o error de validación |
| 401 | Unauthorized - No autenticado |
| 403 | Forbidden - Sin permisos suficientes |
| 404 | Not Found - Recurso no encontrado |
| 500 | Internal Server Error - Error del servidor |

---

## Errores

Todas las respuestas de error siguen el formato:

```json
{
  "success": false,
  "error": "Descripción del error"
}
```

**Ejemplo de error de validación:**
```json
{
  "success": false,
  "error": "Errores de validación: RUT del paciente es requerido, Nombre del paciente es requerido"
}
```

---

## Notas Importantes

1. **Autenticación**: La mayoría de los endpoints requieren autenticación mediante token JWT en el header `Authorization: Bearer {token}`

2. **Permisos**:
   - **Lectura**: Todos los usuarios autenticados
   - **Crear/Editar**: Administradores y Cuidadores
   - **Eliminar**: Solo Administradores

3. **Endpoints Paginados vs No Paginados**:
   - Los endpoints sin `/paginated` mantienen compatibilidad con clientes existentes
   - Los nuevos endpoints `/paginated` incluyen metadatos de paginación completos
   - Se recomienda migrar a endpoints paginados para mejor rendimiento

4. **Formato de Fechas**:
   - Fechas: `Y-m-d` (ejemplo: 2025-12-03)
   - Fechas con hora: `Y-m-d H:i:s` (ejemplo: 2025-12-03 14:30:00)

5. **Campo `registro` en Historiales de Cuidador**:
   - Es un campo JSON que puede contener arrays u objetos
   - Estructura flexible según necesidades del sistema
   - Ejemplos incluyen: texto, modalidad, categorías, y riesgos
