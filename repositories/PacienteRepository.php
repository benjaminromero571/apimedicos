<?php

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../entities/PacienteEntity.php';

/**
 * Repository para manejar las operaciones de acceso a datos de Pacientes
 */
class PacienteRepository extends BaseRepository
{
    protected $table = 'pacientes';
    protected $primaryKey = 'idpaciente';
    protected $fillable = [
        'rutpaciente',
        'nompaciente',
        'edadpaciente',
        'telpaciente',
        'dirpaciente'
    ];

    /**
     * Busca un paciente por RUT
     */
    public function findByRut($rut)
    {
        $query = "SELECT * FROM {$this->table} WHERE rutpaciente = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('s', $rut);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Obtiene todos los pacientes ordenados por nombre
     */
    public function findAllOrdered($orderBy = 'nompaciente ASC')
    {
        return $this->findAll($orderBy);
    }

    /**
     * Busca pacientes por nombre (búsqueda parcial)
     */
    public function searchByName($name)
    {
        $searchTerm = '%' . $name . '%';
        $query = "SELECT * FROM {$this->table} 
                 WHERE nompaciente LIKE ? 
                 ORDER BY nompaciente ASC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('s', $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($data = $result->fetch_assoc()) {
            $records[] = $data;
        }
        
        return $records;
    }

    /**
     * Busca pacientes por múltiples criterios
     */
    public function searchByCriteria(array $criteria)
    {
        $whereClauses = [];
        $params = [];
        $types = '';

        if (!empty($criteria['nombre'])) {
            $whereClauses[] = "nompaciente LIKE ?";
            $params[] = '%' . $criteria['nombre'] . '%';
            $types .= 's';
        }

        if (!empty($criteria['rut'])) {
            $whereClauses[] = "rutpaciente LIKE ?";
            $params[] = '%' . $criteria['rut'] . '%';
            $types .= 's';
        }

        if (!empty($criteria['edad_min'])) {
            $whereClauses[] = "edadpaciente >= ?";
            $params[] = $criteria['edad_min'];
            $types .= 'i';
        }

        if (!empty($criteria['edad_max'])) {
            $whereClauses[] = "edadpaciente <= ?";
            $params[] = $criteria['edad_max'];
            $types .= 'i';
        }

        if (empty($whereClauses)) {
            return $this->findAll();
        }

        $query = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClauses) . " ORDER BY nompaciente ASC";
        
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
     * Verifica si existe un paciente con el RUT dado (excluyendo un ID específico)
     */
    public function existsByRut($rut, $excludeId = null)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE rutpaciente = ?";
        $params = [$rut];
        $types = 's';

        if ($excludeId !== null) {
            $query .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
            $types .= 's';
        }

        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data['count'] > 0;
    }

    /**
     * Obtiene pacientes con historial médico
     */
    public function findWithHistorial($pacienteId = null)
    {
        if ($pacienteId) {
            $query = "SELECT p.*, h.idhistorial, h.fechahistorial, h.diagnostico, h.pesohistorial, h.tallahistorial, 
                             h.fchistorial, h.frhistorial, h.ahhistorial
                     FROM {$this->table} p
                     LEFT JOIN historial h ON p.idpaciente = h.idpaciente
                     WHERE p.idpaciente = ?
                     ORDER BY h.fechahistorial DESC";
            
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('s', $pacienteId);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $query = "SELECT p.*, COUNT(h.idhistorial) as total_historiales
                     FROM {$this->table} p
                     LEFT JOIN historial h ON p.idpaciente = h.idpaciente
                     GROUP BY p.idpaciente
                     ORDER BY p.nompaciente ASC";
            
            $result = $this->executeQuery($query);
        }

        $records = [];
        while ($data = $result->fetch_assoc()) {
            $records[] = $data;
        }

        return $records;
    }

    /**
     * Cuenta el total de pacientes
     */
    public function countAll()
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->executeQuery($query);
        $data = $result->fetch_assoc();
        return (int)$data['total'];
    }

    /**
     * Obtiene pacientes con sus asignaciones
     */
    public function findWithAsignaciones($pacienteId = null)
    {
        if ($pacienteId) {
            $query = "SELECT p.*, a.id as asignacion_id, a.user_id, u.name as user_name, u.rol as user_rol
                     FROM {$this->table} p
                     LEFT JOIN asignaciones a ON p.idpaciente = a.paciente_id
                     LEFT JOIN users u ON a.user_id = u.id
                     WHERE p.idpaciente = ?";
            
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('s', $pacienteId);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $query = "SELECT p.*, COUNT(a.id) as total_asignaciones
                     FROM {$this->table} p
                     LEFT JOIN asignaciones a ON p.idpaciente = a.paciente_id
                     GROUP BY p.idpaciente
                     ORDER BY p.nompaciente ASC";
            
            $result = $this->executeQuery($query);
        }

        $records = [];
        while ($data = $result->fetch_assoc()) {
            $records[] = $data;
        }

        return $records;
    }

    /**
     * Verifica si un paciente tiene asignaciones activas
     */
    public function hasAsignaciones($pacienteId)
    {
        $query = "SELECT COUNT(*) as count FROM asignaciones WHERE paciente_id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('s', $pacienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data['count'] > 0;
    }

    /**
     * Obtiene estadísticas de pacientes
     */
    public function getStats()
    {
        $query = "SELECT 
                    COUNT(*) as total_pacientes,
                    AVG(edadpaciente) as edad_promedio,
                    MIN(edadpaciente) as edad_minima,
                    MAX(edadpaciente) as edad_maxima,
                    COUNT(CASE WHEN edadpaciente < 18 THEN 1 END) as menores_edad,
                    COUNT(CASE WHEN edadpaciente >= 65 THEN 1 END) as adultos_mayores
                  FROM {$this->table}";
        
        $result = $this->executeQuery($query);
        return $result->fetch_assoc();
    }

    /**
     * Busca pacientes con paginación
     */
    public function findWithPagination($page = 1, $limit = 10, $searchTerm = null)
    {
        $offset = ($page - 1) * $limit;
        
        if ($searchTerm) {
            $searchParam = '%' . $searchTerm . '%';
            $query = "SELECT * FROM {$this->table} 
                     WHERE nompaciente LIKE ? OR rutpaciente LIKE ?
                     ORDER BY nompaciente ASC 
                     LIMIT ? OFFSET ?";
            
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('ssii', $searchParam, $searchParam, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $query = "SELECT * FROM {$this->table} 
                     ORDER BY nompaciente ASC 
                     LIMIT ? OFFSET ?";
            
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('ii', $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        
        $records = [];
        while ($data = $result->fetch_assoc()) {
            $records[] = $data;
        }
        
        return $records;
    }

    /**
     * Cuenta el total de registros para paginación con búsqueda
     */
    public function getTotalCountWithSearch($searchTerm = null)
    {
        if ($searchTerm) {
            $searchParam = '%' . $searchTerm . '%';
            $query = "SELECT COUNT(*) as count FROM {$this->table} 
                     WHERE nompaciente LIKE ? OR rutpaciente LIKE ?";
            
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param('ss', $searchParam, $searchParam);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            return $this->count();
        }
        
        $data = $result->fetch_assoc();
        return (int) $data['count'];
    }

    /**
     * Convierte un array de datos a entidad PacienteEntity
     */
    public function toEntity(array $data)
    {
        return PacienteEntity::fromArray($data);
    }

    /**
     * Convierte múltiples arrays a entidades
     */
    public function toEntities(array $dataList)
    {
        return array_map(function($data) {
            return $this->toEntity($data);
        }, $dataList);
    }

    /**
     * Obtiene los cuidadores de un paciente
     */
    public function getCuidadores($pacienteId)
    {
        $query = "SELECT u.*, pc.created_at, pc.created_by
                 FROM paciente_cuidador pc
                 INNER JOIN users u ON pc.id_cuidador = u.id
                 WHERE pc.id_paciente = ? AND u.rol = 'Cuidador'
                 ORDER BY u.name ASC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $pacienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $cuidadores = [];
        while ($data = $result->fetch_assoc()) {
            $cuidadores[] = $data;
        }
        
        return $cuidadores;
    }

    /**
     * Asigna un cuidador a un paciente
     */
    public function assignCuidador($pacienteId, $cuidadorId, $userId = null)
    {
        // Validar que el cuidador existe
        $query = "SELECT COUNT(*) as count FROM users WHERE id = ? AND rol = 'Cuidador'";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $cuidadorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data['count'] == 0) {
            throw new Exception('El cuidador especificado no existe o no tiene el rol correcto');
        }
        
        // Verificar si ya existe la asignación
        $query = "SELECT COUNT(*) as count FROM paciente_cuidador WHERE id_paciente = ? AND id_cuidador = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $pacienteId, $cuidadorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data['count'] > 0) {
            throw new Exception('El cuidador ya está asignado a este paciente');
        }
        
        // Crear la asignación
        $query = "INSERT INTO paciente_cuidador (id_paciente, id_cuidador, created_by) VALUES (?, ?, ?)";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('iii', $pacienteId, $cuidadorId, $userId);
        
        return $stmt->execute();
    }

    /**
     * Remueve la asignación de un cuidador a un paciente
     */
    public function unassignCuidador($pacienteId, $cuidadorId)
    {
        $query = "DELETE FROM paciente_cuidador WHERE id_paciente = ? AND id_cuidador = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $pacienteId, $cuidadorId);
        
        return $stmt->execute();
    }

    /**
     * Remueve todas las asignaciones de cuidadores de un paciente
     */
    public function unassignAllCuidadores($pacienteId)
    {
        $query = "DELETE FROM paciente_cuidador WHERE id_paciente = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $pacienteId);
        
        return $stmt->execute();
    }

    /**
     * Obtiene los profesionales asignados a un paciente
     */
    public function getProfesionales($pacienteId)
    {
        $query = "SELECT prof.*, pp.id_paciente, pp.id_profesional, u.name as user_name, u.email as user_email
                 FROM paciente_profesional pp
                 INNER JOIN profesionales prof ON pp.id_profesional = prof.id
                 LEFT JOIN users u ON prof.id_user = u.id
                 WHERE pp.id_paciente = ?
                 ORDER BY prof.nombre ASC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $pacienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $profesionales = [];
        while ($data = $result->fetch_assoc()) {
            $profesionales[] = $data;
        }
        
        return $profesionales;
    }

    /**
     * Asigna un profesional a un paciente
     */
    public function assignProfesional($pacienteId, $profesionalId)
    {
        // Validar que el profesional existe
        $query = "SELECT COUNT(*) as count FROM profesionales WHERE id = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $profesionalId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data['count'] == 0) {
            throw new Exception('El profesional especificado no existe');
        }
        
        // Verificar si ya existe la asignación
        $query = "SELECT COUNT(*) as count FROM paciente_profesional WHERE id_paciente = ? AND id_profesional = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $pacienteId, $profesionalId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data['count'] > 0) {
            throw new Exception('El profesional ya está asignado a este paciente');
        }
        
        // Crear la asignación
        $query = "INSERT INTO paciente_profesional (id_paciente, id_profesional) VALUES (?, ?)";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $pacienteId, $profesionalId);
        
        return $stmt->execute();
    }

    /**
     * Remueve la asignación de un profesional a un paciente
     */
    public function unassignProfesional($pacienteId, $profesionalId)
    {
        $query = "DELETE FROM paciente_profesional WHERE id_paciente = ? AND id_profesional = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $pacienteId, $profesionalId);
        
        return $stmt->execute();
    }

    /**
     * Remueve todas las asignaciones de profesionales de un paciente
     */
    public function unassignAllProfesionales($pacienteId)
    {
        $query = "DELETE FROM paciente_profesional WHERE id_paciente = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $pacienteId);
        
        return $stmt->execute();
    }

    /**
     * Obtiene pacientes asignados a un profesional específico
     */
    public function findByProfesional($profesionalId)
    {
        $query = "SELECT p.*, pp.id_profesional
                 FROM {$this->table} p
                 INNER JOIN paciente_profesional pp ON p.idpaciente = pp.id_paciente
                 WHERE pp.id_profesional = ?
                 ORDER BY p.nompaciente ASC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $profesionalId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($data = $result->fetch_assoc()) {
            $records[] = $data;
        }
        
        return $records;
    }

    /**
     * Obtiene pacientes asignados a un cuidador específico
     */
    public function findByCuidador($cuidadorId)
    {
        $query = "SELECT p.*, u.name as cuidador_name
                 FROM {$this->table} p
                 INNER JOIN paciente_cuidador pc ON p.{$this->primaryKey} = pc.id_paciente
                 INNER JOIN users u ON pc.id_cuidador = u.id
                 WHERE pc.id_cuidador = ?
                 ORDER BY p.nompaciente ASC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $cuidadorId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($data = $result->fetch_assoc()) {
            $records[] = $data;
        }
        
        return $records;
    }

    /**
     * Verifica si un paciente tiene cuidadores asignados
     */
    public function hasCuidadores($pacienteId)
    {
        $query = "SELECT COUNT(*) as count FROM paciente_cuidador WHERE id_paciente = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $pacienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data['count'] > 0;
    }

    /**
     * Verifica si un paciente tiene profesionales asignados
     */
    public function hasProfesionales($pacienteId)
    {
        $query = "SELECT COUNT(*) as count FROM paciente_profesional WHERE id_paciente = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('i', $pacienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data['count'] > 0;
    }

    /**
     * Obtiene estadísticas extendidas incluyendo cuidadores y profesionales
     */
    public function getExtendedStats()
    {
        $query = "SELECT 
                    COUNT(DISTINCT p.idpaciente) as total_pacientes,
                    AVG(p.edadpaciente) as edad_promedio,
                    MIN(p.edadpaciente) as edad_minima,
                    MAX(p.edadpaciente) as edad_maxima,
                    COUNT(CASE WHEN p.edadpaciente < 18 THEN 1 END) as menores_edad,
                    COUNT(CASE WHEN p.edadpaciente >= 65 THEN 1 END) as adultos_mayores,
                    COUNT(DISTINCT pc.id_paciente) as con_cuidador,
                    COUNT(DISTINCT CASE WHEN pc.id_paciente IS NULL THEN p.idpaciente END) as sin_cuidador,
                    COUNT(DISTINCT pp.id_paciente) as con_profesionales,
                    COUNT(DISTINCT pp.id_profesional) as total_profesionales_asignados,
                    COUNT(DISTINCT pc.id_cuidador) as total_cuidadores_asignados
                  FROM {$this->table} p
                  LEFT JOIN paciente_cuidador pc ON p.idpaciente = pc.id_paciente
                  LEFT JOIN paciente_profesional pp ON p.idpaciente = pp.id_paciente";
        
        $result = $this->executeQuery($query);
        return $result->fetch_assoc();
    }
}

?>