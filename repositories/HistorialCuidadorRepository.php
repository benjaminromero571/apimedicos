<?php

declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../entities/HistorialCuidadorEntity.php';

/**
 * HistorialCuidadorRepository - Repositorio para acceso a datos de historiales de cuidador
 * 
 * Capa de acceso a datos que maneja todas las operaciones CRUD y consultas
 * relacionadas con la tabla historial_cuidador.
 * 
 * Arquitectura: Extiende BaseRepository para heredar funcionalidad común.
 * Solo contiene lógica de acceso a datos, NO lógica de negocio.
 */
class HistorialCuidadorRepository extends BaseRepository
{
    protected $table = 'historial_cuidador';
    protected $primaryKey = 'id';

    /**
     * Obtiene un historial por ID con información relacionada
     * 
     * @param int $id
     * @return HistorialCuidadorEntity|null
     */
    public function getById(int $id): ?HistorialCuidadorEntity
    {
        $query = "
            SELECT 
                hc.*,
                p.nompaciente as nombre_paciente,
                c.name as nombre_cuidador,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} hc
            LEFT JOIN pacientes p ON hc.id_paciente = p.idpaciente
            LEFT JOIN users c ON hc.id_cuidador = c.id
            LEFT JOIN users u1 ON hc.created_by = u1.id
            LEFT JOIN users u2 ON hc.updated_by = u2.id
            WHERE hc.{$this->primaryKey} = ?
        ";

        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data ? new HistorialCuidadorEntity($data) : null;
    }

    /**
     * Obtiene todos los historiales con paginación e información relacionada
     * 
     * @param int|null $limit
     * @param int $offset
     * @return array Array de HistorialCuidadorEntity
     */
    public function getAll(?int $limit = null, int $offset = 0): array
    {
        $query = "
            SELECT 
                hc.*,
                p.nompaciente as nombre_paciente,
                c.name as nombre_cuidador,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} hc
            LEFT JOIN pacientes p ON hc.id_paciente = p.idpaciente
            LEFT JOIN users c ON hc.id_cuidador = c.id
            LEFT JOIN users u1 ON hc.created_by = u1.id
            LEFT JOIN users u2 ON hc.updated_by = u2.id
            ORDER BY hc.fecha_historial DESC, hc.created_at DESC
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

        $historiales = [];
        while ($data = $result->fetch_assoc()) {
            $historiales[] = new HistorialCuidadorEntity($data);
        }

        return $historiales;
    }

    /**
     * Obtiene historiales por paciente
     * 
     * @param int $idPaciente
     * @param int|null $limit
     * @param int $offset
     * @return array Array de HistorialCuidadorEntity
     */
    public function getByPaciente(int $idPaciente, ?int $limit = null, int $offset = 0): array
    {
        $query = "
            SELECT 
                hc.*,
                p.nompaciente as nombre_paciente,
                c.name as nombre_cuidador,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} hc
            LEFT JOIN pacientes p ON hc.id_paciente = p.idpaciente
            LEFT JOIN users c ON hc.id_cuidador = c.id
            LEFT JOIN users u1 ON hc.created_by = u1.id
            LEFT JOIN users u2 ON hc.updated_by = u2.id
            WHERE hc.id_paciente = ?
            ORDER BY hc.fecha_historial DESC, hc.created_at DESC
        ";

        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('iii', $idPaciente, $limit, $offset);
        } else {
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('i', $idPaciente);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $historiales = [];
        while ($data = $result->fetch_assoc()) {
            $historiales[] = new HistorialCuidadorEntity($data);
        }

        return $historiales;
    }

    /**
     * Obtiene historiales por cuidador
     * 
     * @param int $idCuidador
     * @param int|null $limit
     * @param int $offset
     * @return array Array de HistorialCuidadorEntity
     */
    public function getByCuidador(int $idCuidador, ?int $limit = null, int $offset = 0): array
    {
        $query = "
            SELECT 
                hc.*,
                p.nompaciente as nombre_paciente,
                c.name as nombre_cuidador,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} hc
            LEFT JOIN pacientes p ON hc.id_paciente = p.idpaciente
            LEFT JOIN users c ON hc.id_cuidador = c.id
            LEFT JOIN users u1 ON hc.created_by = u1.id
            LEFT JOIN users u2 ON hc.updated_by = u2.id
            WHERE hc.id_cuidador = ?
            ORDER BY hc.fecha_historial DESC, hc.created_at DESC
        ";

        if ($limit !== null) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('iii', $idCuidador, $limit, $offset);
        } else {
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('i', $idCuidador);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $historiales = [];
        while ($data = $result->fetch_assoc()) {
            $historiales[] = new HistorialCuidadorEntity($data);
        }

        return $historiales;
    }

    /**
     * Busca historiales con criterios complejos
     * 
     * @param array $searchData Array con condiciones de búsqueda
     * @return array Array de HistorialCuidadorEntity
     */
    public function search(array $searchData): array
    {
        $whereClauses = $searchData['conditions'] ?? [];
        $params = $searchData['params'] ?? [];
        $types = $searchData['types'] ?? '';
        $limit = $searchData['limit'] ?? 50;
        $offset = $searchData['offset'] ?? 0;
        $orderBy = $searchData['order_by'] ?? 'fecha_historial';
        $orderDirection = $searchData['order_direction'] ?? 'DESC';

        $query = "
            SELECT 
                hc.*,
                p.nompaciente as nombre_paciente,
                c.name as nombre_cuidador,
                u1.name as created_by_name,
                u2.name as updated_by_name
            FROM {$this->table} hc
            LEFT JOIN pacientes p ON hc.id_paciente = p.idpaciente
            LEFT JOIN users c ON hc.id_cuidador = c.id
            LEFT JOIN users u1 ON hc.created_by = u1.id
            LEFT JOIN users u2 ON hc.updated_by = u2.id
        ";

        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $query .= " ORDER BY hc.{$orderBy} {$orderDirection}";
        $query .= " LIMIT ? OFFSET ?";

        // Agregar parámetros de paginación
        $allParams = $params;
        $allParams[] = $limit;
        $allParams[] = $offset;
        $allTypes = $types . 'ii';

        $stmt = $this->conexion->prepare($query);
        
        if (!empty($allParams)) {
            $stmt->bind_param($allTypes, ...$allParams);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $historiales = [];
        while ($data = $result->fetch_assoc()) {
            $historiales[] = new HistorialCuidadorEntity($data);
        }

        return $historiales;
    }

    /**
     * Crea un nuevo historial de cuidador
     * 
     * @param array $data Datos del historial
     * @return int ID del historial creado
     * @throws Exception Si falla la inserción
     */
    public function create(array $data): int
    {
        $fields = [];
        $placeholders = [];
        $params = [];
        $types = '';

        foreach ($data as $field => $value) {
            $fields[] = $field;
            $placeholders[] = '?';
            $params[] = $value;
            
            // Determinar tipo de dato
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }

        $query = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                  VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conexion->error);
        }

        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al crear el historial: " . $stmt->error);
        }

        return $this->conexion->insert_id;
    }

    /**
     * Actualiza un historial existente
     * 
     * @param int|string $id ID del historial
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     * @throws Exception Si falla la actualización
     */
    public function update($id, array $data): bool
    {
        $setClauses = [];
        $params = [];
        $types = '';

        foreach ($data as $field => $value) {
            $setClauses[] = "$field = ?";
            $params[] = $value;
            
            // Determinar tipo de dato
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }

        // Agregar el ID al final
        $params[] = $id;
        $types .= 'i';

        $query = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " 
                  WHERE {$this->primaryKey} = ?";

        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conexion->error);
        }

        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar el historial: " . $stmt->error);
        }

        return $stmt->affected_rows > 0;
    }

    /**
     * Elimina un historial (soft delete si existe campo deleted_at, sino hard delete)
     * 
     * @param int|string $id ID del historial
     * @return bool True si se eliminó correctamente
     * @throws Exception Si falla la eliminación
     */
    public function delete($id): bool
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $this->conexion->error);
        }

        $stmt->bind_param('i', $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al eliminar el historial: " . $stmt->error);
        }

        return $stmt->affected_rows > 0;
    }

    /**
     * Cuenta el total de historiales según criterios
     * 
     * @param array $conditions Condiciones de búsqueda
     * @return int Total de registros
     */
    public function count(array $conditions = []): int
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} hc";

        $whereClauses = $conditions['conditions'] ?? [];
        $params = $conditions['params'] ?? [];
        $types = $conditions['types'] ?? '';

        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(' AND ', $whereClauses);
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
     * Verifica si existe un paciente
     * 
     * @param int $idPaciente
     * @return bool
     */
    public function pacienteExists(int $idPaciente): bool
    {
        $query = "SELECT COUNT(*) as total FROM pacientes WHERE idpaciente = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $idPaciente);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data['total'] > 0;
    }

    /**
     * Verifica si existe un cuidador
     * 
     * @param int $idCuidador
     * @return bool
     */
    public function cuidadorExists(int $idCuidador): bool
    {
        $query = "SELECT COUNT(*) as total FROM users WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $idCuidador);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data['total'] > 0;
    }
}
