<?php

require_once __DIR__ . '/../contracts/ServiceInterface.php';
require_once __DIR__ . '/../repositories/AsignacionRepository.php';
require_once __DIR__ . '/../dto/AsignacionDto.php';
require_once __DIR__ . '/../dto/CreateAsignacionDto.php';
require_once __DIR__ . '/../dto/AsignacionStatsDto.php';
require_once __DIR__ . '/../dto/AsignacionDetailDto.php';
require_once __DIR__ . '/../core/Pagination.php';

/**
 * Service para manejar la lógica de negocio de Asignaciones
 * Contiene todas las reglas de dominio y validaciones
 */
class AsignacionService implements ServiceInterface
{
    private $asignacionRepository;
    
    public function __construct(AsignacionRepository $asignacionRepository = null)
    {
        $this->asignacionRepository = $asignacionRepository ?: new AsignacionRepository();
    }

    /**
     * Obtiene una asignación por ID
     */
    public function getById($id)
    {
        $data = $this->asignacionRepository->findById($id);
        
        if (!$data) {
            return null;
        }
        
        return AsignacionDto::fromArray($data);
    }

    /**
     * Obtiene todas las asignaciones
     */
    public function getAll()
    {
        $dataList = $this->asignacionRepository->findAll('fecha_asignacion DESC');
        
        return array_map(function($data) {
            return AsignacionDto::fromArray($data);
        }, $dataList);
    }

    /**
     * Crea una nueva asignación
     */
    public function create(array $data)
    {
        $createDto = CreateAsignacionDto::fromArray($data);
        
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
            $this->asignacionRepository->beginTransaction();
            
            $newId = $this->asignacionRepository->create($createDto->toArray());
            
            $this->asignacionRepository->commit();
            
            return $this->getById($newId);
            
        } catch (Exception $e) {
            $this->asignacionRepository->rollback();
            throw $e;
        }
    }

    /**
     * Actualiza una asignación existente
     */
    public function update($id, array $data)
    {
        if (!$this->asignacionRepository->exists($id)) {
            throw new Exception('La asignación no existe');
        }
        
        // Validar datos incluyendo el ID para validaciones de actualización
        $errors = $this->validate($data, $id);
        if (!empty($errors)) {
            throw new Exception('Errores de validación: ' . implode(', ', $errors));
        }
        
        try {
            $this->asignacionRepository->beginTransaction();
            
            $success = $this->asignacionRepository->update($id, $data);
            
            if (!$success) {
                throw new Exception('Error al actualizar la asignación');
            }
            
            $this->asignacionRepository->commit();
            
            return true;
            
        } catch (Exception $e) {
            $this->asignacionRepository->rollback();
            throw $e;
        }
    }

    /**
     * Elimina una asignación
     */
    public function delete($id)
    {
        if (!$this->asignacionRepository->exists($id)) {
            throw new Exception('La asignación no existe');
        }
        
        return $this->asignacionRepository->delete($id);
    }

    /**
     * Valida los datos según las reglas de negocio
     */
    public function validate(array $data, $id = null)
    {
        $errors = [];

        // Validar user_id
        if (empty($data['user_id'])) {
            $errors[] = 'ID del usuario es requerido';
        } else {
            if (!$this->userExists($data['user_id'])) {
                $errors[] = 'El usuario especificado no existe';
            }
        }

        // Validar paciente_id
        if (empty($data['paciente_id'])) {
            $errors[] = 'ID del paciente es requerido';
        } else {
            if (!$this->pacienteExists($data['paciente_id'])) {
                $errors[] = 'El paciente especificado no existe';
            }
        }

        // Validar unicidad de la asignación (solo para creación o si cambian los IDs)
        if (!empty($data['user_id']) && !empty($data['paciente_id'])) {
            $existingAssignment = $this->asignacionRepository->findByUserAndPaciente(
                $data['user_id'], 
                $data['paciente_id']
            );
            
            if ($existingAssignment && (!$id || $existingAssignment['id'] != $id)) {
                $errors[] = 'Ya existe una asignación entre este usuario y paciente';
            }
        }

        return $errors;
    }

    /**
     * Asigna un usuario a un paciente
     */
    public function assignUserToPaciente($userId, $pacienteId)
    {
        if ($this->asignacionRepository->existsAssignment($userId, $pacienteId)) {
            throw new Exception('El usuario ya está asignado a este paciente');
        }

        return $this->create([
            'user_id' => $userId,
            'paciente_id' => $pacienteId,
            'fecha_asignacion' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Desasigna un usuario de un paciente
     */
    public function unassignUserFromPaciente($userId, $pacienteId)
    {
        if (!$this->asignacionRepository->existsAssignment($userId, $pacienteId)) {
            throw new Exception('No existe una asignación entre este usuario y paciente');
        }

        return $this->asignacionRepository->deleteByUserAndPaciente($userId, $pacienteId);
    }

    /**
     * Obtiene asignaciones por usuario
     */
    public function getByUser($userId)
    {
        $dataList = $this->asignacionRepository->findByUser($userId);
        
        return array_map(function($data) {
            return AsignacionDto::fromArray($data);
        }, $dataList);
    }

    /**
     * Obtiene asignaciones por paciente
     */
    public function getByPaciente($pacienteId)
    {
        $dataList = $this->asignacionRepository->findByPaciente($pacienteId);
        
        return array_map(function($data) {
            return AsignacionDto::fromArray($data);
        }, $dataList);
    }

    /**
     * Obtiene asignaciones de un usuario con detalles del paciente
     */
    public function getByUserWithDetails($userId)
    {
        $dataList = $this->asignacionRepository->findByUserWithDetails($userId);
        
        return array_map(function($data) {
            return AsignacionDetailDto::fromArray($data);
        }, $dataList);
    }

    /**
     * Obtiene todas las asignaciones con información completa
     */
    public function getAllWithDetails()
    {
        $dataList = $this->asignacionRepository->findAllWithDetails();
        
        return array_map(function($data) {
            return AsignacionDetailDto::fromArray($data);
        }, $dataList);
    }

    /**
     * Obtiene todas las asignaciones con detalles y paginación
     */
    public function getAllPaginated(?int $limit = null, int $offset = 0)
    {
        try {
            $dataList = $this->asignacionRepository->findAllWithDetails();
            
            // Apply limit and offset manually since findAllWithDetails doesn't support it
            $total = count($dataList);
            if ($limit !== null) {
                $dataList = array_slice($dataList, $offset, $limit);
            }
            
            $asignaciones = array_map(function($data) {
                return AsignacionDetailDto::fromArray($data);
            }, $dataList);
            
            $pagination = \Core\Pagination::build($limit, $offset, $total);
            
            return [
                'success' => true,
                'message' => 'Asignaciones obtenidas correctamente',
                'data' => $asignaciones,
                'pagination' => $pagination
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener asignaciones: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene estadísticas de asignaciones
     */
    public function getEstadisticas()
    {
        $stats = $this->asignacionRepository->getStats();
        $stats['fecha_generacion'] = date('Y-m-d H:i:s');
        
        return AsignacionStatsDto::fromArray($stats);
    }

    /**
     * Obtiene asignaciones con paginación
     */
    public function getPaginated($page = 1, $limit = 10)
    {
        $dataList = $this->asignacionRepository->findWithPagination($page, $limit);
        $total = $this->asignacionRepository->getTotalCount();
        
        return [
            'data' => array_map(function($data) {
                return AsignacionDto::fromArray($data);
            }, $dataList),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_more' => ($page * $limit) < $total
            ]
        ];
    }

    /**
     * Verifica si existe una asignación específica
     */
    public function existsAssignment($userId, $pacienteId)
    {
        return $this->asignacionRepository->existsAssignment($userId, $pacienteId);
    }

    /**
     * Verifica si un usuario existe (método auxiliar)
     */
    private function userExists($userId)
    {
        // Usar UserService en lugar del modelo legacy
        require_once __DIR__ . '/UserService.php';
        $userService = new UserService();
        $result = $userService->getUserById($userId);
        return $result['success'];
    }

    /**
     * Verifica si un paciente existe (método auxiliar)
     */
    private function pacienteExists($pacienteId)
    {
        // Aquí deberías usar un PacienteRepository o similar
        require_once __DIR__ . '/../repositories/PacienteRepository.php';
        $pacienteRepository = new PacienteRepository();
        return $pacienteRepository->exists($pacienteId);
    }

    /**
     * Cuenta asignaciones por usuario
     */
    public function countByUser($userId)
    {
        return $this->asignacionRepository->countByUser($userId);
    }

    /**
     * Cuenta asignaciones por paciente
     */
    public function countByPaciente($pacienteId)
    {
        return $this->asignacionRepository->countByPaciente($pacienteId);
    }
}

?>