# Contexto

Eres un programador senior especialista en PHP. Debes escribir código claro, escalable y consistente con los patrones existentes del proyecto.

El software es una **API REST médica** llamada **GICO** (sistema de gestión de pacientes, profesionales y cuidadores). Está construida en PHP puro (sin frameworks), con arquitectura por capas y seguridad JWT.

---

# Arquitectura

Aplicación por capas simples con inyección manual de dependencias:

```
Repositories → Services → Controllers
```

- **Repositories**: acceden a la base de datos, usan **prepared statements**, extienden `BaseRepository`.
- **Services**: consumen repositories, validan datos, retornan **DTOs**, envuelven mutaciones en **transacciones**.
- **Controllers**: consumen services, extienden `BaseController`, manejan request/response HTTP.

---

# Estructura de directorios

| Directorio | Descripción |
|---|---|
| `core/` | Clases base: `Router`, `BaseController`, `Pagination` |
| `core/Security/` | Seguridad: `JWTService`, `AuthMiddleware`, `AuthorizationMiddleware`, `RateLimitService`, `SecurityHeaders`, `SecurityLogger`, `SecurityConfig` |
| `contracts/` | Interfaces: `RepositoryInterface`, `ServiceInterface` |
| `entities/` | Entidades puras del negocio (data classes) |
| `repositories/` | Repositorios que extienden `BaseRepository` |
| `services/` | Servicios que implementan `ServiceInterface` |
| `controllers/` | Controladores que extienden `BaseController` |
| `dto/` | Data Transfer Objects (5 tipos por módulo) |
| `endpoints/` | Archivos de registro de rutas, uno por módulo |
| `sql/` | Archivos SQL de esquema y migraciones |

---

# Convenciones de nombrado

| Aspecto | Convención |
|---|---|
| **Controllers** | Métodos en **español**: `obtenerTodos`, `obtenerPorId`, `crear`, `actualizar`, `eliminar`, `buscar` |
| **Services** | Métodos en **inglés**: `getById`, `getAll`, `create`, `update`, `delete`, `validate` |
| **Repositories** | Métodos en **inglés**: `findById`, `findAll`, `findWhere`, `create`, `update`, `delete`, `exists` |
| **DTOs** | Nombres en español del campo de BD: `nompaciente`, `rutpaciente`, etc. |
| **Rutas** | Recurso en español plural: `/pacientes`, `/profesionales`, `/historiales` |
| **Archivos** | PascalCase para clases: `PacienteController.php`, snake_case para endpoints: `pacientes.php` |

---

# Patrones por capa

## Entity (`entities/{Entidad}Entity.php`)

Clase pura sin lógica de negocio ni acceso a BD.

```php
class {Entidad}Entity {
    private $propiedad;

    public function __construct(array $data = []) { $this->fill($data); }
    public function fill(array $data): void { /* asigna campos desde array */ }
    public function getId() { }
    public function setNombre($v) { $this->nombre = $v; return $this; } // setter fluent
    public function toArray(): array { }
    public static function fromArray(array $data): static { return new static($data); }
    public function exists(): bool { return !empty($this->id); }
    public function getFillableData(): array { /* excluye PK */ }
}
```

## Repository (`repositories/{Entidad}Repository.php`)

Extiende `BaseRepository`. Define tabla, PK y campos fillable. Usa prepared statements.

```php
class {Entidad}Repository extends BaseRepository {
    protected $table = 'nombre_tabla';
    protected $primaryKey = 'id_campo';
    protected $fillable = ['campo1', 'campo2'];

    // Métodos custom con prepared statements
    public function findByXxx($valor) { /* bind_param */ }
    public function searchByCriteria(array $criteria) { /* WHERE dinámico */ }
    public function findWithPagination($page, $limit, $search) { /* LIMIT/OFFSET */ }
}
```

**BaseRepository hereda**: `findById`, `findAll`, `findWhere`, `create` (agrega `created_at = NOW()`), `update` (agrega `updated_at = NOW()`), `delete`, `exists`, `count`, `beginTransaction`, `commit`, `rollback`.

## Service (`services/{Entidad}Service.php`)

Implementa `ServiceInterface`. Compone repository, valida, retorna DTOs, usa transacciones.

```php
class {Entidad}Service implements ServiceInterface {
    private ${entidad}Repository;

    public function __construct($repo = null) {
        $this->{entidad}Repository = $repo ?? new {Entidad}Repository();
    }

    public function getById($id) { /* retorna {Entidad}Dto o null */ }
    public function getAll() { /* retorna array de {Entidad}Dto */ }
    public function getAllPaginated(?int $limit, int $offset) {
        // Usa \Core\Pagination::build($limit, $offset, $total)
        // Retorna { success, message, data: Dto[], pagination }
    }
    public function create(array $data) {
        // 1. Crea CreateDto → isValid()
        // 2. validate() para reglas de negocio
        // 3. beginTransaction/commit/rollback
        // 4. Retorna {Entidad}Dto creado
    }
    public function update($id, array $data) { /* transaccional */ }
    public function delete($id) { /* verifica dependencias antes de borrar */ }
    public function validate(array $data, $id = null): array { /* retorna errores */ }
    public function getByIdWithDetails($id) { /* retorna {Entidad}DetailDto */ }
    public function searchByCriteria({Entidad}SearchDto $dto) { }
}
```

## Controller (`controllers/{Entidad}Controller.php`)

Extiende `BaseController`. Compone service. Todos los métodos reciben `$params`.

```php
class {Entidad}Controller extends BaseController {
    private ${entidad}Service;

    public function __construct() {
        parent::__construct();
        $this->{entidad}Service = new {Entidad}Service();
    }

    public function obtenerTodos($params = []) {
        try {
            $resultado = $this->{entidad}Service->getAll();
            $data = array_map(fn($dto) => $dto->toArray(), $resultado);
            $this->jsonResponse($data, 'Listado exitoso');
        } catch (Exception $e) {
            $this->jsonError("Error: " . $e->getMessage(), 500);
        }
    }

    public function crear($params = []) {
        try {
            $data = $this->getJsonInput();
            $resultado = $this->{entidad}Service->create($data);
            $this->jsonResponse($resultado->toArray(), 'Creado exitosamente', 201);
        } catch (Exception $e) {
            $statusCode = strpos($e->getMessage(), 'validación') !== false ? 400 : 500;
            $this->jsonError("Error: " . $e->getMessage(), $statusCode);
        }
    }
}
```

## Endpoints (`endpoints/{modulo}.php`)

Registro de rutas. Formato string: `'Controller@method'`.

```php
$router->get('/{recurso}', '{Entidad}Controller@obtenerTodos');
$router->get('/{recurso}/paginated', '{Entidad}Controller@obtenerTodosPaginados');
$router->get('/{recurso}/buscar', '{Entidad}Controller@buscar');
$router->get('/{recurso}/{id}', '{Entidad}Controller@obtenerPorId');
$router->post('/{recurso}', '{Entidad}Controller@crear');
$router->put('/{recurso}/{id}', '{Entidad}Controller@actualizar');
$router->delete('/{recurso}/{id}', '{Entidad}Controller@eliminar');
```

---

# DTOs — 5 tipos por módulo

| Tipo | Archivo | Uso |
|---|---|---|
| **Listing** | `{Entidad}Dto.php` | Representación base para listados. Campos + `fill()`, `toArray()`, `fromArray()`. |
| **Detail** | `{Entidad}DetailDto.php` | Vista completa con relaciones anidadas, conteos, resumen booleano. |
| **Create** | `Create{Entidad}Dto.php` | DTO de entrada. Campos requeridos/opcionales + `isValid()` + `getValidationErrors()`. |
| **Search** | `{Entidad}SearchDto.php` | Criterios de filtrado + `getActiveCriteria()` + `hasSearchCriteria()`. |
| **Stats** | `{Entidad}StatsDto.php` | Estadísticas agregadas + métodos computados + `toArray()` con grupos anidados. |

Todos los DTOs siguen el patrón: propiedades públicas, `fill(array)`, `toArray()`, `fromArray()` estático.

---

# Formato de respuesta HTTP

## Éxito

```json
{
    "success": true,
    "data": "...",
    "pagination": { "limit": 10, "offset": 0, "total": 50, "page": 1, "total_pages": 5 },
    "message": "Operación exitosa"
}
```

## Error

```json
{
    "success": false,
    "error": "Mensaje de error",
    "errors": ["Error 1", "Error 2"]
}
```

## Códigos de estado

| Código | Uso |
|---|---|
| `200` | Lectura exitosa, actualización, eliminación |
| `201` | Creación exitosa |
| `400` | Error de validación |
| `404` | Recurso no encontrado |
| `429` | Rate limit excedido |
| `500` | Error del servidor |

---

# Helpers de BaseController

| Método | Uso |
|---|---|
| `getJsonInput()` | Lee body JSON del request |
| `jsonResponse($data, $message, $statusCode)` | Respuesta exitosa |
| `jsonError($message, $statusCode, $errors)` | Respuesta de error |
| `validateRequired($data, $requiredFields)` | Valida campos requeridos, retorna faltantes |
| `sanitizeString($string)` | `htmlspecialchars(trim())` |
| `executeQuery($query)` | Query cruda |
| `fetchAll($query)` | Query + todas las filas |
| `fetchOne($query)` | Query + primera fila |

---

# Paginación

Clase estática `\Core\Pagination::build(?int $limit, int $offset, int $total)`.
Retorna: `{ limit, offset, total, page, total_pages }`.

---

# Seguridad

- **JWT**: Bearer token en header `Authorization`. Implementación custom con HMAC SHA-256.
- **Estado autenticado**: `$GLOBALS['current_user']` (registro completo), `$GLOBALS['current_user_payload']` (payload JWT con `role`).
- **Roles** (jerarquía): Cuidador(1) < Profesional(2) < Medico(3) < Administrador(4).
- **Permisos**: formato `recurso.acción` con wildcard `*`. Ej: `pacientes.*`, `historiales.read`.
- **Rutas públicas**: `/auth/login`, `/auth/register`, `/auth/verify`, `/health`.
- **Rate limiting**: por IP, config por tipo (login: 5/5min, api: 100/min).

---

# Base de datos

Conexión `mysqli` via función `conexion()` con variables de entorno: `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`.

## Tablas principales

| Tabla | PK | Relaciones clave |
|---|---|---|
| `users` | `id` (auto-inc) | Roles: Cuidador, Profesional, Medico, Administrador |
| `pacientes` | `idpaciente` (auto-inc) | RUT, nombre, edad, teléfono, dirección |
| `profesionales` | `id` (auto-inc) | FK `id_user` → users |
| `historial` | `idhistorial` (auto-inc) | FK `idpaciente`. Campos médicos. |
| `historial_cuidador` | `id` (auto-inc) | `id_paciente`, `id_cuidador`, `detalle`, `registro` (JSON) |
| `asignaciones` | `id` (auto-inc) | `user_id` → users, `paciente_id`. UNIQUE(user_id, paciente_id) |
| `paciente_cuidador` | compuesta | Puente many-to-many |
| `paciente_profesional` | compuesta | Puente many-to-many |
| `receta_medica` | `id` (auto-inc) | `id_medico`, `detalle`, `fecha` |
| `indicacion_medica` | `id` (auto-inc) | Indicaciones médicas |

Todas las tablas tienen columnas de auditoría: `created_at`, `created_by`, `updated_at`, `updated_by`.

---

# Bootstrapping (index.php)

1. Carga `.env` via `SecurityConfig::loadEnv()`
2. Aplica security headers (CORS, CSP, HSTS)
3. Aplica rate limiting
4. Maneja preflight OPTIONS
5. Requiere core (`Router`, `BaseController`)
6. Requiere services y controllers
7. Crea `Router`, carga endpoints via `endpoints/index.php`
8. Aplica auth middleware (excluye rutas públicas)
9. Aplica authorization middleware
10. Extrae URI, strip `/api`, dispatch

---

# Interfaces (contracts/)

## RepositoryInterface

```
findById($id): mixed|null
findAll($orderBy): array
findWhere(array): array
create(array): mixed (new ID)
update($id, array): bool
delete($id): bool
exists($id): bool
```

## ServiceInterface

```
getById($id): mixed|null
getAll(): array
create(array): mixed
update($id, array): bool
delete($id): bool
validate(array, $id = null): array (errores)
```

---

# Registro de endpoints (endpoints/index.php)

Orden de carga:
1. `auth.php` (público)
2. `health.php` (público)
3. `pacientes.php`
4. `profesionales.php`
5. `historiales.php`
6. `historiales_cuidador.php`
7. `users.php`
8. `asignaciones.php`
9. `recetas_medicas.php`
10. `indicaciones_medicas.php`
11. `admin.php`

**Para agregar un nuevo módulo**: agregar archivo en `endpoints/` y registrarlo en `endpoints/index.php`.

---

# Checklist para nuevo módulo completo

1. [ ] Crear migración SQL en `sql/`
2. [ ] Crear entity en `entities/`
3. [ ] Crear repository en `repositories/`
4. [ ] Crear 5 DTOs en `dto/`
5. [ ] Crear service en `services/`
6. [ ] Crear controller en `controllers/`
7. [ ] Crear endpoints en `endpoints/`
8. [ ] Registrar endpoints en `endpoints/index.php`
9. [ ] Registrar `require` del controller en `index.php`
10. [ ] Agregar permisos en `AuthorizationMiddleware` 