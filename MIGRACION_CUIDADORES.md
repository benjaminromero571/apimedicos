# Migración: Relación N:M entre Pacientes y Cuidadores

## Resumen de Cambios

Se ha modificado la arquitectura para permitir que un paciente pueda tener **múltiples cuidadores** (relación N:M) en lugar de solo uno (relación 1:1).

---

## Cambios en la Base de Datos

### Nueva Tabla: `paciente_cuidador`
```sql
CREATE TABLE `paciente_cuidador` (
  `id_paciente` int(11) NOT NULL,
  `id_cuidador` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_paciente`, `id_cuidador`)
);
```

### Columna Eliminada
- ✂️ **Eliminado**: `id_cuidador` de la tabla `pacientes`

---

## Cambios en el Código

### 1. **PacienteEntity.php**
✅ Ya estaba preparado con soporte para múltiples cuidadores
- Propiedad `$cuidadores` como array
- Métodos `addCuidador()` y `removeCuidador()`

### 2. **PacienteRepository.php**

#### Nuevo Método: `getCuidadores()`
```php
public function getCuidadores($pacienteId)
```
Obtiene la lista de todos los cuidadores asignados a un paciente.

#### Modificado: `assignCuidador()`
```php
public function assignCuidador($pacienteId, $cuidadorId, $userId = null)
```
- Ahora requiere `$cuidadorId` (no puede ser null)
- Inserta en tabla `paciente_cuidador`
- Valida duplicados

#### Nuevo Método: `unassignCuidador()`
```php
public function unassignCuidador($pacienteId, $cuidadorId)
```
Elimina la relación específica entre un paciente y un cuidador.

#### Nuevo Método: `unassignAllCuidadores()`
```php
public function unassignAllCuidadores($pacienteId)
```
Elimina todas las relaciones de cuidadores de un paciente.

#### Nuevo Método: `hasCuidadores()`
```php
public function hasCuidadores($pacienteId)
```
Verifica si un paciente tiene al menos un cuidador asignado.

#### Modificado: `findWithCuidador()`
Ahora utiliza `GROUP_CONCAT` para obtener múltiples cuidadores:
- Retorna `cuidador_ids`, `cuidador_names`, `cuidador_emails` (separados por coma)
- Convierte `cuidador_ids` en array `cuidadores`

#### Modificado: `findByCuidador()`
Actualizado para usar JOIN con `paciente_cuidador`.

#### Modificado: `getExtendedStats()`
Actualizado para contar relaciones desde `paciente_cuidador`.

### 3. **PacienteService.php**

#### Nuevo Método: `getCuidadores()`
```php
public function getCuidadores($pacienteId)
```
Servicio para obtener cuidadores de un paciente.

#### Modificado: `assignCuidador()`
```php
public function assignCuidador($pacienteId, $cuidadorId, $userId = null)
```
- Ahora **requiere** `$cuidadorId` (no acepta null)
- Agrega un cuidador adicional sin remover los existentes

#### Nuevo Método: `unassignCuidador()`
```php
public function unassignCuidador($pacienteId, $cuidadorId)
```
Remueve un cuidador específico.

#### Nuevo Método: `hasCuidadores()`
```php
public function hasCuidadores($pacienteId)
```
Verifica si el paciente tiene cuidadores.

#### Modificado: `validate()`
- ✂️ Eliminada la validación de `id_cuidador` en datos del paciente
- Los cuidadores ahora se gestionan por separado

### 4. **PacienteController.php**

#### Nuevo Método: `obtenerCuidadores()`
```php
GET /pacientes/{id}/cuidadores
```
Lista todos los cuidadores de un paciente.

#### Modificado: `asignarCuidador()`
```php
POST /pacientes/{id}/cuidador
Body: { "id_cuidador": 123 }
```
- Agrega un nuevo cuidador (no reemplaza)
- Ahora captura `user_id` de sesión para auditoría

#### Modificado: `removerCuidador()`
```php
DELETE /pacientes/{id}/cuidador/{cuidador_id}
// o
DELETE /pacientes/{id}/cuidador
Body: { "id_cuidador": 123 }
```
- Ahora remueve un cuidador específico
- Soporta ID en URL o en body

### 5. **pacientes.php** (Rutas)

#### Nueva Ruta
```php
$router->get('/pacientes/{id}/cuidadores', 'PacienteController@obtenerCuidadores');
```

#### Modificada
```php
$router->delete('/pacientes/{id}/cuidador/{cuidador_id}', 'PacienteController@removerCuidador');
```

---

## Uso de la API

### Obtener cuidadores de un paciente
```bash
GET /api/pacientes/5/cuidadores
```

**Respuesta:**
```json
[
  {
    "id": 10,
    "name": "María González",
    "email": "maria@example.com",
    "rol": "Cuidador",
    "created_at": "2025-11-27 10:00:00"
  },
  {
    "id": 15,
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "rol": "Cuidador",
    "created_at": "2025-11-27 11:30:00"
  }
]
```

### Asignar un cuidador
```bash
POST /api/pacientes/5/cuidador
Content-Type: application/json

{
  "id_cuidador": 20
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Cuidador asignado exitosamente"
}
```

### Remover un cuidador específico
```bash
DELETE /api/pacientes/5/cuidador/20
```

**O con body:**
```bash
DELETE /api/pacientes/5/cuidador
Content-Type: application/json

{
  "id_cuidador": 20
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Cuidador removido exitosamente"
}
```

### Obtener paciente con cuidadores
```bash
GET /api/pacientes/5
```

**Respuesta incluye:**
```json
{
  "idpaciente": 5,
  "rutpaciente": "12345678-9",
  "nompaciente": "Pedro Martínez",
  "cuidador_ids": "10,15",
  "cuidador_names": "María González, Juan Pérez",
  "cuidador_emails": "maria@example.com, juan@example.com",
  "cuidadores": [10, 15]
}
```

---

## Migración de Datos Existentes

1. **Ejecutar** el script `migration_paciente_cuidador.sql`
2. **Verificar** que los datos se migraron correctamente
3. **Probar** la aplicación con la nueva estructura
4. **Solo cuando esté todo funcionando**, descomentar la línea que elimina la columna `id_cuidador`

---

## Ventajas del Nuevo Sistema

✅ **Flexibilidad**: Un paciente puede tener múltiples cuidadores
✅ **Auditoría**: Registra quién y cuándo asignó cada cuidador
✅ **Escalabilidad**: Fácil agregar/remover cuidadores
✅ **Historial**: Mantiene timestamps de creación y actualización
✅ **Integridad**: Evita duplicados con PRIMARY KEY compuesta

---

## Compatibilidad

⚠️ **IMPORTANTE**: Los endpoints existentes siguen funcionando pero con nueva lógica:
- `POST /pacientes/{id}/cuidador` → AGREGA en lugar de reemplazar
- `DELETE /pacientes/{id}/cuidador` → Requiere especificar qué cuidador remover

---

## Rollback (Si es necesario)

Si necesitas volver atrás:

1. Restaurar la columna `id_cuidador`:
```sql
ALTER TABLE `pacientes` ADD COLUMN `id_cuidador` int(11) DEFAULT NULL;
```

2. Migrar de vuelta (tomando el primer cuidador):
```sql
UPDATE `pacientes` p
LEFT JOIN (
    SELECT id_paciente, MIN(id_cuidador) as id_cuidador
    FROM `paciente_cuidador`
    GROUP BY id_paciente
) pc ON p.idpaciente = pc.id_paciente
SET p.id_cuidador = pc.id_cuidador;
```

3. Restaurar código de los archivos de backup o repositorio.
