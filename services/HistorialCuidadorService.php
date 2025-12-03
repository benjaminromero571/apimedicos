<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/HistorialCuidadorRepository.php';
require_once __DIR__ . '/../core/Pagination.php';
require_once __DIR__ . '/../dto/HistorialCuidadorDto.php';
require_once __DIR__ . '/../dto/CreateHistorialCuidadorDto.php';
require_once __DIR__ . '/../dto/HistorialCuidadorSearchDto.php';
require_once __DIR__ . '/../dto/HistorialCuidadorDetailDto.php';

/**
 * HistorialCuidadorService - Servicio para gestión de historiales de cuidadores
 * 
 * Capa de lógica de negocio que maneja:
 * - Validaciones de negocio
 * - Transformación de datos entre DTOs y Entidades
 * - Orquestación de operaciones complejas
 * - Manejo de excepciones y mensajes de respuesta
 * 
 * Arquitectura: No contiene lógica de acceso a datos (usa Repository)
 * ni lógica de presentación (retorna arrays estructurados).
 */
class HistorialCuidadorService
{
    private HistorialCuidadorRepository $repository;

    public function __construct()
    {
        $this->repository = new HistorialCuidadorRepository();
    }

    /**
     * Obtiene todos los historiales con paginación
     * 
     * @param int|null $limit
     * @param int $offset
     * @return array Response estructurado con success, message, data
     */
    public function getAllHistoriales(?int $limit = null, int $offset = 0): array
    {
        try {
            $entities = $this->repository->getAll($limit, $offset);
            
            $historiales = [];
            foreach ($entities as $entity) {
                $historiales[] = HistorialCuidadorDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Historiales obtenidos correctamente',
                'data' => array_map(fn($dto) => $dto->toArray(), $historiales),
                'total' => count($historiales)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener historiales: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene un historial por ID con detalles completos
     * 
     * @param int $id
     * @return array Response estructurado
     */
    public function getHistorialById(int $id): array
    {
        try {
            if ($id <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de historial inválido',
                    'data' => null
                ];
            }

            $entity = $this->repository->getById($id);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Historial no encontrado',
                    'data' => null
                ];
            }

            $detailDto = HistorialCuidadorDetailDto::fromEntity($entity);

            return [
                'success' => true,
                'message' => 'Historial obtenido correctamente',
                'data' => $detailDto->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener el historial: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Obtiene historiales por paciente
     * 
     * @param int $idPaciente
     * @param int|null $limit
     * @param int $offset
     * @return array Response estructurado
     */
    public function getHistorialesByPaciente(int $idPaciente, ?int $limit = null, int $offset = 0): array
    {
        try {
            if ($idPaciente <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de paciente inválido',
                    'data' => []
                ];
            }

            // Verificar que el paciente existe
            if (!$this->repository->pacienteExists($idPaciente)) {
                return [
                    'success' => false,
                    'message' => 'El paciente no existe',
                    'data' => []
                ];
            }

            $entities = $this->repository->getByPaciente($idPaciente, $limit, $offset);
            
            $historiales = [];
            foreach ($entities as $entity) {
                $historiales[] = HistorialCuidadorDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Historiales del paciente obtenidos correctamente',
                'data' => array_map(fn($dto) => $dto->toArray(), $historiales),
                'total' => count($historiales)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener historiales del paciente: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene historiales por cuidador
     * 
     * @param int $idCuidador
     * @param int|null $limit
     * @param int $offset
     * @return array Response estructurado
     */
    public function getHistorialesByCuidador(int $idCuidador, ?int $limit = null, int $offset = 0): array
    {
        try {
            if ($idCuidador <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de cuidador inválido',
                    'data' => []
                ];
            }

            // Verificar que el cuidador existe
            if (!$this->repository->cuidadorExists($idCuidador)) {
                return [
                    'success' => false,
                    'message' => 'El cuidador no existe',
                    'data' => []
                ];
            }

            $entities = $this->repository->getByCuidador($idCuidador, $limit, $offset);
            
            $historiales = [];
            foreach ($entities as $entity) {
                $historiales[] = HistorialCuidadorDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Historiales del cuidador obtenidos correctamente',
                'data' => array_map(fn($dto) => $dto->toArray(), $historiales),
                'total' => count($historiales)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener historiales del cuidador: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Busca historiales con criterios complejos
     * 
     * @param array $searchParams Parámetros de búsqueda
     * @return array Response estructurado
     */
    public function searchHistoriales(array $searchParams): array
    {
        try {
            $searchDto = new HistorialCuidadorSearchDto($searchParams);
            $searchData = $searchDto->buildWhereConditions();
            
            // Agregar parámetros adicionales
            $searchData['limit'] = $searchDto->getLimit();
            $searchData['offset'] = $searchDto->getOffset();
            $searchData['order_by'] = $searchDto->getOrderBy();
            $searchData['order_direction'] = $searchDto->getOrderDirection();

            $entities = $this->repository->search($searchData);
            
            $historiales = [];
            foreach ($entities as $entity) {
                $historiales[] = HistorialCuidadorDto::fromEntity($entity);
            }

            // Contar total para paginación
            $totalCount = $this->repository->count([
                'conditions' => $searchData['conditions'],
                'params' => $searchData['params'],
                'types' => $searchData['types']
            ]);

            return [
                'success' => true,
                'message' => 'Búsqueda realizada correctamente',
                'data' => array_map(fn($dto) => $dto->toArray(), $historiales),
                'total' => $totalCount,
                'pagination' => [
                    'limit' => $searchDto->getLimit(),
                    'offset' => $searchDto->getOffset(),
                    'total' => $totalCount
                ]
            ];

        } catch (InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => 'Error de validación: ' . $e->getMessage(),
                'data' => []
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar historiales: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Crea un nuevo historial de cuidador
     * 
     * Validaciones:
     * - Campos requeridos presentes
     * - Paciente existe
     * - Cuidador existe
     * - Usuario tiene permisos (verificado en controller)
     * 
     * @param array $data Datos del historial
     * @return array Response estructurado
     */
    public function createHistorial(array $data): array
    {
        try {
            // Validar datos usando DTO
            $createDto = new CreateHistorialCuidadorDto($data);

            // Validaciones de negocio: verificar que existan paciente y cuidador
            if (!$this->repository->pacienteExists($createDto->getIdPaciente())) {
                return [
                    'success' => false,
                    'message' => 'El paciente especificado no existe',
                    'data' => null
                ];
            }

            if (!$this->repository->cuidadorExists($createDto->getIdCuidador())) {
                return [
                    'success' => false,
                    'message' => 'El cuidador especificado no existe',
                    'data' => null
                ];
            }

            // Crear el historial
            $insertId = $this->repository->create($createDto->toArray());

            if ($insertId <= 0) {
                return [
                    'success' => false,
                    'message' => 'No se pudo crear el historial',
                    'data' => null
                ];
            }

            // Obtener el historial creado con todos sus datos
            $entity = $this->repository->getById($insertId);
            $detailDto = HistorialCuidadorDetailDto::fromEntity($entity);

            return [
                'success' => true,
                'message' => 'Historial creado exitosamente',
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
                'message' => 'Error al crear el historial: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Actualiza un historial existente
     * 
     * Solo se pueden actualizar: detalle y fecha_historial
     * Los campos de auditoría se actualizan automáticamente
     * 
     * @param int $id ID del historial
     * @param array $data Datos a actualizar
     * @param int $userId ID del usuario que actualiza
     * @return array Response estructurado
     */
    public function updateHistorial(int $id, array $data, int $userId): array
    {
        try {
            if ($id <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de historial inválido',
                    'data' => null
                ];
            }

            // Verificar que el historial existe
            $existingEntity = $this->repository->getById($id);
            if (!$existingEntity) {
                return [
                    'success' => false,
                    'message' => 'Historial no encontrado',
                    'data' => null
                ];
            }

            // Preparar datos para actualización (solo campos permitidos)
            $updateData = [];
            
            if (isset($data['detalle'])) {
                $detalle = trim($data['detalle']);
                if (strlen($detalle) < 5) {
                    return [
                        'success' => false,
                        'message' => 'El detalle debe tener al menos 5 caracteres',
                        'data' => null
                    ];
                }
                if (strlen($detalle) > 255) {
                    return [
                        'success' => false,
                        'message' => 'El detalle no puede exceder 255 caracteres',
                        'data' => null
                    ];
                }
                $updateData['detalle'] = $detalle;
            }

            if (isset($data['fecha_historial'])) {
                $fecha = \DateTime::createFromFormat('Y-m-d', $data['fecha_historial']);
                if (!$fecha) {
                    $fecha = \DateTime::createFromFormat('Y-m-d H:i:s', $data['fecha_historial']);
                    if (!$fecha) {
                        return [
                            'success' => false,
                            'message' => 'Formato de fecha inválido. Use Y-m-d o Y-m-d H:i:s',
                            'data' => null
                        ];
                    }
                }
                $updateData['fecha_historial'] = $data['fecha_historial'];
            }

            if (isset($data['registro'])) {
                if (!is_array($data['registro'])) {
                    return [
                        'success' => false,
                        'message' => 'El registro debe ser un objeto JSON válido',
                        'data' => null
                    ];
                }
                $updateData['registro'] = json_encode($data['registro']);
            }

            if (empty($updateData)) {
                return [
                    'success' => false,
                    'message' => 'No hay datos para actualizar',
                    'data' => null
                ];
            }

            // Agregar campos de auditoría
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $updateData['updated_by'] = $userId;

            // Actualizar
            $updated = $this->repository->update($id, $updateData);

            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'No se pudo actualizar el historial',
                    'data' => null
                ];
            }

            // Obtener el historial actualizado
            $entity = $this->repository->getById($id);
            $detailDto = HistorialCuidadorDetailDto::fromEntity($entity);

            return [
                'success' => true,
                'message' => 'Historial actualizado exitosamente',
                'data' => $detailDto->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el historial: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Elimina un historial
     * 
     * @param int $id ID del historial
     * @return array Response estructurado
     */
    public function deleteHistorial(int $id): array
    {
        try {
            if ($id <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de historial inválido'
                ];
            }

            // Verificar que el historial existe
            $entity = $this->repository->getById($id);
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Historial no encontrado'
                ];
            }

            $deleted = $this->repository->delete($id);

            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'No se pudo eliminar el historial'
                ];
            }

            return [
                'success' => true,
                'message' => 'Historial eliminado exitosamente'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el historial: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene estadísticas de historiales por paciente
     * 
     * @param int $idPaciente
     * @return array Response estructurado
     */
    public function getEstadisticasPorPaciente(int $idPaciente): array
    {
        try {
            if ($idPaciente <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de paciente inválido',
                    'data' => null
                ];
            }

            $entities = $this->repository->getByPaciente($idPaciente);
            
            $estadisticas = [
                'total_registros' => count($entities),
                'ultimo_registro' => null,
                'total_cuidadores' => 0
            ];

            if (!empty($entities)) {
                // Último registro
                $ultimoDto = HistorialCuidadorDto::fromEntity($entities[0]);
                $estadisticas['ultimo_registro'] = $ultimoDto->toArray();

                // Contar cuidadores únicos
                $cuidadores = array_unique(array_map(fn($e) => $e->getIdCuidador(), $entities));
                $estadisticas['total_cuidadores'] = count($cuidadores);
            }

            return [
                'success' => true,
                'message' => 'Estadísticas obtenidas correctamente',
                'data' => $estadisticas
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
     * Obtiene los historiales de pacientes asignados a un cuidador específico
     * 
     * Retorna todos los historiales de los pacientes que tienen al menos un historial
     * creado por el cuidador especificado.
     * 
     * @param int $idCuidador ID del cuidador
     * @param int|null $limit
     * @param int $offset
     * @return array Response estructurado
     */
    public function getHistorialesPacientesAsignadosByCuidador(int $idCuidador, ?int $limit = null, int $offset = 0, ?int $idPaciente = null): array
    {
        try {
            if ($idCuidador <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de cuidador inválido',
                    'data' => []
                ];
            }

            // Verificar que el cuidador existe
            if (!$this->repository->cuidadorExists($idCuidador)) {
                return [
                    'success' => false,
                    'message' => 'El cuidador no existe',
                    'data' => []
                ];
            }

            $entities = $this->repository->getHistorialesPacientesAsignadosByCuidador($idCuidador, $limit, $offset, $idPaciente);
            
            $historiales = [];
            foreach ($entities as $entity) {
                $historiales[] = HistorialCuidadorDto::fromEntity($entity);
            }

            $totalCount = $this->repository->countHistorialesPacientesAsignadosByCuidador($idCuidador, $idPaciente);
            $pagination = \Core\Pagination::build($limit, $offset, $totalCount);

            return [
                'success' => true,
                'message' => 'Historiales de pacientes asignados obtenidos correctamente',
                'data' => array_map(fn($dto) => $dto->toArray(), $historiales),
                'pagination' => $pagination
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener historiales de pacientes asignados: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
}
