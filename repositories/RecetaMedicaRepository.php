<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../entities/RecetaMedicaEntity.php';

/**
 * RecetaMedicaRepository - Repositorio para acceso a datos de recetas médicas
 * 
 * Capa de acceso a datos que maneja todas las operaciones CRUD y consultas
 * relacionadas con la tabla receta_medica.
 * 
 * Arquitectura: Extiende BaseRepository para heredar funcionalidad común.
 * Solo contiene lógica de acceso a datos, NO lógica de negocio.
 */
class RecetaMedicaRepository extends BaseRepository
{
    protected $table = 'receta_medica';
    protected $primaryKey = 'id';

    /**
     * Obtiene una receta por ID con información relacionada
     * 
     * @param int $id
     * @return RecetaMedicaEntity|null
     */
    public function getById(int $id): ?RecetaMedicaEntity
    {
        $query = "
            SELECT 
                rm.*,
                m.name as nombre_medico,
                m.email as email_medico,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} rm
            LEFT JOIN users m ON rm.id_medico = m.id
            LEFT JOIN users u1 ON rm.created_by = u1.id
            LEFT JOIN users u2 ON rm.updated_by = u2.id
            WHERE rm.{$this->primaryKey} = ?
        ";

        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data ? new RecetaMedicaEntity($data) : null;
    }

    /**
     * Obtiene todas las recetas con paginación e información relacionada
     * 
     * @param int|null $limit
     * @param int $offset
     * @return array Array de RecetaMedicaEntity
     */
    public function getAll(?int $limit = null, int $offset = 0): array
    {
        $query = "
            SELECT 
                rm.*,
                m.name as nombre_medico,
                m.email as email_medico,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} rm
            LEFT JOIN users m ON rm.id_medico = m.id
            LEFT JOIN users u1 ON rm.created_by = u1.id
            LEFT JOIN users u2 ON rm.updated_by = u2.id
            ORDER BY rm.fecha DESC, rm.created_at DESC
        ";

        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('ii', $limit, $offset);
        } else {
            $stmt = $this->conexion->prepare($query);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $recetas = [];
        while ($data = $result->fetch_assoc()) {
            $recetas[] = new RecetaMedicaEntity($data);
        }

        return $recetas;
    }

    /**
     * Obtiene recetas por médico
     * 
     * @param int $idMedico
     * @param int|null $limit
     * @param int $offset
     * @return array Array de RecetaMedicaEntity
     */
    public function getByMedico(int $idMedico, ?int $limit = null, int $offset = 0): array
    {
        $query = "
            SELECT 
                rm.*,
                m.name as nombre_medico,
                m.email as email_medico,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} rm
            LEFT JOIN users m ON rm.id_medico = m.id
            LEFT JOIN users u1 ON rm.created_by = u1.id
            LEFT JOIN users u2 ON rm.updated_by = u2.id
            WHERE rm.id_medico = ?
            ORDER BY rm.fecha DESC, rm.created_at DESC
        ";

        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('iii', $idMedico, $limit, $offset);
        } else {
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('i', $idMedico);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $recetas = [];
        while ($data = $result->fetch_assoc()) {
            $recetas[] = new RecetaMedicaEntity($data);
        }

        return $recetas;
    }

    /**
     * Búsqueda avanzada de recetas con múltiples filtros
     * 
     * @param array $filters Array con filtros: id_medico, fecha_desde, fecha_hasta, detalle
     * @param int|null $limit
     * @param int $offset
     * @return array Array de RecetaMedicaEntity
     */
    public function search(array $filters, ?int $limit = null, int $offset = 0): array
    {
        $query = "
            SELECT 
                rm.*,
                m.name as nombre_medico,
                m.email as email_medico,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} rm
            LEFT JOIN users m ON rm.id_medico = m.id
            LEFT JOIN users u1 ON rm.created_by = u1.id
            LEFT JOIN users u2 ON rm.updated_by = u2.id
        ";

        $conditions = [];
        $params = [];
        $types = '';

        // Filtro por médico
        if (isset($filters['id_medico']) && $filters['id_medico'] !== null) {
            $conditions[] = "rm.id_medico = ?";
            $params[] = $filters['id_medico'];
            $types .= 'i';
        }

        // Filtro por historial
        if (isset($filters['id_historial']) && $filters['id_historial'] !== null) {
            $conditions[] = "rm.id_historial = ?";
            $params[] = $filters['id_historial'];
            $types .= 'i';
        }

        // Filtro por rango de fechas
        if (isset($filters['fecha_desde']) && $filters['fecha_desde'] !== null) {
            $conditions[] = "rm.fecha >= ?";
            $params[] = $filters['fecha_desde'];
            $types .= 's';
        }

        if (isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== null) {
            $conditions[] = "rm.fecha <= ?";
            $params[] = $filters['fecha_hasta'];
            $types .= 's';
        }

        // Filtro por detalle (búsqueda parcial)
        if (isset($filters['detalle']) && $filters['detalle'] !== null) {
            $conditions[] = "rm.detalle LIKE ?";
            $params[] = '%' . $filters['detalle'] . '%';
            $types .= 's';
        }

        // Agregar condiciones WHERE si existen
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $query .= " ORDER BY rm.fecha DESC, rm.created_at DESC";

        // Agregar límite y offset
        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        }

        $stmt = $this->conexion->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $recetas = [];
        while ($data = $result->fetch_assoc()) {
            $recetas[] = new RecetaMedicaEntity($data);
        }

        return $recetas;
    }

    /**
     * Crea una nueva receta médica
     * 
     * @param array $data Datos de la receta
     * @return int ID de la receta creada
     */
    public function create(array $data): int
    {
        $fields = [];
        $placeholders = [];
        $values = [];
        $types = '';

        foreach ($data as $field => $value) {
            $fields[] = $field;
            $placeholders[] = '?';
            $values[] = $value;
            $types .= is_int($value) ? 'i' : 's';
        }

        $query = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                  VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param($types, ...$values);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al crear la receta: " . $stmt->error);
        }

        return $stmt->insert_id;
    }

    /**
     * Actualiza una receta existente
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data)
    {
        $sets = [];
        $values = [];
        $types = '';

        foreach ($data as $field => $value) {
            $sets[] = "{$field} = ?";
            $values[] = $value;
            $types .= is_int($value) ? 'i' : 's';
        }

        $values[] = $id;
        $types .= 'i';

        $query = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = ?";

        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param($types, ...$values);

        return $stmt->execute();
    }

    /**
     * Elimina una receta
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        
        return $stmt->execute();
    }

    /**
     * Cuenta el total de recetas (para paginación)
     * 
     * @param array $filters Filtros opcionales
     * @return int
     */
    public function count(array $filters = []): int
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} rm";

        $conditions = [];
        $params = [];
        $types = '';

        if (isset($filters['id_medico']) && $filters['id_medico'] !== null) {
            $conditions[] = "rm.id_medico = ?";
            $params[] = $filters['id_medico'];
            $types .= 'i';
        }

        if (isset($filters['id_historial']) && $filters['id_historial'] !== null) {
            $conditions[] = "rm.id_historial = ?";
            $params[] = $filters['id_historial'];
            $types .= 'i';
        }

        if (isset($filters['fecha_desde']) && $filters['fecha_desde'] !== null) {
            $conditions[] = "rm.fecha >= ?";
            $params[] = $filters['fecha_desde'];
            $types .= 's';
        }

        if (isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== null) {
            $conditions[] = "rm.fecha <= ?";
            $params[] = $filters['fecha_hasta'];
            $types .= 's';
        }

        if (isset($filters['detalle']) && $filters['detalle'] !== null) {
            $conditions[] = "rm.detalle LIKE ?";
            $params[] = '%' . $filters['detalle'] . '%';
            $types .= 's';
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->conexion->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return (int)$data['total'];
    }

    /**
     * Verifica si un médico existe
     * 
     * @param int $idMedico
     * @return bool
     */
    public function medicoExists(int $idMedico): bool
    {
        $query = "SELECT COUNT(*) as total FROM users WHERE id = ? AND rol = 'Medico'";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $idMedico);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data['total'] > 0;
    }

    /**
     * Verifica si un historial existe
     *
     * @param int $idHistorial
     * @return bool
     */
    public function historialExists(int $idHistorial): bool
    {
        $query = "SELECT COUNT(*) as total FROM historial WHERE idhistorial = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $idHistorial);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data['total'] > 0;
    }

    /**
     * Verifica si una receta existe
     * 
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data['total'] > 0;
    }

    /**
     * Obtiene el ID del médico propietario de una receta
     * 
     * @param int $idReceta
     * @return int|null
     */
    public function getIdMedicoPropietario(int $idReceta): ?int
    {
        $query = "SELECT id_medico FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $idReceta);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data ? (int)$data['id_medico'] : null;
    }
}
