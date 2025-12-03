<?php

require_once __DIR__ . '/../services/AsignacionService.php';

class AsignacionController extends BaseController
{
    private $asignacionService;

    public function __construct()
    {
        $this->asignacionService = new AsignacionService();
    }
    /**
     * Obtiene todas las asignaciones
     */
    public function obtenerTodas($params = [])
    {
        try {
            $asignaciones = $this->asignacionService->getAllWithDetails();
            
            $response = array_map(function($dto) {
                return $dto->toArray();
            }, $asignaciones);
            
            $this->jsonResponse($response);
        } catch (Exception $e) {
            $this->jsonError("Error al obtener asignaciones: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene todas las asignaciones con paginación
     */
    public function obtenerTodasPaginadas($params = [])
    {
        try {
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->asignacionService->getAllPaginated($limit, $offset);
            
            if ($result['success']) {
                $response = [
                    'data' => array_map(function($dto) {
                        return $dto->toArray();
                    }, $result['data']),
                    'pagination' => $result['pagination']
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $this->jsonError($result['message'], 500);
            }
        } catch (Exception $e) {
            $this->jsonError("Error al obtener asignaciones: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene asignaciones por usuario
     */
    public function obtenerPorUser($params)
    {
        try {
            if (!isset($params['id'])) {
                $this->jsonError("ID de usuario requerido", 400);
                return;
            }

            $asignaciones = $this->asignacionService->getByUserWithDetails($params['id']);
            
            $response = array_map(function($dto) {
                return $dto->toArray();
            }, $asignaciones);
            
            $this->jsonResponse($response);
        } catch (Exception $e) {
            $this->jsonError("Error al obtener asignaciones del usuario: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene asignaciones por paciente
     */
    public function obtenerPorPaciente($params)
    {
        try {
            if (!isset($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            $asignaciones = $this->asignacionService->getByPaciente($params['id']);
            
            $response = array_map(function($dto) {
                return $dto->toArray();
            }, $asignaciones);
            
            $this->jsonResponse($response);
        } catch (Exception $e) {
            $this->jsonError("Error al obtener asignaciones del paciente: " . $e->getMessage(), 500);
        }
    }

    /**
     * Crea una nueva asignación
     */
    public function crear($params = [])
    {
        try {
            $data = $this->getJsonInput();
            
            if (!$data) {
                $this->jsonError("Datos JSON requeridos", 400);
                return;
            }

            // Validar campos requeridos
            $required = ['user_id', 'paciente_id'];
            $missing = $this->validateRequired($data, $required);

            if (!empty($missing)) {
                $this->jsonError("Campos requeridos faltantes", 400, $missing);
                return;
            }

            // Crear nueva asignación usando el service
            $asignacionDto = $this->asignacionService->create($data);

            $this->jsonResponse($asignacionDto->toArray(), "Asignación creada exitosamente", 201);

        } catch (Exception $e) {
            $statusCode = strpos($e->getMessage(), 'validación') !== false || 
                         strpos($e->getMessage(), 'ya está asignado') !== false ? 400 : 500;
            $this->jsonError("Error al crear asignación: " . $e->getMessage(), $statusCode);
        }
    }

    /**
     * Elimina una asignación
     */
    public function eliminar($params)
    {
        try {
            if (!isset($params['id'])) {
                $this->jsonError("ID de asignación requerido", 400);
                return;
            }

            $result = $this->asignacionService->delete($params['id']);

            if (!$result) {
                $this->jsonError("Asignación no encontrada", 404);
                return;
            }

            $this->jsonResponse(null, "Asignación eliminada exitosamente");

        } catch (Exception $e) {
            $this->jsonError("Error al eliminar asignación: " . $e->getMessage(), 500);
        }
    }

    /**
     * Elimina asignación por usuario y paciente
     */
    public function eliminarPorUserYPaciente($params = [])
    {
        try {
            $data = $this->getJsonInput();
            
            if (!$data || !isset($data['user_id']) || !isset($data['paciente_id'])) {
                $this->jsonError("user_id y paciente_id son requeridos", 400);
                return;
            }

            $result = $this->asignacionService->unassignUserFromPaciente($data['user_id'], $data['paciente_id']);

            $this->jsonResponse(null, "Asignación eliminada exitosamente");

        } catch (Exception $e) {
            $this->jsonError("Error al eliminar asignación: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene estadísticas de asignaciones
     */
    public function obtenerEstadisticas($params = [])
    {
        try {
            $estadisticasDto = $this->asignacionService->getEstadisticas();
            
            $this->jsonResponse($estadisticasDto->toArray());
        } catch (Exception $e) {
            $this->jsonError("Error al obtener estadísticas: " . $e->getMessage(), 500);
        }
    }
}

?>