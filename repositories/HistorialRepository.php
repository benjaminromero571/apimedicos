<?php

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../entities/HistorialEntity.php';

class HistorialRepository extends BaseRepository
{
    protected $table = 'historial';
    protected $fillable = [
        'idpaciente', 'fechahistorial', 'pesohistorial', 'tallahistorial',
        'fchistorial', 'frhistorial', 'ahhistorial', 'apnphistorial',
        'hemotipohistorial', 'alergiashistorial', 'apphistorial', 'citahistorial',
        'diagnostico'
    ];

    /**
     * Obtener todos los historiales médicos
     */
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT 
                    h.idhistorial, h.idpaciente, h.fechahistorial, h.pesohistorial,
                    h.tallahistorial, h.fchistorial, h.frhistorial, h.ahhistorial,
                    h.apnphistorial, h.hemotipohistorial, h.alergiashistorial,
                    h.apphistorial, h.citahistorial, h.diagnostico,
                    h.created_at, h.created_by, h.updated_at, h.updated_by,
                    p.nompaciente,
                    p.rutpaciente,
                    uc.name as created_by_name,
                    uu.name as updated_by_name
                FROM historial h 
                LEFT JOIN pacientes p ON h.idpaciente = p.idpaciente
                LEFT JOIN users uc ON h.created_by = uc.id
                LEFT JOIN users uu ON h.updated_by = uu.id
                ORDER BY h.fechahistorial DESC";
        
        if ($limit) {
            $sql .= " LIMIT $offset, $limit";
        }

        $result = $this->conexion->query($sql);
        
        if (!$result) {
            return false;
        }

        $historiales = [];
        while ($row = $result->fetch_assoc()) {
            $historiales[] = $this->mapToEntity($row);
        }
        
        return $historiales;
    }

    /**
     * Obtener historial por ID
     */
    public function getById($id)
    {
        $stmt = $this->conexion->prepare("
            SELECT 
                h.idhistorial, h.idpaciente, h.fechahistorial, h.pesohistorial,
                h.tallahistorial, h.fchistorial, h.frhistorial, h.ahhistorial,
                h.apnphistorial, h.hemotipohistorial, h.alergiashistorial,
                h.apphistorial, h.citahistorial, h.diagnostico,
                h.created_at, h.created_by, h.updated_at, h.updated_by,
                p.nompaciente,
                p.rutpaciente,
                uc.name as created_by_name,
                uu.name as updated_by_name
            FROM historial h 
            LEFT JOIN pacientes p ON h.idpaciente = p.idpaciente
            LEFT JOIN users uc ON h.created_by = uc.id
            LEFT JOIN users uu ON h.updated_by = uu.id
            WHERE h.idhistorial = ?
        ");
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->mapToEntity($row);
        }
        
        return null;
    }

    /**
     * Obtener historiales por ID de paciente
     */
    public function getByPacienteId($idpaciente, $limit = null, $offset = 0)
    {
        $sql = "SELECT 
                    h.idhistorial, h.idpaciente, h.fechahistorial, h.pesohistorial,
                    h.tallahistorial, h.fchistorial, h.frhistorial, h.ahhistorial,
                    h.apnphistorial, h.hemotipohistorial, h.alergiashistorial,
                    h.apphistorial, h.citahistorial, h.diagnostico,
                    h.created_at, h.created_by, h.updated_at, h.updated_by,
                    p.nompaciente,
                    p.rutpaciente,
                    uc.name as created_by_name,
                    uu.name as updated_by_name
                FROM historial h 
                LEFT JOIN pacientes p ON h.idpaciente = p.idpaciente
                LEFT JOIN users uc ON h.created_by = uc.id
                LEFT JOIN users uu ON h.updated_by = uu.id
                WHERE h.idpaciente = ?
                ORDER BY h.fechahistorial DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?, ?";
        }

        $stmt = $this->conexion->prepare($sql);
        
        if ($limit) {
            $stmt->bind_param("iii", $idpaciente, $offset, $limit);
        } else {
            $stmt->bind_param("i", $idpaciente);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $historiales = [];
        while ($row = $result->fetch_assoc()) {
            $historiales[] = $this->mapToEntity($row);
        }
        
        return $historiales;
    }

    // Método create heredado de BaseRepository

    // Método update heredado de BaseRepository

    /**
     * Eliminar historial médico
     */
    public function delete($id)
    {
        $stmt = $this->conexion->prepare("DELETE FROM historial WHERE idhistorial = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Buscar historiales por criterios médicos
     */
    public function search($criteria, $limit = 20, $offset = 0)
    {
        $conditions = [];
        $params = [];
        $types = "";

        if (!empty($criteria['nombre_paciente'])) {
            $conditions[] = "(p.nombrepaciente LIKE ? OR p.apellidopaciente LIKE ?)";
            $searchTerm = '%' . $criteria['nombre_paciente'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }

        if (!empty($criteria['rutpaciente'])) {
            $conditions[] = "p.rutpaciente LIKE ?";
            $params[] = '%' . $criteria['rutpaciente'] . '%';
            $types .= "s";
        }

        if (!empty($criteria['fecha_desde'])) {
            $conditions[] = "h.fechahistorial >= ?";
            $params[] = $criteria['fecha_desde'];
            $types .= "s";
        }

        if (!empty($criteria['fecha_hasta'])) {
            $conditions[] = "h.fechahistorial <= ?";
            $params[] = $criteria['fecha_hasta'];
            $types .= "s";
        }

        if (!empty($criteria['alergia'])) {
            $conditions[] = "h.alergiashistorial LIKE ?";
            $params[] = '%' . $criteria['alergia'] . '%';
            $types .= "s";
        }

        $sql = "SELECT 
                    h.idhistorial, h.idpaciente, h.fechahistorial, h.pesohistorial,
                    h.tallahistorial, h.fchistorial, h.frhistorial, h.ahhistorial,
                    h.apnphistorial, h.hemotipohistorial, h.alergiashistorial,
                    h.apphistorial, h.citahistorial, h.diagnostico,
                    h.created_at, h.created_by, h.updated_at, h.updated_by,
                    CONCAT(p.nombrepaciente, ' ', p.apellidopaciente) as nombre_paciente,
                    p.nompaciente,
                    p.rutpaciente,
                    uc.name as created_by_name,
                    uu.name as updated_by_name
                FROM historial h 
                LEFT JOIN pacientes p ON h.idpaciente = p.idpaciente
                LEFT JOIN users uc ON h.created_by = uc.id
                LEFT JOIN users uu ON h.updated_by = uu.id";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY h.fechahistorial DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;
        $types .= "ii";

        $stmt = $this->conexion->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $historiales = [];
        while ($row = $result->fetch_assoc()) {
            $historiales[] = $this->mapToEntity($row);
        }
        
        return $historiales;
    }

    /**
     * Obtener último historial de un paciente
     */
    public function getLastByPaciente($idpaciente)
    {
        $stmt = $this->conexion->prepare("
            SELECT 
                h.idhistorial, h.idpaciente, h.fechahistorial, h.pesohistorial,
                h.tallahistorial, h.fchistorial, h.frhistorial, h.ahhistorial,
                h.apnphistorial, h.hemotipohistorial, h.alergiashistorial,
                h.apphistorial, h.citahistorial, h.diagnostico,
                h.created_at, h.created_by, h.updated_at, h.updated_by,
                p.nompaciente,
                p.rutpaciente,
                uc.name as created_by_name,
                uu.name as updated_by_name
            FROM historial h 
            LEFT JOIN pacientes p ON h.idpaciente = p.idpaciente
            LEFT JOIN users uc ON h.created_by = uc.id
            LEFT JOIN users uu ON h.updated_by = uu.id
            WHERE h.idpaciente = ?
            ORDER BY h.fechahistorial DESC, h.idhistorial DESC
            LIMIT 1
        ");
        
        $stmt->bind_param("i", $idpaciente);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->mapToEntity($row);
        }
        
        return null;
    }

    /**
     * Contar total de historiales
     */
    public function count($criteria = [])
    {
        $conditions = [];
        $params = [];
        $types = "";

        if (!empty($criteria['nombre_paciente'])) {
            $conditions[] = "(p.nombrepaciente LIKE ? OR p.apellidopaciente LIKE ?)";
            $searchTerm = '%' . $criteria['nombre_paciente'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }

        if (!empty($criteria['rutpaciente'])) {
            $conditions[] = "p.rutpaciente LIKE ?";
            $params[] = '%' . $criteria['rutpaciente'] . '%';
            $types .= "s";
        }

        $sql = "SELECT COUNT(*) as total 
                FROM historial h 
                LEFT JOIN pacientes p ON h.idpaciente = p.idpaciente";

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
     * Obtener estadísticas médicas de un paciente
     */
    public function getEstadisticasPaciente($idpaciente)
    {
        $stmt = $this->conexion->prepare("
            SELECT 
                COUNT(*) as total_consultas,
                AVG(pesohistorial) as peso_promedio,
                MIN(pesohistorial) as peso_minimo,
                MAX(pesohistorial) as peso_maximo,
                AVG(tallahistorial) as talla_promedio,
                AVG(fchistorial) as fc_promedio,
                AVG(frhistorial) as fr_promedio,
                MIN(fechahistorial) as primera_consulta,
                MAX(fechahistorial) as ultima_consulta
            FROM historial 
            WHERE idpaciente = ? AND pesohistorial > 0
        ");
        
        $stmt->bind_param("i", $idpaciente);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    /**
     * Mapear datos de la base de datos a entidad
     */
    protected function mapToEntity($data)
    {
        return new HistorialEntity([
            'idhistorial' => $data['idhistorial'] ?? null,
            'idpaciente' => $data['idpaciente'] ?? null,
            'fechahistorial' => $data['fechahistorial'] ?? null,
            'pesohistorial' => $data['pesohistorial'] ?? null,
            'tallahistorial' => $data['tallahistorial'] ?? null,
            'fchistorial' => $data['fchistorial'] ?? null,
            'frhistorial' => $data['frhistorial'] ?? null,
            'ahhistorial' => $data['ahhistorial'] ?? null,
            'apnphistorial' => $data['apnphistorial'] ?? null,
            'hemotipohistorial' => $data['hemotipohistorial'] ?? null,
            'alergiashistorial' => $data['alergiashistorial'] ?? null,
            'apphistorial' => $data['apphistorial'] ?? null,
            'citahistorial' => $data['citahistorial'] ?? null,
            'diagnostico' => $data['diagnostico'] ?? null,
            // nompaciente viene del JOIN con tabla pacientes
            'nompaciente' => $data['nombre_paciente'] ?? $data['nompaciente'] ?? null,
            // Datos adicionales del JOIN
            'rutpaciente' => $data['rutpaciente'] ?? null,
            // Campos de auditoría
            'created_at' => $data['created_at'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'created_by_name' => $data['created_by_name'] ?? null,
            'updated_at' => $data['updated_at'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
            'updated_by_name' => $data['updated_by_name'] ?? null
        ]);
    }
}