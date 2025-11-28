<?php

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../entities/AsignacionEntity.php';

/**
 * Repository para manejar las operaciones de acceso a datos de Asignaciones
 */
class AsignacionRepository extends BaseRepository
{
    protected $table = 'asignaciones';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'paciente_id',
        'fecha_asignacion'
    ];

    /**
     * Obtiene asignaciones por usuario
     */
    public function findByUser($userId)
    {
        return $this->findWhere(['user_id' => $userId]);
    }

    /**
     * Obtiene asignaciones por paciente
     */
    public function findByPaciente($pacienteId)
    {
        return $this->findWhere(['paciente_id' => $pacienteId]);
    }

    /**
     * Verifica si existe una asignación específica
     */
    public function existsAssignment($userId, $pacienteId)
    {
        $result = $this->findWhere([
            'user_id' => $userId,
            'paciente_id' => $pacienteId
        ]);
        
        return count($result) > 0;
    }

    /**
     * Obtiene asignaciones con información completa (con JOINs)
     */
    public function findAllWithDetails()
    {
        $query = "SELECT a.*, u.name as user_name, u.rol as user_rol, 
                         p.nompaciente, p.rutpaciente
                 FROM {$this->table} a 
                 LEFT JOIN users u ON a.user_id = u.id 
                 LEFT JOIN pacientes p ON a.paciente_id = p.idpaciente 
                 ORDER BY a.fecha_asignacion DESC";
        
        $result = $this->executeQuery($query);
        $records = [];
        
        while ($data = $result->fetch_assoc()) {
            $records[] = $data;
        }
        
        return $records;
    }

    /**
     * Obtiene asignaciones de un usuario con detalles del paciente
     */
    public function findByUserWithDetails($userId)
    {
        $query = "SELECT a.*, p.nompaciente, p.rutpaciente, p.edadpaciente, 
                         p.telpaciente, p.dirpaciente
                 FROM {$this->table} a 
                 LEFT JOIN pacientes p ON a.paciente_id = p.idpaciente 
                 WHERE a.user_id = ?
                 ORDER BY a.fecha_asignacion DESC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($data = $result->fetch_assoc()) {
            $records[] = $data;
        }
        
        return $records;
    }

    /**
     * Elimina una asignación específica por user_id y paciente_id
     */
    public function deleteByUserAndPaciente($userId, $pacienteId)
    {
        $query = "DELETE FROM {$this->table} 
                 WHERE user_id = ? AND paciente_id = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ss', $userId, $pacienteId);
        
        return $stmt->execute();
    }

    /**
     * Obtiene estadísticas de asignaciones
     */
    public function getStats()
    {
        $query = "SELECT 
                    COUNT(*) as total_asignaciones,
                    COUNT(DISTINCT user_id) as usuarios_con_asignaciones,
                    COUNT(DISTINCT paciente_id) as pacientes_asignados
                  FROM {$this->table}";
        
        $result = $this->executeQuery($query);
        return $result->fetch_assoc();
    }

    /**
     * Busca una asignación específica por user_id y paciente_id
     */
    public function findByUserAndPaciente($userId, $pacienteId)
    {
        $query = "SELECT * FROM {$this->table} 
                 WHERE user_id = ? AND paciente_id = ?
                 LIMIT 1";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ss', $userId, $pacienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Cuenta asignaciones por usuario
     */
    public function countByUser($userId)
    {
        return $this->count(['user_id' => $userId]);
    }

    /**
     * Cuenta asignaciones por paciente
     */
    public function countByPaciente($pacienteId)
    {
        return $this->count(['paciente_id' => $pacienteId]);
    }

    /**
     * Convierte un array de datos a entidad AsignacionEntity
     */
    public function toEntity(array $data)
    {
        return AsignacionEntity::fromArray($data);
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
     * Busca asignaciones con paginación
     */
    public function findWithPagination($page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM {$this->table} 
                 ORDER BY fecha_asignacion DESC 
                 LIMIT ? OFFSET ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($data = $result->fetch_assoc()) {
            $records[] = $data;
        }
        
        return $records;
    }

    /**
     * Cuenta el total de registros para paginación
     */
    public function getTotalCount()
    {
        return $this->count();
    }
}

?>