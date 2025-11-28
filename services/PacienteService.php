<?php

require_once __DIR__ . '/../contracts/ServiceInterface.php';
require_once __DIR__ . '/../repositories/PacienteRepository.php';
require_once __DIR__ . '/../dto/PacienteDto.php';
require_once __DIR__ . '/../dto/CreatePacienteDto.php';
require_once __DIR__ . '/../dto/PacienteSearchDto.php';
require_once __DIR__ . '/../dto/PacienteDetailDto.php';
require_once __DIR__ . '/../dto/PacienteStatsDto.php';
require_once __DIR__ . '/HistorialService.php';

/**
 * Service para manejar la lógica de negocio de Pacientes
 * Contiene todas las reglas de dominio y validaciones
 */
class PacienteService implements ServiceInterface
{
    private $pacienteRepository;
    private $historialService;
    
    public function __construct(?PacienteRepository $pacienteRepository = null)
    {
        $this->pacienteRepository = $pacienteRepository ?: new PacienteRepository();
        $this->historialService = new HistorialService();
    }

    /**
     * Obtiene un paciente por ID
     */
    public function getById($id)
    {
        $data = $this->pacienteRepository->findById($id);
        
        if (!$data) {
            return null;
        }
        
        return PacienteDto::fromArray($data);
    }

    /**
     * Obtiene todos los pacientes
     */
    public function getAll()
    {
        $dataList = $this->pacienteRepository->findAllOrdered();
        
        return array_map(function($data) {
            return PacienteDto::fromArray($data);
        }, $dataList);
    }

    /**
     * Crea un nuevo paciente
     */
    public function create(array $data)
    {
        $createDto = CreatePacienteDto::fromArray($data);
        
        // Validar datos básicos del DTO
        if (!$createDto->isValid()) {
            throw new Exception('Datos inválidos: ' . implode(', ', $createDto->getValidationErrors()));
        }
        
        // Validaciones de negocio
        $errors = $this->validate($createDto->toArray());
        if (!empty($errors)) {
            throw new Exception('Errores de validación: ' . implode(', ', $errors));
        }
        
        try {
            $this->pacienteRepository->beginTransaction();
            
            $newId = $this->pacienteRepository->create($createDto->toArray());
            
            $this->pacienteRepository->commit();
            
            return $this->getById($newId);
            
        } catch (Exception $e) {
            $this->pacienteRepository->rollback();
            throw $e;
        }
    }

    /**
     * Actualiza un paciente existente
     */
    public function update($id, array $data)
    {
        if (!$this->pacienteRepository->exists($id)) {
            throw new Exception('El paciente no existe');
        }
        
        // Validar datos incluyendo el ID para validaciones de actualización
        $errors = $this->validate($data, $id);
        if (!empty($errors)) {
            throw new Exception('Errores de validación: ' . implode(', ', $errors));
        }
        
        try {
            $this->pacienteRepository->beginTransaction();
            
            $success = $this->pacienteRepository->update($id, $data);
            
            if (!$success) {
                throw new Exception('Error al actualizar el paciente');
            }
            
            $this->pacienteRepository->commit();
            
            return true;
            
        } catch (Exception $e) {
            $this->pacienteRepository->rollback();
            throw $e;
        }
    }

    /**
     * Elimina un paciente
     */
    public function delete($id)
    {
        if (!$this->pacienteRepository->exists($id)) {
            throw new Exception('El paciente no existe');
        }
        
        // Verificar si tiene asignaciones activas
        if ($this->pacienteRepository->hasAsignaciones($id)) {
            throw new Exception('No se puede eliminar el paciente porque tiene asignaciones activas');
        }
        
        return $this->pacienteRepository->delete($id);
    }

    /**
     * Valida los datos según las reglas de negocio
     */
    public function validate(array $data, $id = null)
    {
        $errors = [];

        // Validar RUT
        if (empty($data['rutpaciente'])) {
            $errors[] = 'RUT del paciente es requerido';
        } else {
            if (!$this->validateRut($data['rutpaciente'])) {
                $errors[] = 'RUT del paciente no es válido';
            } else {
                // Verificar unicidad del RUT
                if ($this->pacienteRepository->existsByRut($data['rutpaciente'], $id)) {
                    $errors[] = 'Ya existe un paciente con este RUT';
                }
            }
        }

        // Validar nombre
        if (empty($data['nompaciente'])) {
            $errors[] = 'Nombre del paciente es requerido';
        } elseif (strlen(trim($data['nompaciente'])) < 2) {
            $errors[] = 'Nombre del paciente debe tener al menos 2 caracteres';
        }

        // Validar edad
        if (empty($data['edadpaciente'])) {
            $errors[] = 'Edad del paciente es requerida';
        } elseif (!is_numeric($data['edadpaciente']) || $data['edadpaciente'] < 0 || $data['edadpaciente'] > 150) {
            $errors[] = 'Edad del paciente debe ser un número válido entre 0 y 150';
        }

        // Validar teléfono
        if (empty($data['telpaciente'])) {
            $errors[] = 'Teléfono del paciente es requerido';
        } elseif (!$this->validateTelefono($data['telpaciente'])) {
            $errors[] = 'Teléfono del paciente no tiene un formato válido';
        }

        // Validar dirección
        if (empty($data['dirpaciente'])) {
            $errors[] = 'Dirección del paciente es requerida';
        } elseif (strlen(trim($data['dirpaciente'])) < 5) {
            $errors[] = 'Dirección del paciente debe tener al menos 5 caracteres';
        }

        return $errors;
    }

    /**
     * Busca un paciente por RUT
     */
    public function getByRut($rut)
    {
        $data = $this->pacienteRepository->findByRut($rut);
        
        if (!$data) {
            return null;
        }
        
        return PacienteDto::fromArray($data);
    }

    /**
     * Busca pacientes por nombre
     */
    public function searchByName($name)
    {
        if (empty(trim($name))) {
            throw new Exception('El término de búsqueda no puede estar vacío');
        }
        
        $dataList = $this->pacienteRepository->searchByName($name);
        
        return array_map(function($data) {
            return PacienteDto::fromArray($data);
        }, $dataList);
    }

    /**
     * Busca pacientes por múltiples criterios
     */
    public function searchByCriteria(PacienteSearchDto $searchDto)
    {
        if (!$searchDto->hasSearchCriteria()) {
            return $this->getAll();
        }
        
        $criteria = $searchDto->getActiveCriteria();
        $dataList = $this->pacienteRepository->searchByCriteria($criteria);
        
        return array_map(function($data) {
            return PacienteDto::fromArray($data);
        }, $dataList);
    }

    /**
     * Obtiene un paciente con su información completa (historial, asignaciones, profesionales y cuidador)
     */
    public function getByIdWithDetails($id)
    {
        // Obtener datos básicos del paciente con cuidador
        $pacienteData = $this->pacienteRepository->findWithCuidador($id);
        
        if (!$pacienteData) {
            return null;
        }
        
        // Obtener historiales usando HistorialService
        $historialResult = $this->historialService->getHistorialesByPaciente($id);
        $historialData = [];
        $totalHistoriales = 0;
        
        if ($historialResult['success']) {
            $historialData = $historialResult['data'];
            $totalHistoriales = $historialResult['total'];
        }
        
        // Obtener asignaciones
        $asignacionesData = $this->pacienteRepository->findWithAsignaciones($id);
        
        // Obtener profesionales asignados
        $profesionalesData = $this->pacienteRepository->getProfesionales($id);
        
        // Combinar datos
        $detailData = $pacienteData;
        $detailData['historial'] = $historialData;
        $detailData['asignaciones'] = $asignacionesData;
        $detailData['profesionales'] = $profesionalesData;
        $detailData['total_historiales'] = $totalHistoriales;
        $detailData['total_asignaciones'] = count($asignacionesData);
        $detailData['total_profesionales'] = count($profesionalesData);
        
        // Si hay historiales, agregar estadísticas médicas
        if ($totalHistoriales > 0) {
            $estadisticasResult = $this->historialService->getEstadisticasPaciente($id);
            if ($estadisticasResult['success']) {
                $detailData['estadisticas_medicas'] = $estadisticasResult['data'];
            }
        }
        
        return PacienteDetailDto::fromArray($detailData);
    }

    /**
     * Obtiene todos los pacientes con resumen de información
     */
    public function getAllWithSummary()
    {
        $dataList = $this->pacienteRepository->findWithAsignaciones();
        
        return array_map(function($data) {
            return PacienteDetailDto::fromArray($data);
        }, $dataList);
    }

    /**
     * Obtiene estadísticas básicas de pacientes
     */
    public function getEstadisticas()
    {
        $stats = $this->pacienteRepository->getStats();
        $stats['fecha_generacion'] = date('Y-m-d H:i:s');
        
        return PacienteStatsDto::fromArray($stats);
    }

    /**
     * Obtiene estadísticas extendidas incluyendo cuidadores y profesionales
     */
    public function getEstadisticasExtendidas()
    {
        $stats = $this->pacienteRepository->getExtendedStats();
        $stats['fecha_generacion'] = date('Y-m-d H:i:s');
        
        return PacienteStatsDto::fromArray($stats);
    }

    /**
     * Obtiene pacientes con paginación
     */
    public function getPaginated($page = 1, $limit = 10, $searchTerm = null)
    {
        $dataList = $this->pacienteRepository->findWithPagination($page, $limit, $searchTerm);
        $total = $this->pacienteRepository->getTotalCountWithSearch($searchTerm);
        
        return [
            'data' => array_map(function($data) {
                return PacienteDto::fromArray($data);
            }, $dataList),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_more' => ($page * $limit) < $total,
                'search_term' => $searchTerm
            ]
        ];
    }

    /**
     * Verifica si un paciente tiene asignaciones activas
     */
    public function hasAsignaciones($id)
    {
        return $this->pacienteRepository->hasAsignaciones($id);
    }

    /**
     * Verifica si un paciente tiene profesionales asignados
     */
    public function hasProfesionales($id)
    {
        return $this->pacienteRepository->hasProfesionales($id);
    }

    /**
     * Obtiene los cuidadores de un paciente
     */
    public function getCuidadores($pacienteId)
    {
        if (!$this->pacienteRepository->exists($pacienteId)) {
            throw new Exception('El paciente no existe');
        }

        return $this->pacienteRepository->getCuidadores($pacienteId);
    }

    /**
     * Asigna un cuidador a un paciente
     */
    public function assignCuidador($pacienteId, $cuidadorId, $userId = null)
    {
        if (!$this->pacienteRepository->exists($pacienteId)) {
            throw new Exception('El paciente no existe');
        }

        if (!$this->validateCuidadorExists($cuidadorId)) {
            throw new Exception('El cuidador especificado no existe o no tiene el rol correcto');
        }

        try {
            $this->pacienteRepository->beginTransaction();
            
            $success = $this->pacienteRepository->assignCuidador($pacienteId, $cuidadorId, $userId);
            
            if (!$success) {
                throw new Exception('Error al asignar cuidador');
            }
            
            $this->pacienteRepository->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pacienteRepository->rollback();
            throw $e;
        }
    }

    /**
     * Remueve un cuidador de un paciente
     */
    public function unassignCuidador($pacienteId, $cuidadorId)
    {
        if (!$this->pacienteRepository->exists($pacienteId)) {
            throw new Exception('El paciente no existe');
        }

        try {
            $this->pacienteRepository->beginTransaction();
            
            $success = $this->pacienteRepository->unassignCuidador($pacienteId, $cuidadorId);
            
            if (!$success) {
                throw new Exception('Error al remover cuidador');
            }
            
            $this->pacienteRepository->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pacienteRepository->rollback();
            throw $e;
        }
    }

    /**
     * Verifica si un paciente tiene cuidadores asignados
     */
    public function hasCuidadores($pacienteId)
    {
        return $this->pacienteRepository->hasCuidadores($pacienteId);
    }

    /**
     * Asigna un profesional a un paciente
     */
    public function assignProfesional($pacienteId, $profesionalId)
    {
        if (!$this->pacienteRepository->exists($pacienteId)) {
            throw new Exception('El paciente no existe');
        }

        try {
            $this->pacienteRepository->beginTransaction();
            
            $success = $this->pacienteRepository->assignProfesional($pacienteId, $profesionalId);
            
            if (!$success) {
                throw new Exception('Error al asignar el profesional al paciente');
            }
            
            $this->pacienteRepository->commit();
            
            return true;
            
        } catch (Exception $e) {
            $this->pacienteRepository->rollback();
            throw $e;
        }
    }

    /**
     * Remueve la asignación de un profesional a un paciente
     */
    public function unassignProfesional($pacienteId, $profesionalId)
    {
        if (!$this->pacienteRepository->exists($pacienteId)) {
            throw new Exception('El paciente no existe');
        }

        try {
            $this->pacienteRepository->beginTransaction();
            
            $success = $this->pacienteRepository->unassignProfesional($pacienteId, $profesionalId);
            
            if (!$success) {
                throw new Exception('Error al remover la asignación del profesional');
            }
            
            $this->pacienteRepository->commit();
            
            return true;
            
        } catch (Exception $e) {
            $this->pacienteRepository->rollback();
            throw $e;
        }
    }

    /**
     * Obtiene los profesionales asignados a un paciente
     */
    public function getProfesionales($pacienteId)
    {
        if (!$this->pacienteRepository->exists($pacienteId)) {
            throw new Exception('El paciente no existe');
        }

        return $this->pacienteRepository->getProfesionales($pacienteId);
    }

    /**
     * Obtiene pacientes asignados a un profesional
     */
    public function getByProfesional($profesionalId)
    {
        $dataList = $this->pacienteRepository->findByProfesional($profesionalId);
        
        return array_map(function($data) {
            return PacienteDto::fromArray($data);
        }, $dataList);
    }

    /**
     * Obtiene pacientes asignados a un cuidador
     */
    public function getByCuidador($cuidadorId)
    {
        $dataList = $this->pacienteRepository->findByCuidador($cuidadorId);
        
        return array_map(function($data) {
            return PacienteDto::fromArray($data);
        }, $dataList);
    }

    /**
     * Obtiene un paciente con información del cuidador
     */
    public function getByIdWithCuidador($id)
    {
        $data = $this->pacienteRepository->findWithCuidador($id);
        
        if (!$data) {
            return null;
        }
        
        return PacienteDto::fromArray($data);
    }

    /**
     * Obtiene el conteo total de pacientes
     */
    public function getTotalCount()
    {
        return $this->pacienteRepository->count();
    }

    /**
     * Valida formato RUT chileno
     */
    private function validateRut($rut)
    {
        // Limpiar RUT
        $cleanRut = preg_replace('/[^0-9kK]/', '', $rut);
        
        if (strlen($cleanRut) < 2) {
            return false;
        }
        
        // Separar número y dígito verificador
        $numero = substr($cleanRut, 0, -1);
        $dv = strtoupper(substr($cleanRut, -1));
        
        // Validar que el número sea numérico
        if (!is_numeric($numero)) {
            return false;
        }
        
        // Calcular dígito verificador
        $suma = 0;
        $multiplicador = 2;
        
        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += $numero[$i] * $multiplicador;
            $multiplicador++;
            if ($multiplicador > 7) {
                $multiplicador = 2;
            }
        }
        
        $resto = $suma % 11;
        $dvCalculado = 11 - $resto;
        
        if ($dvCalculado == 11) {
            $dvCalculado = '0';
        } elseif ($dvCalculado == 10) {
            $dvCalculado = 'K';
        } else {
            $dvCalculado = (string) $dvCalculado;
        }
        
        return $dv === $dvCalculado;
    }

    /**
     * Valida formato de teléfono
     */
    private function validateTelefono($telefono)
    {
        // Formato básico: debe contener solo números, espacios, guiones y paréntesis
        // Mínimo 8 dígitos para teléfonos chilenos
        $cleanTelefono = preg_replace('/[^0-9]/', '', $telefono);
        
        return strlen($cleanTelefono) >= 8 && strlen($cleanTelefono) <= 15;
    }

    /**
     * Valida que un cuidador existe y tiene el rol correcto
     */
    private function validateCuidadorExists($cuidadorId)
    {
        if (empty($cuidadorId)) {
            return false;
        }

        $conexion = $this->pacienteRepository->getConexion();
        $query = "SELECT COUNT(*) as count FROM users WHERE id = ? AND rol = 'Cuidador'";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param('i', $cuidadorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        return $data['count'] > 0;
    }
}

?>