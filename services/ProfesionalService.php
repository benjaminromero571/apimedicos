<?php

require_once __DIR__ . '/../repositories/ProfesionalRepository.php';
require_once __DIR__ . '/../dto/ProfesionalDto.php';
require_once __DIR__ . '/../dto/CreateProfesionalDto.php';
require_once __DIR__ . '/../dto/ProfesionalSearchDto.php';
require_once __DIR__ . '/../dto/ProfesionalDetailDto.php';
require_once __DIR__ . '/../core/Pagination.php';

/**
 * Servicio para gestión de profesionales médicos
 * Contiene toda la lógica de negocio relacionada con doctores/profesionales
 */
class ProfesionalService
{
    private $profesionalRepository;

    public function __construct()
    {
        $this->profesionalRepository = new ProfesionalRepository();
    }

    /**
     * Obtiene todos los profesionales con ordenamiento
     */
    public function getAllProfesionales($orderBy = 'nombre ASC', $limit = null, $offset = 0)
    {
        try {
            $entities = $this->profesionalRepository->getAll($orderBy, $limit, $offset);
            
            if (!$entities) {
                return [
                    'success' => false,
                    'message' => 'No se pudieron obtener los profesionales',
                    'data' => []
                ];
            }

            $profesionales = [];
            foreach ($entities as $entity) {
                $profesionales[] = ProfesionalDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Profesionales obtenidos correctamente',
                'data' => $profesionales,
                'total' => count($profesionales)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener profesionales: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene todos los profesionales con paginación
     */
    public function getAllProfesionalesPaginated(?int $limit = null, int $offset = 0, $orderBy = 'nombre ASC')
    {
        try {
            $total = $this->profesionalRepository->countAll();
            $entities = $this->profesionalRepository->getAll($orderBy, $limit, $offset);
            
            if (!$entities) {
                return [
                    'success' => false,
                    'message' => 'No se pudieron obtener los profesionales',
                    'data' => []
                ];
            }

            $profesionales = [];
            foreach ($entities as $entity) {
                $profesionales[] = ProfesionalDto::fromEntity($entity);
            }

            $pagination = \Core\Pagination::build($limit, $offset, $total);

            return [
                'success' => true,
                'message' => 'Profesionales obtenidos correctamente',
                'data' => $profesionales,
                'pagination' => $pagination
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener profesionales: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene un profesional por ID con detalles completos
     */
    public function getProfesionalById($id)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                return [
                    'success' => false,
                    'message' => 'ID de profesional inválido',
                    'data' => null
                ];
            }

            $entity = $this->profesionalRepository->getById($id);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Profesional no encontrado',
                    'data' => null
                ];
            }

            // Usar DTO detallado para información completa
            $profesionalDetail = new ProfesionalDetailDto($entity->toArray());

            return [
                'success' => true,
                'message' => 'Profesional obtenido correctamente',
                'data' => $profesionalDetail->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener el profesional: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Busca un profesional por documento/cédula
     */
    public function getProfesionalByDocumento($documento)
    {
        try {
            if (empty($documento)) {
                return [
                    'success' => false,
                    'message' => 'Documento profesional requerido',
                    'data' => null
                ];
            }

            $entity = $this->profesionalRepository->getByDocumento($documento);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Profesional no encontrado con ese documento',
                    'data' => null
                ];
            }

            $profesionalDto = ProfesionalDto::fromEntity($entity);

            return [
                'success' => true,
                'message' => 'Profesional encontrado',
                'data' => $profesionalDto->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar profesional: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Método de compatibilidad para buscar por cédula
     */
    public function getProfesionalByCedula($cedula)
    {
        return $this->getProfesionalByDocumento($cedula);
    }

    /**
     * Obtiene profesionales por especialidad
     */
    public function getProfesionalesByEspecialidad($especialidad, $limit = null, $offset = 0)
    {
        try {
            if (empty($especialidad)) {
                return [
                    'success' => false,
                    'message' => 'Especialidad requerida',
                    'data' => []
                ];
            }

            $entities = $this->profesionalRepository->getByEspecialidad($especialidad, $limit, $offset);
            
            $profesionales = [];
            foreach ($entities as $entity) {
                $profesionales[] = ProfesionalDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => "Profesionales de {$especialidad} obtenidos correctamente",
                'data' => $profesionales,
                'especialidad' => $especialidad,
                'total' => count($profesionales)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener profesionales por especialidad: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene todas las especialidades disponibles
     */
    public function getEspecialidades()
    {
        try {
            $especialidades = $this->profesionalRepository->getEspecialidades();

            return [
                'success' => true,
                'message' => 'Especialidades obtenidas correctamente',
                'data' => $especialidades,
                'total' => count($especialidades)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener especialidades: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Crea un nuevo profesional
     */
    public function createProfesional($data)
    {
        try {
            // Crear DTO de creación y validar
            $createDto = new CreateProfesionalDto($data);
            $createDto->sanitize();
            
            $validationErrors = $createDto->validate();
            if (!empty($validationErrors)) {
                return [
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validationErrors,
                    'data' => null
                ];
            }

            // Verificar que el documento no exista
            if ($this->profesionalRepository->existsByDocumento($createDto->documento)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un profesional con ese documento',
                    'data' => null
                ];
            }

            // Crear el profesional
            $newId = $this->profesionalRepository->create($createDto->toArray());
            
            if (!$newId) {
                return [
                    'success' => false,
                    'message' => 'Error al crear el profesional',
                    'data' => null
                ];
            }

            // Obtener el profesional creado con detalles
            $createdEntity = $this->profesionalRepository->getById($newId);
            $profesionalDetail = new ProfesionalDetailDto($createdEntity->toArray());

            return [
                'success' => true,
                'message' => 'Profesional creado correctamente',
                'data' => $profesionalDetail->toArray(),
                'id' => $newId,
                'warnings' => $this->getCreationWarnings($createDto)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear profesional: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Actualiza un profesional existente
     */
    public function updateProfesional($id, $data)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                return [
                    'success' => false,
                    'message' => 'ID de profesional inválido',
                    'data' => null
                ];
            }

            // Verificar que existe
            $existing = $this->profesionalRepository->getById($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Profesional no encontrado',
                    'data' => null
                ];
            }

            // Crear DTO y validar
            $updateDto = new CreateProfesionalDto($data);
            $updateDto->sanitize();
            
            $validationErrors = $updateDto->validate();
            if (!empty($validationErrors)) {
                return [
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validationErrors,
                    'data' => null
                ];
            }

            // Verificar documento único (excluyendo el actual)
            if ($this->profesionalRepository->existsByDocumento($updateDto->documento, $id)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe otro profesional con ese documento',
                    'data' => null
                ];
            }

            // Actualizar
            $success = $this->profesionalRepository->update($id, $updateDto->toArray());
            
            if (!$success) {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el profesional',
                    'data' => null
                ];
            }

            // Obtener el profesional actualizado
            $updatedEntity = $this->profesionalRepository->getById($id);
            $profesionalDetail = new ProfesionalDetailDto($updatedEntity->toArray());

            return [
                'success' => true,
                'message' => 'Profesional actualizado correctamente',
                'data' => $profesionalDetail->toArray(),
                'warnings' => $this->getCreationWarnings($updateDto)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar profesional: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Elimina un profesional
     */
    public function deleteProfesional($id)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                return [
                    'success' => false,
                    'message' => 'ID de profesional inválido'
                ];
            }

            // Verificar que existe
            $existing = $this->profesionalRepository->getById($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ];
            }

            // TODO: Verificar si tiene asignaciones activas antes de eliminar
            // Esto se podría implementar cuando se integre con otros servicios

            $success = $this->profesionalRepository->delete($id);
            
            if (!$success) {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el profesional'
                ];
            }

            return [
                'success' => true,
                'message' => 'Profesional eliminado correctamente'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar profesional: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Busca profesionales según criterios
     */
    public function searchProfesionales($searchData)
    {
        try {
            $searchDto = new ProfesionalSearchDto($searchData);
            
            // Validar criterios de búsqueda
            if (!$searchDto->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Criterios de búsqueda inválidos',
                    'errors' => $searchDto->validate(),
                    'data' => []
                ];
            }
            
            $criteria = $searchDto->getCriteriaForRepository();
            
            // Realizar búsqueda
            $entities = $this->profesionalRepository->search(
                $criteria, 
                $searchDto->limit, 
                $searchDto->offset
            );
            
            // Obtener total para paginación
            $total = $this->profesionalRepository->count($criteria);
            
            $profesionales = [];
            foreach ($entities as $entity) {
                $profesionales[] = ProfesionalDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Búsqueda realizada correctamente',
                'data' => $profesionales,
                'pagination' => [
                    'total' => $total,
                    'limit' => $searchDto->limit,
                    'offset' => $searchDto->offset,
                    'page' => $searchDto->getPagination()['page'],
                    'total_pages' => ceil($total / $searchDto->limit)
                ],
                'search_criteria' => $searchDto->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene estadísticas de profesionales por especialidad
     */
    public function getEstadisticasByEspecialidad()
    {
        try {
            $estadisticas = $this->profesionalRepository->getEstadisticasByEspecialidad();
            
            // Procesar estadísticas adicionales
            $totalProfesionales = array_sum(array_column($estadisticas, 'total_profesionales'));
            
            $estadisticasProcessed = array_map(function($stat) use ($totalProfesionales) {
                $stat['porcentaje'] = $totalProfesionales > 0 ? 
                                    round(($stat['total_profesionales'] / $totalProfesionales) * 100, 2) : 0;
                return $stat;
            }, $estadisticas);

            return [
                'success' => true,
                'message' => 'Estadísticas obtenidas correctamente',
                'data' => [
                    'estadisticas_por_especialidad' => $estadisticasProcessed,
                    'total_profesionales' => $totalProfesionales,
                    'total_especialidades' => count($estadisticas)
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Busca profesionales por nombre
     */
    public function searchByName($name, $limit = 20, $offset = 0)
    {
        try {
            if (empty(trim($name))) {
                return [
                    'success' => false,
                    'message' => 'Nombre para búsqueda requerido',
                    'data' => []
                ];
            }

            if (strlen(trim($name)) < 2) {
                return [
                    'success' => false,
                    'message' => 'El nombre debe tener al menos 2 caracteres',
                    'data' => []
                ];
            }

            $entities = $this->profesionalRepository->searchByName($name, $limit, $offset);
            
            $profesionales = [];
            foreach ($entities as $entity) {
                $profesionales[] = ProfesionalDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Búsqueda por nombre realizada correctamente',
                'data' => $profesionales,
                'search_term' => $name,
                'total' => count($profesionales)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en la búsqueda por nombre: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene advertencias durante la creación/actualización
     */
    private function getCreationWarnings($dto)
    {
        $warnings = [];
        
        // Advertencia si la especialidad no es estándar
        if (!$dto->isEspecialidadReconocida()) {
            $warnings[] = [
                'tipo' => 'especialidad',
                'mensaje' => 'La especialidad registrada no está en la lista estándar',
                'sugerencias' => $dto->getSugerenciasEspecialidad()
            ];
        }
        
        // Advertencia si no tiene teléfono
        if (empty($dto->telefono)) {
            $warnings[] = [
                'tipo' => 'contacto',
                'mensaje' => 'Profesional registrado sin teléfono de contacto'
            ];
        }

        // Advertencia si no tiene usuario asignado
        if (empty($dto->id_user)) {
            $warnings[] = [
                'tipo' => 'usuario',
                'mensaje' => 'Profesional registrado sin usuario del sistema asignado'
            ];
        }
        
        return $warnings;
    }

    /**
     * Obtiene profesionales por usuario
     */
    public function getProfesionalesByUserId($userId, $limit = null, $offset = 0)
    {
        try {
            if (empty($userId) || !is_numeric($userId)) {
                return [
                    'success' => false,
                    'message' => 'ID de usuario inválido',
                    'data' => []
                ];
            }

            // Verificar que el usuario existe
            require_once __DIR__ . '/../repositories/UserRepository.php';
            $userRepo = new UserRepository();
            if (!$userRepo->exists($userId)) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'data' => []
                ];
            }

            $entities = $this->profesionalRepository->getByUserId($userId, $limit, $offset);
            
            $profesionales = [];
            foreach ($entities as $entity) {
                $profesionales[] = ProfesionalDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Profesionales del usuario obtenidos correctamente',
                'data' => $profesionales,
                'user_id' => $userId,
                'total' => count($profesionales)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener profesionales por usuario: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene profesionales sin usuario asignado
     */
    public function getProfesionalesSinUsuario($limit = null, $offset = 0)
    {
        try {
            $entities = $this->profesionalRepository->getWithoutUser($limit, $offset);
            
            $profesionales = [];
            foreach ($entities as $entity) {
                $profesionales[] = ProfesionalDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Profesionales sin usuario obtenidos correctamente',
                'data' => $profesionales,
                'total' => count($profesionales)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener profesionales sin usuario: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Asigna un usuario a un profesional
     */
    public function asignarUsuario($profesionalId, $userId)
    {
        try {
            if (empty($profesionalId) || !is_numeric($profesionalId)) {
                return [
                    'success' => false,
                    'message' => 'ID de profesional inválido'
                ];
            }

            if (empty($userId) || !is_numeric($userId)) {
                return [
                    'success' => false,
                    'message' => 'ID de usuario inválido'
                ];
            }

            // Verificar que el profesional existe
            $profesional = $this->profesionalRepository->getById($profesionalId);
            if (!$profesional) {
                return [
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ];
            }

            // Verificar que el usuario existe
            require_once __DIR__ . '/../repositories/UserRepository.php';
            $userRepo = new UserRepository();
            if (!$userRepo->exists($userId)) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ];
            }

            // Actualizar el profesional
            $data = $profesional->getFillableData();
            $data['id_user'] = $userId;

            $success = $this->profesionalRepository->update($profesionalId, $data);
            
            if (!$success) {
                return [
                    'success' => false,
                    'message' => 'Error al asignar usuario al profesional'
                ];
            }

            return [
                'success' => true,
                'message' => 'Usuario asignado correctamente al profesional'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al asignar usuario: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Valida que un profesional puede ser eliminado
     */
    public function canDeleteProfesional($id)
    {
        // Esta función se puede expandir para verificar:
        // - Si tiene asignaciones activas
        // - Si tiene citas programadas
        // - Otras restricciones de negocio
        
        return [
            'can_delete' => true,
            'restrictions' => []
        ];
    }
}

?>