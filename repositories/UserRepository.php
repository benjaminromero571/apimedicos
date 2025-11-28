<?php

require_once __DIR__ . '/../repositories/BaseRepository.php';
require_once __DIR__ . '/../entities/UserEntity.php';

/**
 * UserRepository
 * 
 * Repositorio para el manejo de datos de usuarios
 * Contiene todas las consultas y operaciones de base de datos relacionadas con usuarios
 */
class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
        $this->primaryKey = 'id';
        $this->fillable = ['name', 'email', 'password', 'rol'];
    }

    /**
     * Busca un usuario por ID y devuelve UserEntity
     */
    public function findById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data ? new UserEntity($data) : null;
    }

    /**
     * Busca un usuario por email
     */
    public function findByEmail($email)
    {
        $query = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data ? new UserEntity($data) : null;
    }

    /**
     * Obtiene todos los usuarios con filtros opcionales
     */
    public function getAll($orderBy = 'name ASC', $limit = null, $offset = 0)
    {
        $query = "SELECT * FROM {$this->table}";
        
        // Validar orderBy para prevenir inyección SQL
        $allowedFields = ['id', 'name', 'email', 'rol', 'created_at'];
        $allowedDirections = ['ASC', 'DESC'];
        
        if ($orderBy && $orderBy !== 'name ASC') {
            $orderParts = explode(' ', $orderBy);
            $field = $orderParts[0];
            $direction = isset($orderParts[1]) ? strtoupper($orderParts[1]) : 'ASC';
            
            if (in_array($field, $allowedFields) && in_array($direction, $allowedDirections)) {
                $query .= " ORDER BY {$field} {$direction}";
            }
        } else {
            $query .= " ORDER BY name ASC";
        }
        
        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
            if ($offset) {
                $query .= " OFFSET " . (int)$offset;
            }
        }
        
        $result = $this->conexion->query($query);
        
        if (!$result) {
            throw new Exception("Error ejecutando consulta: " . $this->conexion->error);
        }
        
        $users = [];
        while ($data = $result->fetch_assoc()) {
            $users[] = new UserEntity($data);
        }
        
        return $users;
    }

    /**
     * Busca usuarios por rol
     */
    public function getByRol($rol, $orderBy = 'name ASC', $limit = null, $offset = 0)
    {
        $query = "SELECT * FROM {$this->table} WHERE rol = ?";
        
        // Agregar ordenamiento
        $allowedFields = ['id', 'name', 'email', 'created_at'];
        $allowedDirections = ['ASC', 'DESC'];
        
        if ($orderBy) {
            $orderParts = explode(' ', $orderBy);
            $field = $orderParts[0];
            $direction = isset($orderParts[1]) ? strtoupper($orderParts[1]) : 'ASC';
            
            if (in_array($field, $allowedFields) && in_array($direction, $allowedDirections)) {
                $query .= " ORDER BY {$field} {$direction}";
            }
        }
        
        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
            if ($offset) {
                $query .= " OFFSET " . (int)$offset;
            }
        }
        
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        $stmt->bind_param("s", $rol);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $users = [];
        
        while ($data = $result->fetch_assoc()) {
            $users[] = new UserEntity($data);
        }
        
        $stmt->close();
        return $users;
    }

    /**
     * Busca usuarios con criterios múltiples
     */
    public function search($criteria, $orderBy = 'name ASC', $limit = null, $offset = 0)
    {
        $conditions = [];
        $params = [];
        $types = '';
        
        // Construir condiciones dinámicamente
        if (!empty($criteria['name'])) {
            $conditions[] = "name LIKE ?";
            $params[] = "%" . $criteria['name'] . "%";
            $types .= 's';
        }
        
        if (!empty($criteria['email'])) {
            $conditions[] = "email LIKE ?";
            $params[] = "%" . $criteria['email'] . "%";
            $types .= 's';
        }
        
        if (!empty($criteria['rol'])) {
            $conditions[] = "rol = ?";
            $params[] = $criteria['rol'];
            $types .= 's';
        }
        
        if (!empty($criteria['search'])) {
            // Búsqueda general en nombre y email
            $conditions[] = "(name LIKE ? OR email LIKE ?)";
            $searchTerm = "%" . $criteria['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }
        
        $query = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Agregar ordenamiento
        $allowedFields = ['id', 'name', 'email', 'rol', 'created_at'];
        $allowedDirections = ['ASC', 'DESC'];
        
        if ($orderBy) {
            $orderParts = explode(' ', $orderBy);
            $field = $orderParts[0];
            $direction = isset($orderParts[1]) ? strtoupper($orderParts[1]) : 'ASC';
            
            if (in_array($field, $allowedFields) && in_array($direction, $allowedDirections)) {
                $query .= " ORDER BY {$field} {$direction}";
            }
        }
        
        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
            if ($offset) {
                $query .= " OFFSET " . (int)$offset;
            }
        }
        
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($data = $result->fetch_assoc()) {
            $users[] = new UserEntity($data);
        }
        
        $stmt->close();
        return $users;
    }

    /**
     * Crea un nuevo usuario
     */
    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (name, email, password, rol) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        $stmt->bind_param("ssss", $data['name'], $data['email'], $data['password'], $data['rol']);
        
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception("Error creando usuario: " . $error);
        }
        
        $newId = $this->conexion->insert_id;
        $stmt->close();
        
        return $this->findById($newId);
    }

    /**
     * Actualiza un usuario existente
     */
    public function update($id, $data)
    {
        $setParts = [];
        $params = [];
        $types = '';
        
        // Construir SET dinámicamente solo con campos proporcionados
        $allowedFields = ['name', 'email', 'password', 'rol'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $setParts[] = "{$field} = ?";
                $params[] = $data[$field];
                $types .= 's';
            }
        }
        
        if (empty($setParts)) {
            throw new Exception("No hay campos para actualizar");
        }
        
        $query = "UPDATE {$this->table} SET " . implode(", ", $setParts) . " WHERE {$this->primaryKey} = ?";
        $params[] = $id;
        $types .= 'i';
        
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        $stmt->bind_param($types, ...$params);
        
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception("Error actualizando usuario: " . $error);
        }
        
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        return $affectedRows > 0 ? $this->findById($id) : null;
    }

    /**
     * Elimina un usuario
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception("Error eliminando usuario: " . $error);
        }
        
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        return $affectedRows > 0;
    }

    /**
     * Verifica si existe un usuario con el email dado
     */
    public function emailExists($email, $excludeId = null)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];
        $types = 's';
        
        if ($excludeId) {
            $query .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
            $types .= 'i';
        }
        
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    /**
     * Obtiene estadísticas de usuarios por rol
     */
    public function getStatsByRole()
    {
        $query = "SELECT rol, COUNT(*) as count FROM {$this->table} GROUP BY rol ORDER BY count DESC";
        $result = $this->conexion->query($query);
        
        if (!$result) {
            throw new Exception("Error ejecutando consulta: " . $this->conexion->error);
        }
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = [
                'rol' => $row['rol'],
                'count' => (int)$row['count']
            ];
        }
        
        return $stats;
    }

    /**
     * Cuenta el total de usuarios
     */
    public function getTotalCount()
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->conexion->query($query);
        
        if (!$result) {
            throw new Exception("Error ejecutando consulta: " . $this->conexion->error);
        }
        
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    /**
     * Cuenta usuarios por rol específico
     */
    public function countByRole($rol)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE rol = ?";
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        $stmt->bind_param("s", $rol);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['count'];
    }

    /**
     * Obtiene los pacientes asignados a un usuario
     */
    public function getPacientesAsignados($userId, $limit = null, $offset = 0)
    {
        $query = "SELECT p.*, a.fecha_asignacion, a.id as asignacion_id
                 FROM pacientes p 
                 INNER JOIN asignaciones a ON p.idpaciente = a.paciente_id 
                 WHERE a.user_id = ?
                 ORDER BY a.fecha_asignacion DESC";
        
        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
            if ($offset) {
                $query .= " OFFSET " . (int)$offset;
            }
        }
        
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $pacientes = [];
        
        while ($data = $result->fetch_assoc()) {
            $pacientes[] = $data;
        }
        
        $stmt->close();
        return $pacientes;
    }

    /**
     * Obtiene las asignaciones de un usuario
     */
    public function getAsignaciones($userId, $limit = null, $offset = 0)
    {
        $query = "SELECT a.*, p.nompaciente, p.edadpaciente, p.telpaciente
                 FROM asignaciones a
                 INNER JOIN pacientes p ON a.paciente_id = p.idpaciente
                 WHERE a.user_id = ?
                 ORDER BY a.fecha_asignacion DESC";
        
        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
            if ($offset) {
                $query .= " OFFSET " . (int)$offset;
            }
        }
        
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $asignaciones = [];
        
        while ($data = $result->fetch_assoc()) {
            $asignaciones[] = $data;
        }
        
        $stmt->close();
        return $asignaciones;
    }

    /**
     * Verifica si un usuario existe
     */
    public function exists($id)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conexion->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $this->conexion->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    /**
     * Obtiene roles disponibles
     */
    public function getAvailableRoles()
    {
        return ['Administrador', 'Medico', 'Profesional', 'Cuidador'];
    }
}

?>
