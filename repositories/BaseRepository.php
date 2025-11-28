<?php

require_once __DIR__ . '/../contracts/RepositoryInterface.php';

/**
 * Clase base para todos los repositorios
 * Implementa funcionalidad común de acceso a datos
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected $conexion;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];

    public function __construct()
    {
        require_once __DIR__ . '/../conexion.php';
        $this->conexion = conexion();
    }

    /**
     * Encuentra un registro por su ID
     */
    public function findById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todos los registros
     */
    public function findAll($orderBy = null)
    {
        $query = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $query .= " ORDER BY " . $this->sanitize($orderBy);
        }
        
        $result = $this->executeQuery($query);
        $records = [];
        
        while ($data = $result->fetch_assoc()) {
            $records[] = $data;
        }
        
        return $records;
    }

    /**
     * Busca registros que coincidan con las condiciones
     */
    public function findWhere(array $conditions)
    {
        $whereClauses = [];
        $params = [];
        $types = '';
        
        foreach ($conditions as $field => $value) {
            $whereClauses[] = "$field = ?";
            $params[] = $value;
            $types .= 's'; // Por simplicidad, tratamos todo como string
        }
        
        $query = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClauses);
        
        $stmt = $this->conexion->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($data = $result->fetch_assoc()) {
            $records[] = $data;
        }
        
        return $records;
    }

    /**
     * Crea un nuevo registro
     */
    public function create(array $data)
    {
        $fields = [];
        $placeholders = [];
        $values = [];
        $types = '';
        
        foreach ($this->fillable as $field) {
            if (isset($data[$field])) {
                $fields[] = $field;
                $placeholders[] = '?';
                $values[] = $data[$field];
                $types .= 's'; // Por simplicidad, tratamos todo como string
            }
        }
        
        // Agregar columnas de auditoría
        $fields[] = 'created_at';
        $placeholders[] = 'NOW()';
        
        if (isset($data['created_by'])) {
            $fields[] = 'created_by';
            $placeholders[] = '?';
            $values[] = $data['created_by'];
            $types .= 'i';
        }
        
        $query = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                 VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->conexion->prepare($query);
        
        if (!empty($values)) {
            $stmt->bind_param($types, ...$values);
        }
        
        $result = $stmt->execute();
        
        if ($result) {
            return $this->conexion->insert_id;
        }
        
        throw new Exception("Error al crear el registro: " . $this->conexion->error);
    }

    /**
     * Actualiza un registro existente
     */
    public function update($id, array $data)
    {
        $updates = [];
        $values = [];
        $types = '';
        
        foreach ($this->fillable as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
                $types .= 's';
            }
        }
        
        // Agregar columnas de auditoría
        $updates[] = "updated_at = NOW()";
        
        if (isset($data['updated_by'])) {
            $updates[] = "updated_by = ?";
            $values[] = $data['updated_by'];
            $types .= 'i';
        }
        
        // Agregar el ID para la condición WHERE
        $values[] = $id;
        $types .= 's';
        
        $query = "UPDATE {$this->table} SET " . implode(', ', $updates) . 
                " WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param($types, ...$values);
        
        return $stmt->execute();
    }

    /**
     * Elimina un registro
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('s', $id);
        
        return $stmt->execute();
    }

    /**
     * Verifica si existe un registro
     */
    public function exists($id)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        return $data['count'] > 0;
    }

    /**
     * Cuenta registros que coincidan con las condiciones
     */
    public function count(array $conditions = [])
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($conditions)) {
            $whereClauses = [];
            $params = [];
            $types = '';
            
            foreach ($conditions as $field => $value) {
                $whereClauses[] = "$field = ?";
                $params[] = $value;
                $types .= 's';
            }
            
            $query .= " WHERE " . implode(' AND ', $whereClauses);
            
            $stmt = $this->conexion->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->executeQuery($query);
        }
        
        $data = $result->fetch_assoc();
        return (int) $data['count'];
    }

    /**
     * Ejecuta una consulta personalizada
     */
    protected function executeQuery($query, array $params = [])
    {
        if (!empty($params)) {
            $stmt = $this->conexion->prepare($query);
            $types = str_repeat('s', count($params)); // Por simplicidad, todo como string
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->get_result();
        } else {
            $result = mysqli_query($this->conexion, $query);
            
            if (!$result) {
                throw new Exception("Error en la consulta: " . mysqli_error($this->conexion));
            }
            
            return $result;
        }
    }

    /**
     * Sanitiza una cadena (método legacy, se recomienda usar prepared statements)
     */
    protected function sanitize($string)
    {
        return mysqli_real_escape_string($this->conexion, $string);
    }

    /**
     * Obtiene la conexión a la base de datos
     */
    protected function getConnection()
    {
        return $this->conexion;
    }

    /**
     * Inicia una transacción
     */
    public function beginTransaction()
    {
        return $this->conexion->autocommit(false);
    }

    /**
     * Confirma una transacción
     */
    public function commit()
    {
        $result = $this->conexion->commit();
        $this->conexion->autocommit(true);
        return $result;
    }

    /**
     * Revierte una transacción
     */
    public function rollback()
    {
        $result = $this->conexion->rollback();
        $this->conexion->autocommit(true);
        return $result;
    }

    /**
     * Obtiene la conexión de base de datos
     */
    public function getConexion()
    {
        return $this->conexion;
    }
}

?>