<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Security/AuthMiddleware.php';
require_once __DIR__ . '/../services/HistorialService.php';

class HistorialController extends BaseController
{
    private $historialService;

    public function __construct()
    {
        parent::__construct();
        $this->historialService = new HistorialService();
    }

    /**
     * Obtiene todos los historiales con paginación
     */
    public function obtenerTodos($params = [])
    {
        try {
            // Parámetros de paginación
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->historialService->getAllHistoriales($limit, $offset);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $this->jsonError($result['message'], 500);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene un historial por ID con detalles completos
     */
    public function obtenerPorId($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de historial requerido", 400);
                return;
            }

            $result = $this->historialService->getHistorialById($params['id']);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $statusCode = $result['message'] === 'Historial no encontrado' ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene historiales de un paciente específico
     */
    public function obtenerPorPaciente($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            // Parámetros de paginación opcionales
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->historialService->getHistorialesByPaciente($params['id'], $limit, $offset);
            
            if ($result['success']) {
                // Incluir estadísticas en la respuesta
                $response = [
                    'historiales' => $result['data'],
                    'estadisticas' => $result['estadisticas'] ?? null,
                    'total' => $result['total']
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $this->jsonError($result['message'], 400);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene el último historial de un paciente
     */
    public function obtenerUltimoPorPaciente($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }
            
            $result = $this->historialService->getLastHistorialByPaciente($params['id']);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $statusCode = strpos($result['message'], 'no encontrado') !== false ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Crea un nuevo historial médico
     */
    public function crear($params = [])
    {
        try {
            $data = $this->getJsonInput();
            
            if (!$data) {
                $this->jsonError("Datos JSON requeridos", 400);
                return;
            }

            // Obtener usuario autenticado
            $authResult = AuthMiddleware::verify();
            if ($authResult['success'] && isset($authResult['payload']['user_id'])) {
                $data['created_by'] = $authResult['payload']['user_id'];
            }

            $result = $this->historialService->createHistorial($data);

            if ($result['success']) {
                $response = [
                    'id' => $result['id'],
                    'historial' => $result['data']
                ];
                $this->jsonResponse($response, $result['message'], 201);
            } else {
                $statusCode = isset($result['errors']) ? 400 : 500;
                $errorMessage = $result['message'];
                
                if (isset($result['errors'])) {
                    $errorMessage .= ': ' . implode(', ', $result['errors']);
                }
                
                $this->jsonError($errorMessage, $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Actualiza un historial existente
     */
    public function actualizar($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de historial requerido", 400);
                return;
            }

            $data = $this->getJsonInput();
            
            if (!$data) {
                $this->jsonError("Datos JSON requeridos", 400);
                return;
            }

            // Obtener usuario autenticado
            $authResult = AuthMiddleware::verify();
            if ($authResult['success'] && isset($authResult['payload']['user_id'])) {
                $data['updated_by'] = $authResult['payload']['user_id'];
            }

            $result = $this->historialService->updateHistorial($params['id'], $data);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $statusCode = $result['message'] === 'Historial no encontrado' ? 404 : 400;
                $errorMessage = $result['message'];
                
                if (isset($result['errors'])) {
                    $errorMessage .= ': ' . implode(', ', $result['errors']);
                }
                
                $this->jsonError($errorMessage, $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Elimina un historial
     */
    public function eliminar($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de historial requerido", 400);
                return;
            }

            $result = $this->historialService->deleteHistorial($params['id']);
            
            if ($result['success']) {
                $this->jsonResponse(null, $result['message']);
            } else {
                $statusCode = $result['message'] === 'Historial no encontrado' ? 404 : 500;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Busca historiales según criterios
     */
    public function buscar($params = [])
    {
        try {
            // Obtener criterios de búsqueda desde query params y body
            $queryParams = $_GET ?? [];
            $bodyData = $this->getJsonInput() ?? [];
            $searchData = array_merge($queryParams, $bodyData);
            
            $result = $this->historialService->searchHistoriales($searchData);
            
            if ($result['success']) {
                $response = [
                    'historiales' => $result['data'],
                    'pagination' => $result['pagination'],
                    'search_criteria' => $result['search_criteria']
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $this->jsonError($result['message'], 400);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene estadísticas médicas de un paciente
     */
    public function obtenerEstadisticas($params)
    {
        try {
            if (!isset($params['idpaciente']) || empty($params['idpaciente'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }
            
            $result = $this->historialService->getEstadisticasPaciente($params['idpaciente']);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $this->jsonError($result['message'], 404);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Endpoint para obtener resumen médico de un paciente
     */
    public function resumenMedico($params)
    {
        try {
            if (!isset($params['idpaciente']) || empty($params['idpaciente'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }
            
            // Obtener último historial
            $ultimoHistorial = $this->historialService->getLastHistorialByPaciente($params['idpaciente']);
            
            // Obtener estadísticas
            $estadisticas = $this->historialService->getEstadisticasPaciente($params['idpaciente']);
            
            $resumen = [
                'ultimo_historial' => $ultimoHistorial['success'] ? $ultimoHistorial['data'] : null,
                'estadisticas' => $estadisticas['success'] ? $estadisticas['data'] : null,
                'tiene_datos' => $ultimoHistorial['success'] || $estadisticas['success']
            ];
            
            $this->jsonResponse($resumen, "Resumen médico obtenido correctamente");

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }
}

?>