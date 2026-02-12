<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/IndicacionMedicaRepository.php';
require_once __DIR__ . '/../repositories/AsignacionRepository.php';
require_once __DIR__ . '/../dto/IndicacionMedicaDto.php';
require_once __DIR__ . '/../dto/CreateIndicacionMedicaDto.php';
require_once __DIR__ . '/../dto/IndicacionMedicaSearchDto.php';
require_once __DIR__ . '/../dto/IndicacionMedicaDetailDto.php';

/**
 * IndicacionMedicaService - Servicio de lógica de negocio para indicaciones médicas
 * 
 * Permisos:
 * - Crear: Administrador, Médico, Profesional (user_id se obtiene del JWT)
 * - Leer: Todos los autenticados. Cuidador solo ve indicaciones de sus pacientes asignados.
 * - Actualizar: Administrador puede editar cualquiera. Médico/Profesional solo las propias.
 * - Eliminar: Administrador puede eliminar cualquiera. Médico/Profesional solo las propias.
 */
class IndicacionMedicaService
{
    private IndicacionMedicaRepository $repository;
    private AsignacionRepository $asignacionRepository;

    public function __construct()
    {
        $this->repository = new IndicacionMedicaRepository();
        $this->asignacionRepository = new AsignacionRepository();
    }

    /**
     * Obtiene todas las indicaciones con paginación
     * Cuidadores solo ven indicaciones de pacientes asignados
     */
    public function getAllIndicaciones(int $userId, string $userRole, ?int $limit = null, int $offset = 0): array
    {
        try {
            if ($userRole === 'Cuidador') {
                return $this->getIndicacionesCuidador($userId, $limit, $offset);
            }

            $entities = $this->repository->getAll($limit, $offset);
            $indicaciones = [];
            foreach ($entities as $entity) {
                $indicaciones[] = IndicacionMedicaDto::fromEntity($entity);
            }
            $total = $this->repository->count();
            return [
                'success' => true,
                'message' => 'Indicaciones obtenidas correctamente',
                'data' => array_map(fn($dto) => $dto->toArray(), $indicaciones),
                'total' => $total,
                'showing' => count($indicaciones)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener indicaciones: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene indicaciones filtradas para un cuidador (solo pacientes asignados)
     */
    private function getIndicacionesCuidador(int $userId, ?int $limit = null, int $offset = 0): array
    {
        $pacienteIds = $this->getPacientesAsignados($userId);
        if (empty($pacienteIds)) {
            return [
                'success' => true,
                'message' => 'No tiene pacientes asignados',
                'data' => [],
                'total' => 0,
                'showing' => 0
            ];
        }

        $entities = $this->repository->getByPacientes($pacienteIds, $limit, $offset);
        $indicaciones = [];
        foreach ($entities as $entity) {
            $indicaciones[] = IndicacionMedicaDto::fromEntity($entity);
        }
        $total = $this->repository->countByPacientes($pacienteIds);
        return [
            'success' => true,
            'message' => 'Indicaciones obtenidas correctamente',
            'data' => array_map(fn($dto) => $dto->toArray(), $indicaciones),
            'total' => $total,
            'showing' => count($indicaciones)
        ];
    }

    /**
     * Obtiene una indicación por ID
     * Cuidadores solo pueden ver indicaciones de pacientes asignados
     */
    public function getIndicacionById(int $id, int $userId, string $userRole): array
    {
        try {
            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID de indicación inválido', 'data' => null];
            }
            $entity = $this->repository->getById($id);
            if (!$entity) {
                return ['success' => false, 'message' => 'Indicación no encontrada', 'data' => null];
            }

            // Cuidadores solo pueden ver indicaciones de sus pacientes asignados
            if ($userRole === 'Cuidador') {
                $pacienteIds = $this->getPacientesAsignados($userId);
                if (!in_array($entity->getPacienteId(), $pacienteIds)) {
                    return ['success' => false, 'message' => 'Acceso denegado: No tiene acceso a esta indicación', 'data' => null];
                }
            }

            $detailDto = IndicacionMedicaDetailDto::fromEntity($entity);
            return ['success' => true, 'message' => 'Indicación obtenida correctamente', 'data' => $detailDto->toArray()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al obtener la indicación: ' . $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Obtiene indicaciones por paciente
     * Cuidadores solo pueden consultar pacientes asignados
     */
    public function getIndicacionesByPaciente(int $pacienteId, int $userId, string $userRole, ?int $limit = null, int $offset = 0): array
    {
        try {
            if ($pacienteId <= 0) {
                return ['success' => false, 'message' => 'ID de paciente inválido', 'data' => []];
            }
            if (!$this->repository->pacienteExists($pacienteId)) {
                return ['success' => false, 'message' => 'El paciente no existe', 'data' => []];
            }

            // Cuidadores solo pueden consultar pacientes asignados
            if ($userRole === 'Cuidador') {
                $pacienteIds = $this->getPacientesAsignados($userId);
                if (!in_array($pacienteId, $pacienteIds)) {
                    return ['success' => false, 'message' => 'Acceso denegado: No tiene acceso a este paciente', 'data' => []];
                }
            }

            $entities = $this->repository->getByPaciente($pacienteId, $limit, $offset);
            $indicaciones = [];
            foreach ($entities as $entity) {
                $indicaciones[] = IndicacionMedicaDto::fromEntity($entity);
            }
            $total = $this->repository->count(['paciente_id' => $pacienteId]);
            return [
                'success' => true,
                'message' => 'Indicaciones del paciente obtenidas correctamente',
                'data' => array_map(fn($dto) => $dto->toArray(), $indicaciones),
                'total' => $total,
                'showing' => count($indicaciones)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al obtener indicaciones: ' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * Búsqueda avanzada de indicaciones
     * Cuidadores solo ven indicaciones de pacientes asignados
     */
    public function searchIndicaciones(IndicacionMedicaSearchDto $searchDto, int $userId, string $userRole): array
    {
        try {
            $filters = [];
            if ($searchDto->getPacienteId() !== null) { $filters['paciente_id'] = $searchDto->getPacienteId(); }
            if ($searchDto->getUserId() !== null) { $filters['user_id'] = $searchDto->getUserId(); }
            if ($searchDto->getFechaDesde() !== null) { $filters['fecha_desde'] = $searchDto->getFechaDesde(); }
            if ($searchDto->getFechaHasta() !== null) { $filters['fecha_hasta'] = $searchDto->getFechaHasta(); }
            if ($searchDto->getIndicacionesBusqueda() !== null) { $filters['indicaciones'] = $searchDto->getIndicacionesBusqueda(); }

            // Cuidadores solo ven indicaciones de pacientes asignados
            if ($userRole === 'Cuidador') {
                $pacienteIds = $this->getPacientesAsignados($userId);
                if (empty($pacienteIds)) {
                    return [
                        'success' => true,
                        'message' => 'No tiene pacientes asignados',
                        'data' => [],
                        'total' => 0,
                        'showing' => 0
                    ];
                }
                // Si el cuidador filtra por paciente, verificar que sea uno asignado
                if (isset($filters['paciente_id']) && !in_array($filters['paciente_id'], $pacienteIds)) {
                    return ['success' => false, 'message' => 'Acceso denegado: No tiene acceso a este paciente', 'data' => []];
                }
                // Si no filtra por paciente específico, restringir a sus pacientes
                if (!isset($filters['paciente_id'])) {
                    $filters['paciente_ids'] = $pacienteIds;
                }
            }

            $entities = $this->repository->search($filters, $searchDto->getLimit(), $searchDto->getOffset());
            $indicaciones = [];
            foreach ($entities as $entity) {
                $indicaciones[] = IndicacionMedicaDto::fromEntity($entity);
            }
            $total = $this->repository->count($filters);
            return [
                'success' => true,
                'message' => 'Búsqueda completada correctamente',
                'data' => array_map(fn($dto) => $dto->toArray(), $indicaciones),
                'total' => $total,
                'showing' => count($indicaciones)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al buscar indicaciones: ' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * Crea una nueva indicación médica
     * Solo Administrador, Médico y Profesional. user_id se obtiene del JWT.
     */
    public function createIndicacion(CreateIndicacionMedicaDto $createDto, int $userId, string $userRole): array
    {
        try {
            // Verificar rol
            if (!in_array($userRole, ['Administrador', 'Medico', 'Profesional'])) {
                return ['success' => false, 'message' => 'Acceso denegado: Solo administradores, médicos y profesionales pueden crear indicaciones', 'data' => null];
            }

            // Verificar que el paciente exista
            if (!$this->repository->pacienteExists($createDto->getPacienteId())) {
                return ['success' => false, 'message' => 'El paciente especificado no existe', 'data' => null];
            }

            $id = $this->repository->create($createDto->toArray());
            $entity = $this->repository->getById($id);
            $detailDto = IndicacionMedicaDetailDto::fromEntity($entity);
            return ['success' => true, 'message' => 'Indicación médica creada exitosamente', 'data' => $detailDto->toArray()];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'message' => 'Error de validación: ' . $e->getMessage(), 'data' => null];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al crear la indicación: ' . $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Actualiza una indicación médica
     * Administrador puede editar cualquiera. Médico/Profesional solo las propias.
     */
    public function updateIndicacion(int $id, array $data, int $userId, string $userRole): array
    {
        try {
            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID de indicación inválido', 'data' => null];
            }

            // Verificar rol de escritura
            if (!in_array($userRole, ['Administrador', 'Medico', 'Profesional'])) {
                return ['success' => false, 'message' => 'Acceso denegado: Solo administradores, médicos y profesionales pueden editar indicaciones', 'data' => null];
            }

            if (!$this->repository->exists($id)) {
                return ['success' => false, 'message' => 'Indicación no encontrada', 'data' => null];
            }

            // Verificar propiedad: Médico/Profesional solo pueden editar sus propias indicaciones
            if ($userRole !== 'Administrador') {
                $propietarioId = $this->repository->getUserIdPropietario($id);
                if ($propietarioId !== $userId) {
                    return ['success' => false, 'message' => 'Acceso denegado: Solo puede editar sus propias indicaciones', 'data' => null];
                }
            }

            // Campos permitidos para actualizar
            $allowedFields = ['indicaciones', 'paciente_id'];
            $updateData = [];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) { $updateData[$field] = $data[$field]; }
            }
            if (empty($updateData)) {
                return ['success' => false, 'message' => 'No hay datos válidos para actualizar', 'data' => null];
            }

            // Validaciones de campos
            if (isset($updateData['indicaciones'])) {
                if (strlen($updateData['indicaciones']) < 10) {
                    return ['success' => false, 'message' => 'Las indicaciones deben tener al menos 10 caracteres', 'data' => null];
                }
                if (strlen($updateData['indicaciones']) > 65535) {
                    return ['success' => false, 'message' => 'Las indicaciones no pueden exceder 65535 caracteres', 'data' => null];
                }
            }

            if (isset($updateData['paciente_id'])) {
                $pacienteId = filter_var($updateData['paciente_id'], FILTER_VALIDATE_INT);
                if ($pacienteId === false || $pacienteId <= 0) {
                    return ['success' => false, 'message' => 'El ID de paciente debe ser un entero positivo', 'data' => null];
                }
                if (!$this->repository->pacienteExists($pacienteId)) {
                    return ['success' => false, 'message' => 'El paciente especificado no existe', 'data' => null];
                }
                $updateData['paciente_id'] = $pacienteId;
            }

            $updateData['updated_by'] = $userId;

            $this->repository->update($id, $updateData);
            $entity = $this->repository->getById($id);
            $detailDto = IndicacionMedicaDetailDto::fromEntity($entity);
            return ['success' => true, 'message' => 'Indicación actualizada exitosamente', 'data' => $detailDto->toArray()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar la indicación: ' . $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Elimina una indicación médica
     * Administrador puede eliminar cualquiera. Médico/Profesional solo las propias.
     */
    public function deleteIndicacion(int $id, int $userId, string $userRole): array
    {
        try {
            if ($id <= 0) {
                return ['success' => false, 'message' => 'ID de indicación inválido'];
            }

            // Verificar rol de escritura
            if (!in_array($userRole, ['Administrador', 'Medico', 'Profesional'])) {
                return ['success' => false, 'message' => 'Acceso denegado: Solo administradores, médicos y profesionales pueden eliminar indicaciones'];
            }

            if (!$this->repository->exists($id)) {
                return ['success' => false, 'message' => 'Indicación no encontrada'];
            }

            // Verificar propiedad: Médico/Profesional solo pueden eliminar sus propias indicaciones
            if ($userRole !== 'Administrador') {
                $propietarioId = $this->repository->getUserIdPropietario($id);
                if ($propietarioId !== $userId) {
                    return ['success' => false, 'message' => 'Acceso denegado: Solo puede eliminar sus propias indicaciones'];
                }
            }

            $this->repository->delete($id);
            return ['success' => true, 'message' => 'Indicación eliminada exitosamente'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al eliminar la indicación: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene los IDs de pacientes asignados a un cuidador
     */
    private function getPacientesAsignados(int $userId): array
    {
        $asignaciones = $this->asignacionRepository->findByUser($userId);
        $pacienteIds = [];
        foreach ($asignaciones as $asignacion) {
            if (is_array($asignacion) && isset($asignacion['paciente_id'])) {
                $pacienteIds[] = (int)$asignacion['paciente_id'];
            } elseif (is_object($asignacion) && method_exists($asignacion, 'getPacienteId')) {
                $pacienteIds[] = $asignacion->getPacienteId();
            }
        }
        return $pacienteIds;
    }
}
