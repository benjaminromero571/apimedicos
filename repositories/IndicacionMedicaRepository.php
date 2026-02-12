<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../entities/IndicacionMedicaEntity.php';

/**
 * Repository para manejar las operaciones de acceso a datos de Indicaciones Médicas
 */
class IndicacionMedicaRepository extends BaseRepository
{
    protected $table = 'indicaciones_medicas';
    protected $primaryKey = 'id';

    /**
     * Obtiene una indicación por ID con datos relacionados (joins)
     */
    public function getById(int $id): ?IndicacionMedicaEntity
    {
        $query = "
            SELECT 
                im.*,
                p.nompaciente as nombre_paciente,
                u.name as nombre_user,
                u.email as email_user,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} im
            LEFT JOIN pacientes p ON im.paciente_id = p.idpaciente
            LEFT JOIN users u ON im.user_id = u.id
            LEFT JOIN users u1 ON im.created_by = u1.id
            LEFT JOIN users u2 ON im.updated_by = u2.id
            WHERE im.{$this->primaryKey} = ?
        ";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data ? new IndicacionMedicaEntity($data) : null;
    }

    /**
     * Obtiene todas las indicaciones con paginación
     */
    public function getAll(?int $limit = null, int $offset = 0): array
    {
        $query = "
            SELECT 
                im.*,
                p.nompaciente as nombre_paciente,
                u.name as nombre_user,
                u.email as email_user,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} im
            LEFT JOIN pacientes p ON im.paciente_id = p.idpaciente
            LEFT JOIN users u ON im.user_id = u.id
            LEFT JOIN users u1 ON im.created_by = u1.id
            LEFT JOIN users u2 ON im.updated_by = u2.id
            ORDER BY im.created_at DESC
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
        $indicaciones = [];
        while ($data = $result->fetch_assoc()) {
            $indicaciones[] = new IndicacionMedicaEntity($data);
        }
        return $indicaciones;
    }

    /**
     * Obtiene indicaciones por paciente
     */
    public function getByPaciente(int $pacienteId, ?int $limit = null, int $offset = 0): array
    {
        $query = "
            SELECT 
                im.*,
                p.nompaciente as nombre_paciente,
                u.name as nombre_user,
                u.email as email_user,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} im
            LEFT JOIN pacientes p ON im.paciente_id = p.idpaciente
            LEFT JOIN users u ON im.user_id = u.id
            LEFT JOIN users u1 ON im.created_by = u1.id
            LEFT JOIN users u2 ON im.updated_by = u2.id
            WHERE im.paciente_id = ?
            ORDER BY im.created_at DESC
        ";
        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('iii', $pacienteId, $limit, $offset);
        } else {
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('i', $pacienteId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $indicaciones = [];
        while ($data = $result->fetch_assoc()) {
            $indicaciones[] = new IndicacionMedicaEntity($data);
        }
        return $indicaciones;
    }

    /**
     * Obtiene indicaciones por usuario (médico/profesional que las creó)
     */
    public function getByUser(int $userId, ?int $limit = null, int $offset = 0): array
    {
        $query = "
            SELECT 
                im.*,
                p.nompaciente as nombre_paciente,
                u.name as nombre_user,
                u.email as email_user,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} im
            LEFT JOIN pacientes p ON im.paciente_id = p.idpaciente
            LEFT JOIN users u ON im.user_id = u.id
            LEFT JOIN users u1 ON im.created_by = u1.id
            LEFT JOIN users u2 ON im.updated_by = u2.id
            WHERE im.user_id = ?
            ORDER BY im.created_at DESC
        ";
        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('iii', $userId, $limit, $offset);
        } else {
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('i', $userId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $indicaciones = [];
        while ($data = $result->fetch_assoc()) {
            $indicaciones[] = new IndicacionMedicaEntity($data);
        }
        return $indicaciones;
    }

    /**
     * Obtiene indicaciones filtradas por múltiples pacientes (para cuidadores)
     */
    public function getByPacientes(array $pacienteIds, ?int $limit = null, int $offset = 0): array
    {
        if (empty($pacienteIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($pacienteIds), '?'));
        $types = str_repeat('i', count($pacienteIds));
        $params = $pacienteIds;

        $query = "
            SELECT 
                im.*,
                p.nompaciente as nombre_paciente,
                u.name as nombre_user,
                u.email as email_user,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} im
            LEFT JOIN pacientes p ON im.paciente_id = p.idpaciente
            LEFT JOIN users u ON im.user_id = u.id
            LEFT JOIN users u1 ON im.created_by = u1.id
            LEFT JOIN users u2 ON im.updated_by = u2.id
            WHERE im.paciente_id IN ({$placeholders})
            ORDER BY im.created_at DESC
        ";
        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        }
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $indicaciones = [];
        while ($data = $result->fetch_assoc()) {
            $indicaciones[] = new IndicacionMedicaEntity($data);
        }
        return $indicaciones;
    }

    /**
     * Cuenta indicaciones filtradas por múltiples pacientes
     */
    public function countByPacientes(array $pacienteIds): int
    {
        if (empty($pacienteIds)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($pacienteIds), '?'));
        $types = str_repeat('i', count($pacienteIds));

        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE paciente_id IN ({$placeholders})";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param($types, ...$pacienteIds);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return (int)$data['total'];
    }

    /**
     * Búsqueda avanzada con filtros dinámicos
     */
    public function search(array $filters, ?int $limit = null, int $offset = 0): array
    {
        $query = "
            SELECT 
                im.*,
                p.nompaciente as nombre_paciente,
                u.name as nombre_user,
                u.email as email_user,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} im
            LEFT JOIN pacientes p ON im.paciente_id = p.idpaciente
            LEFT JOIN users u ON im.user_id = u.id
            LEFT JOIN users u1 ON im.created_by = u1.id
            LEFT JOIN users u2 ON im.updated_by = u2.id
        ";
        $conditions = [];
        $params = [];
        $types = '';

        if (isset($filters['paciente_id']) && $filters['paciente_id'] !== null) {
            $conditions[] = "im.paciente_id = ?";
            $params[] = $filters['paciente_id'];
            $types .= 'i';
        }
        if (isset($filters['paciente_ids']) && is_array($filters['paciente_ids']) && !empty($filters['paciente_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['paciente_ids']), '?'));
            $conditions[] = "im.paciente_id IN ({$placeholders})";
            foreach ($filters['paciente_ids'] as $pid) {
                $params[] = $pid;
                $types .= 'i';
            }
        }
        if (isset($filters['user_id']) && $filters['user_id'] !== null) {
            $conditions[] = "im.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= 'i';
        }
        if (isset($filters['fecha_desde']) && $filters['fecha_desde'] !== null) {
            $conditions[] = "im.created_at >= ?";
            $params[] = $filters['fecha_desde'] . ' 00:00:00';
            $types .= 's';
        }
        if (isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== null) {
            $conditions[] = "im.created_at <= ?";
            $params[] = $filters['fecha_hasta'] . ' 23:59:59';
            $types .= 's';
        }
        if (isset($filters['indicaciones']) && $filters['indicaciones'] !== null) {
            $conditions[] = "im.indicaciones LIKE ?";
            $params[] = '%' . $filters['indicaciones'] . '%';
            $types .= 's';
        }
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }
        $query .= " ORDER BY im.created_at DESC";
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
        $indicaciones = [];
        while ($data = $result->fetch_assoc()) {
            $indicaciones[] = new IndicacionMedicaEntity($data);
        }
        return $indicaciones;
    }

    /**
     * Crea una nueva indicación médica
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
            throw new Exception("Error al crear la indicación médica: " . $stmt->error);
        }
        return $stmt->insert_id;
    }

    /**
     * Actualiza una indicación médica existente
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
     * Elimina una indicación médica
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    /**
     * Cuenta indicaciones con filtros opcionales
     */
    public function count(array $filters = []): int
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} im";
        $conditions = [];
        $params = [];
        $types = '';
        if (isset($filters['paciente_id']) && $filters['paciente_id'] !== null) {
            $conditions[] = "im.paciente_id = ?";
            $params[] = $filters['paciente_id'];
            $types .= 'i';
        }
        if (isset($filters['paciente_ids']) && is_array($filters['paciente_ids']) && !empty($filters['paciente_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['paciente_ids']), '?'));
            $conditions[] = "im.paciente_id IN ({$placeholders})";
            foreach ($filters['paciente_ids'] as $pid) {
                $params[] = $pid;
                $types .= 'i';
            }
        }
        if (isset($filters['user_id']) && $filters['user_id'] !== null) {
            $conditions[] = "im.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= 'i';
        }
        if (isset($filters['fecha_desde']) && $filters['fecha_desde'] !== null) {
            $conditions[] = "im.created_at >= ?";
            $params[] = $filters['fecha_desde'] . ' 00:00:00';
            $types .= 's';
        }
        if (isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== null) {
            $conditions[] = "im.created_at <= ?";
            $params[] = $filters['fecha_hasta'] . ' 23:59:59';
            $types .= 's';
        }
        if (isset($filters['indicaciones']) && $filters['indicaciones'] !== null) {
            $conditions[] = "im.indicaciones LIKE ?";
            $params[] = '%' . $filters['indicaciones'] . '%';
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
     * Verifica si una indicación existe
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
     * Verifica si un paciente existe
     */
    public function pacienteExists(int $pacienteId): bool
    {
        $query = "SELECT COUNT(*) as total FROM pacientes WHERE idpaciente = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $pacienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data['total'] > 0;
    }

    /**
     * Verifica si un usuario existe
     */
    public function userExists(int $userId): bool
    {
        $query = "SELECT COUNT(*) as total FROM users WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data['total'] > 0;
    }

    /**
     * Obtiene el user_id (propietario) de una indicación
     */
    public function getUserIdPropietario(int $idIndicacion): ?int
    {
        $query = "SELECT user_id FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $idIndicacion);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        return $data ? (int)$data['user_id'] : null;
    }
}
