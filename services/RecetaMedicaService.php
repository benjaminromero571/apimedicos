<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/RecetaMedicaRepository.php';
require_once __DIR__ . '/../dto/RecetaMedicaDto.php';
require_once __DIR__ . '/../dto/CreateRecetaMedicaDto.php';
require_once __DIR__ . '/../dto/RecetaMedicaSearchDto.php';
require_once __DIR__ . '/../dto/RecetaMedicaDetailDto.php';

/**
 * RecetaMedicaService - Servicio para gestión de recetas médicas
 * 
 * Capa de lógica de negocio que maneja:
 * - Validaciones de negocio y permisos
 * - Verificación de que solo médicos puedan crear/editar recetas
 * - Verificación de que solo el médico propietario pueda editar su receta
 * - Transformación de datos entre DTOs y Entidades
 * - Orquestación de operaciones complejas
 * 
 * Arquitectura: No contiene lógica de acceso a datos (usa Repository)
 * ni lógica de presentación (retorna arrays estructurados).
 */
class RecetaMedicaService
{
    private RecetaMedicaRepository $repository;

    public function __construct()
    {
        $this->repository = new RecetaMedicaRepository();
    }

    /**
     * Obtiene todas las recetas con paginación
     * 
     * @param int|null $limit
     * @param int $offset
     * @return array Response estructurado con success, message, data
     */
    public function getAllRecetas(?int $limit = null, int $offset = 0): array
    {
        try {
            $entities = $this->repository->getAll($limit, $offset);
            
            $recetas = [];
            foreach ($entities as $entity) {
                $recetas[] = RecetaMedicaDto::fromEntity($entity);
            }

            $total = $this->repository->count();

            return [
                'success' => true,
                'message' => 'Recetas obtenidas correctamente',
                'data' => array_map(fn($dto) => $dto->toArray(), $recetas),
                'total' => $total,
                'showing' => count($recetas)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener recetas: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene una receta por ID con detalles completos
     * 
     * @param int $id
     * @return array Response estructurado
     */
    public function getRecetaById(int $id): array
    {
        try {
            if ($id <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de receta inválido',
                    'data' => null
                ];
            }

            $entity = $this->repository->getById($id);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Receta no encontrada',
                    'data' => null
                ];
            }

            $detailDto = RecetaMedicaDetailDto::fromEntity($entity);

            return [
                'success' => true,
                'message' => 'Receta obtenida correctamente',
                'data' => $detailDto->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener la receta: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Obtiene recetas por médico
     * 
     * @param int $idMedico
     * @param int|null $limit
     * @param int $offset
     * @return array Response estructurado
     */
    public function getRecetasByMedico(int $idMedico, ?int $limit = null, int $offset = 0): array
    {
        try {
            if ($idMedico <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de médico inválido',
                    'data' => []
                ];
            }

            // Verificar que el médico existe
            if (!$this->repository->medicoExists($idMedico)) {
                return [
                    'success' => false,
                    'message' => 'El médico no existe o no tiene rol de Medico',
                    'data' => []
                ];
            }

            $entities = $this->repository->getByMedico($idMedico, $limit, $offset);
            
            $recetas = [];
            foreach ($entities as $entity) {
                $recetas[] = RecetaMedicaDto::fromEntity($entity);
            }

            $total = $this->repository->count(['id_medico' => $idMedico]);

            return [
                'success' => true,
                'message' => 'Recetas del médico obtenidas correctamente',
                'data' => array_map(fn($dto) => $dto->toArray(), $recetas),
                'total' => $total,
                'showing' => count($recetas)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener recetas: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Búsqueda avanzada de recetas
     * 
     * @param RecetaMedicaSearchDto $searchDto
     * @return array Response estructurado
     */
    public function searchRecetas(RecetaMedicaSearchDto $searchDto): array
    {
        try {
            $filters = [];
            
            if ($searchDto->getIdMedico() !== null) {
                $filters['id_medico'] = $searchDto->getIdMedico();
            }
            if ($searchDto->getIdHistorial() !== null) {
                $filters['id_historial'] = $searchDto->getIdHistorial();
            }
            if ($searchDto->getFechaDesde() !== null) {
                $filters['fecha_desde'] = $searchDto->getFechaDesde();
            }
            if ($searchDto->getFechaHasta() !== null) {
                $filters['fecha_hasta'] = $searchDto->getFechaHasta();
            }
            if ($searchDto->getDetalleBusqueda() !== null) {
                $filters['detalle'] = $searchDto->getDetalleBusqueda();
            }

            $entities = $this->repository->search(
                $filters,
                $searchDto->getLimit(),
                $searchDto->getOffset()
            );
            
            $recetas = [];
            foreach ($entities as $entity) {
                $recetas[] = RecetaMedicaDto::fromEntity($entity);
            }

            $total = $this->repository->count($filters);

            return [
                'success' => true,
                'message' => 'Búsqueda completada correctamente',
                'data' => array_map(fn($dto) => $dto->toArray(), $recetas),
                'total' => $total,
                'showing' => count($recetas)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar recetas: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Crea una nueva receta médica
     * 
     * IMPORTANTE: Solo usuarios con rol 'Medico' pueden crear recetas
     * 
     * @param CreateRecetaMedicaDto $createDto
     * @param int $userId ID del usuario autenticado
     * @param string $userRole Rol del usuario autenticado
     * @return array Response estructurado
     */
    public function createReceta(CreateRecetaMedicaDto $createDto, int $userId, string $userRole): array
    {
        try {
            // VALIDACIÓN: Solo médicos pueden crear recetas
            if ($userRole !== 'Medico' && $userRole !== 'Administrador') {
                return [
                    'success' => false,
                    'message' => 'Acceso denegado: Solo los médicos pueden crear recetas',
                    'data' => null
                ];
            }

            // VALIDACIÓN: Para médicos, verificar que creen recetas a su nombre
            if ($userRole === 'Medico' && $createDto->getIdMedico() !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Acceso denegado: Solo puede crear recetas a su propio nombre',
                    'data' => null
                ];
            }

            // Verificar que el médico existe
            if (!$this->repository->medicoExists($createDto->getIdMedico())) {
                return [
                    'success' => false,
                    'message' => 'El médico especificado no existe o no tiene rol de Medico',
                    'data' => null
                ];
            }

            // Verificar que el historial existe
            if (!$this->repository->historialExists($createDto->getIdHistorial())) {
                return [
                    'success' => false,
                    'message' => 'El historial especificado no existe',
                    'data' => null
                ];
            }

            // Crear la receta
            $id = $this->repository->create($createDto->toArray());

            // Obtener la receta recién creada con todos sus detalles
            $entity = $this->repository->getById($id);
            $detailDto = RecetaMedicaDetailDto::fromEntity($entity);

            return [
                'success' => true,
                'message' => 'Receta creada exitosamente',
                'data' => $detailDto->toArray()
            ];

        } catch (InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => 'Error de validación: ' . $e->getMessage(),
                'data' => null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la receta: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Actualiza una receta existente
     * 
     * IMPORTANTE: Solo el médico que creó la receta puede editarla
     * (Los administradores también pueden editar cualquier receta)
     * 
     * @param int $id
     * @param array $data
     * @param int $userId ID del usuario autenticado
     * @param string $userRole Rol del usuario autenticado
     * @return array Response estructurado
     */
    public function updateReceta(int $id, array $data, int $userId, string $userRole): array
    {
        try {
            if ($id <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de receta inválido',
                    'data' => null
                ];
            }

            // Verificar que la receta existe
            if (!$this->repository->exists($id)) {
                return [
                    'success' => false,
                    'message' => 'Receta no encontrada',
                    'data' => null
                ];
            }

            // Obtener el ID del médico propietario de la receta
            $idMedicoPropietario = $this->repository->getIdMedicoPropietario($id);

            // VALIDACIÓN: Solo el médico propietario o un administrador pueden editar
            if ($userRole === 'Medico' && $idMedicoPropietario !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Acceso denegado: Solo puede editar sus propias recetas',
                    'data' => null
                ];
            }

            if ($userRole !== 'Medico' && $userRole !== 'Administrador') {
                return [
                    'success' => false,
                    'message' => 'Acceso denegado: Solo los médicos pueden editar recetas',
                    'data' => null
                ];
            }

            // Validar campos permitidos para actualización
            $allowedFields = ['detalle', 'fecha', 'id_historial'];
            $updateData = [];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (empty($updateData)) {
                return [
                    'success' => false,
                    'message' => 'No hay datos válidos para actualizar',
                    'data' => null
                ];
            }

            // Agregar auditoría
            $updateData['updated_by'] = $userId;

            // Validar detalle si se proporciona
            if (isset($updateData['detalle'])) {
                if (strlen($updateData['detalle']) < 10) {
                    return [
                        'success' => false,
                        'message' => 'El detalle debe tener al menos 10 caracteres',
                        'data' => null
                    ];
                }
                if (strlen($updateData['detalle']) > 255) {
                    return [
                        'success' => false,
                        'message' => 'El detalle no puede exceder 255 caracteres',
                        'data' => null
                    ];
                }
            }

            // Validar fecha si se proporciona
            if (isset($updateData['fecha'])) {
                $fecha = DateTime::createFromFormat('Y-m-d', $updateData['fecha']);
                if (!$fecha || $fecha->format('Y-m-d') !== $updateData['fecha']) {
                    return [
                        'success' => false,
                        'message' => 'El formato de fecha debe ser YYYY-MM-DD',
                        'data' => null
                    ];
                }
            }

            if (isset($updateData['id_historial'])) {
                $idHistorial = filter_var($updateData['id_historial'], FILTER_VALIDATE_INT);
                if ($idHistorial === false || $idHistorial <= 0) {
                    return [
                        'success' => false,
                        'message' => 'El ID de historial debe ser un entero positivo',
                        'data' => null
                    ];
                }

                if (!$this->repository->historialExists($idHistorial)) {
                    return [
                        'success' => false,
                        'message' => 'El historial especificado no existe',
                        'data' => null
                    ];
                }

                $updateData['id_historial'] = $idHistorial;
            }

            // Actualizar la receta
            $this->repository->update($id, $updateData);

            // Obtener la receta actualizada
            $entity = $this->repository->getById($id);
            $detailDto = RecetaMedicaDetailDto::fromEntity($entity);

            return [
                'success' => true,
                'message' => 'Receta actualizada exitosamente',
                'data' => $detailDto->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la receta: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Elimina una receta
     * 
     * IMPORTANTE: Solo administradores pueden eliminar recetas
     * 
     * @param int $id
     * @param string $userRole Rol del usuario autenticado
     * @return array Response estructurado
     */
    public function deleteReceta(int $id, string $userRole): array
    {
        try {
            if ($id <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de receta inválido'
                ];
            }

            // VALIDACIÓN: Solo administradores pueden eliminar
            if ($userRole !== 'Administrador') {
                return [
                    'success' => false,
                    'message' => 'Acceso denegado: Solo los administradores pueden eliminar recetas'
                ];
            }

            // Verificar que la receta existe
            if (!$this->repository->exists($id)) {
                return [
                    'success' => false,
                    'message' => 'Receta no encontrada'
                ];
            }

            // Eliminar la receta
            $this->repository->delete($id);

            return [
                'success' => true,
                'message' => 'Receta eliminada exitosamente'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la receta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene estadísticas de recetas por médico
     * 
     * @param int $idMedico
     * @return array Response estructurado
     */
    public function getEstadisticasByMedico(int $idMedico): array
    {
        try {
            if ($idMedico <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de médico inválido',
                    'data' => null
                ];
            }

            if (!$this->repository->medicoExists($idMedico)) {
                return [
                    'success' => false,
                    'message' => 'El médico no existe',
                    'data' => null
                ];
            }

            $total = $this->repository->count(['id_medico' => $idMedico]);
            
            // Obtener recetas del último mes
            $fechaDesde = date('Y-m-d', strtotime('-30 days'));
            $totalUltimoMes = $this->repository->count([
                'id_medico' => $idMedico,
                'fecha_desde' => $fechaDesde
            ]);

            return [
                'success' => true,
                'message' => 'Estadísticas obtenidas correctamente',
                'data' => [
                    'id_medico' => $idMedico,
                    'total_recetas' => $total,
                    'recetas_ultimo_mes' => $totalUltimoMes
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
}
