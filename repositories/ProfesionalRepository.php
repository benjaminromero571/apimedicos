<?php

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../entities/ProfesionalEntity.php';

class ProfesionalRepository extends BaseRepository
{
    protected $table = 'profesionales';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'nombre',
        'telefono',
        'documento',
        'especialidad',
        'id_user'
    ];

    /**
     * Obtiene todos los profesionales ordenados
     */
    public function getAll($orderBy = 'nombre ASC', $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        
        if ($limit) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }

        $result = $this->conexion->query($sql);
        
        if (!$result) {
            return false;
        }

        $profesionales = [];
        while ($row = $result->fetch_assoc()) {
            $profesionales[] = $this->mapToEntity($row);
        }
        
        return $profesionales;
    }

    /**
     * Obtiene un profesional por ID
     */
    public function getById($id)
    {
        $stmt = $this->conexion->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->mapToEntity($row);
        }
        
        return null;
    }

    /**
     * Busca un profesional por documento/cédula
     */
    public function getByDocumento($documento)
    {
        $stmt = $this->conexion->prepare("SELECT * FROM {$this->table} WHERE documento = ?");
        $stmt->bind_param("s", $documento);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->mapToEntity($row);
        }
        
        return null;
    }

    /**
     * Método de compatibilidad para buscar por cédula
     */
    public function getByCedula($cedula)
    {
        return $this->getByDocumento($cedula);
    }

    /**
     * Obtiene profesionales por especialidad
     */
    public function getByEspecialidad($especialidad, $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE especialidad = ? ORDER BY nombre ASC";
        
        if ($limit) {
            $sql .= " LIMIT ?, ?";
        }

        $stmt = $this->conexion->prepare($sql);
        
        if ($limit) {
            $stmt->bind_param("sii", $especialidad, $offset, $limit);
        } else {
            $stmt->bind_param("s", $especialidad);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $profesionales = [];
        while ($row = $result->fetch_assoc()) {
            $profesionales[] = $this->mapToEntity($row);
        }
        
        return $profesionales;
    }

    /**
     * Busca profesionales por nombre (búsqueda parcial)
     */
    public function searchByName($name, $limit = 20, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE nombre LIKE ? 
                ORDER BY nombre ASC 
                LIMIT ?, ?";
        
        $searchTerm = '%' . $name . '%';
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("sii", $searchTerm, $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $profesionales = [];
        while ($row = $result->fetch_assoc()) {
            $profesionales[] = $this->mapToEntity($row);
        }
        
        return $profesionales;
    }

    /**
     * Obtiene todas las especialidades únicas
     */
    public function getEspecialidades()
    {
        $sql = "SELECT DISTINCT especialidad 
                FROM {$this->table} 
                WHERE especialidad != '' AND especialidad IS NOT NULL
                ORDER BY especialidad ASC";
        
        $result = $this->conexion->query($sql);
        $especialidades = [];
        
        while ($row = $result->fetch_assoc()) {
            $especialidades[] = $row['especialidad'];
        }
        
        return $especialidades;
    }

    /**
     * Crea un nuevo profesional
     */
    public function create($data)
    {
        // Validar que el usuario existe si se proporciona id_user
        if (!empty($data['id_user'])) {
            require_once __DIR__ . '/UserRepository.php';
            $userRepo = new UserRepository();
            if (!$userRepo->exists($data['id_user'])) {
                throw new Exception("El usuario con ID {$data['id_user']} no existe");
            }
        }

        $sql = "INSERT INTO {$this->table} (nombre, telefono, documento, especialidad, id_user) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param(
            "ssssi",
            $data['nombre'],
            $data['telefono'],
            $data['documento'],
            $data['especialidad'],
            $data['id_user']
        );
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id;
        }
        
        return false;
    }

    /**
     * Actualiza un profesional existente
     */
    public function update($id, $data)
    {
        // Validar que el usuario existe si se proporciona id_user
        if (!empty($data['id_user'])) {
            require_once __DIR__ . '/UserRepository.php';
            $userRepo = new UserRepository();
            if (!$userRepo->exists($data['id_user'])) {
                throw new Exception("El usuario con ID {$data['id_user']} no existe");
            }
        }

        $sql = "UPDATE {$this->table} SET 
                    nombre = ?, telefono = ?, documento = ?, especialidad = ?, id_user = ?
                WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param(
            "ssssii",
            $data['nombre'],
            $data['telefono'],
            $data['documento'],
            $data['especialidad'],
            $data['id_user'],
            $id
        );
        
        return $stmt->execute();
    }

    /**
     * Elimina un profesional
     */
    public function delete($id)
    {
        $stmt = $this->conexion->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Busca profesionales según múltiples criterios
     */
    public function search($criteria, $limit = 20, $offset = 0)
    {
        $conditions = [];
        $params = [];
        $types = "";

        if (!empty($criteria['nombre'])) {
            $conditions[] = "nombre LIKE ?";
            $params[] = '%' . $criteria['nombre'] . '%';
            $types .= "s";
        }

        if (!empty($criteria['especialidad'])) {
            $conditions[] = "especialidad = ?";
            $params[] = $criteria['especialidad'];
            $types .= "s";
        }

        if (!empty($criteria['documento'])) {
            $conditions[] = "documento LIKE ?";
            $params[] = '%' . $criteria['documento'] . '%';
            $types .= "s";
        }

        if (!empty($criteria['telefono'])) {
            $conditions[] = "telefono LIKE ?";
            $params[] = '%' . $criteria['telefono'] . '%';
            $types .= "s";
        }

        if (!empty($criteria['id_user'])) {
            $conditions[] = "id_user = ?";
            $params[] = $criteria['id_user'];
            $types .= "i";
        }

        $sql = "SELECT * FROM {$this->table}";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY nombre ASC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;
        $types .= "ii";

        $stmt = $this->conexion->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $profesionales = [];
        while ($row = $result->fetch_assoc()) {
            $profesionales[] = $this->mapToEntity($row);
        }
        
        return $profesionales;
    }

    /**
     * Cuenta el total de profesionales
     */
    public function count($criteria = [])
    {
        $conditions = [];
        $params = [];
        $types = "";

        if (!empty($criteria['nombre'])) {
            $conditions[] = "nombre LIKE ?";
            $params[] = '%' . $criteria['nombre'] . '%';
            $types .= "s";
        }

        if (!empty($criteria['especialidad'])) {
            $conditions[] = "especialidad = ?";
            $params[] = $criteria['especialidad'];
            $types .= "s";
        }

        if (!empty($criteria['id_user'])) {
            $conditions[] = "id_user = ?";
            $params[] = $criteria['id_user'];
            $types .= "i";
        }

        $sql = "SELECT COUNT(*) as total FROM {$this->table}";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->conexion->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return (int) $row['total'];
        }
        
        return 0;
    }

    /**
     * Verifica si existe un profesional con el mismo documento
     */
    public function existsByDocumento($documento, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE documento = ?";
        $params = [$documento];
        $types = "s";
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
            $types .= "i";
        }
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    /**
     * Método de compatibilidad para verificar cédula
     */
    public function existsByCedula($cedula, $excludeId = null)
    {
        return $this->existsByDocumento($cedula, $excludeId);
    }

    /**
     * Obtiene estadísticas de profesionales por especialidad
     */
    public function getEstadisticasByEspecialidad()
    {
        $sql = "SELECT 
                    especialidad,
                    COUNT(*) as total_profesionales
                FROM {$this->table} 
                WHERE especialidad != '' AND especialidad IS NOT NULL
                GROUP BY especialidad 
                ORDER BY total_profesionales DESC";
        
        $result = $this->conexion->query($sql);
        $estadisticas = [];
        
        while ($row = $result->fetch_assoc()) {
            $estadisticas[] = $row;
        }
        
        return $estadisticas;
    }

    /**
     * Obtiene profesionales con más información (puede expandirse con JOINs)
     */
    public function getWithDetails($id)
    {
        $profesional = $this->getById($id);
        
        if (!$profesional) {
            return null;
        }
        
        // Aquí se podrían agregar JOINs con otras tablas si fuera necesario
        // Por ejemplo, asignaciones, citas, etc.
        
        return $profesional;
    }

    /**
     * Mapear datos de la base de datos a entidad
     */
    protected function mapToEntity($data)
    {
        return new ProfesionalEntity([
            'id' => $data['id'] ?? null,
            'nombre' => $data['nombre'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'documento' => $data['documento'] ?? null,
            'especialidad' => $data['especialidad'] ?? null,
            'id_user' => $data['id_user'] ?? null
        ]);
    }

    /**
     * Obtiene profesionales por usuario
     */
    public function getByUserId($userId, $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_user = ? ORDER BY nombre ASC";
        
        if ($limit) {
            $sql .= " LIMIT ?, ?";
        }

        $stmt = $this->conexion->prepare($sql);
        
        if ($limit) {
            $stmt->bind_param("iii", $userId, $offset, $limit);
        } else {
            $stmt->bind_param("i", $userId);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $profesionales = [];
        while ($row = $result->fetch_assoc()) {
            $profesionales[] = $this->mapToEntity($row);
        }
        
        return $profesionales;
    }

    /**
     * Obtiene profesionales sin usuario asignado
     */
    public function getWithoutUser($limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_user IS NULL ORDER BY nombre ASC";
        
        if ($limit) {
            $sql .= " LIMIT ?, ?";
        }

        $stmt = $this->conexion->prepare($sql);
        
        if ($limit) {
            $stmt->bind_param("ii", $offset, $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $profesionales = [];
        while ($row = $result->fetch_assoc()) {
            $profesionales[] = $this->mapToEntity($row);
        }
        
        return $profesionales;
    }
}

?>