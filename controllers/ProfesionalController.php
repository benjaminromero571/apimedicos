<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../services/ProfesionalService.php';

class ProfesionalController extends BaseController
{
    private $profesionalService;

    public function __construct()
    {
        parent::__construct();
        $this->profesionalService = new ProfesionalService();
    }

    /**
     * Obtiene todos los profesionales con ordenamiento opcional
     */
    public function obtenerTodos($params = [])
    {
        try {
            // Parámetros de paginación y ordenamiento
            $orderBy = isset($params['orderBy']) ? $params['orderBy'] : 'nombre ASC';
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->profesionalService->getAllProfesionales($orderBy, $limit, $offset);
            
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
     * Obtiene un profesional por ID con detalles completos
     */
    public function obtenerPorId($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de profesional requerido", 400);
                return;
            }

            $result = $this->profesionalService->getProfesionalById($params['id']);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $statusCode = $result['message'] === 'Profesional no encontrado' ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Busca un profesional por documento/cédula
     */
    public function obtenerPorDocumento($params)
    {
        try {
            if (!isset($params['documento']) || empty($params['documento'])) {
                $this->jsonError("Documento profesional requerido", 400);
                return;
            }

            $result = $this->profesionalService->getProfesionalByDocumento($params['documento']);
            
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
     * Método de compatibilidad para buscar por cédula
     */
    public function obtenerPorCedula($params)
    {
        // Mapear cédula a documento para compatibilidad
        if (isset($params['cedula'])) {
            $params['documento'] = $params['cedula'];
        }
        return $this->obtenerPorDocumento($params);
    }

    /**
     * Obtiene todas las especialidades disponibles
     */
    public function obtenerEspecialidades($params = [])
    {
        try {
            $result = $this->profesionalService->getEspecialidades();
            
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
     * Obtiene profesionales por especialidad
     */
    public function obtenerPorEspecialidad($params)
    {
        try {
            if (!isset($params['especialidad']) || empty($params['especialidad'])) {
                $this->jsonError("Especialidad requerida", 400);
                return;
            }

            // Decodificar la especialidad si viene en URL
            $especialidad = urldecode($params['especialidad']);
            
            // Parámetros opcionales de paginación
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->profesionalService->getProfesionalesByEspecialidad($especialidad, $limit, $offset);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $this->jsonError($result['message'], 400);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Crea un nuevo profesional
     */
    public function crear($params = [])
    {
        try {
            $data = $this->getJsonInput();
            
            if (!$data) {
                $this->jsonError("Datos JSON requeridos", 400);
                return;
            }

            $result = $this->profesionalService->createProfesional($data);

            if ($result['success']) {
                $response = [
                    'id' => $result['id'],
                    'profesional' => $result['data']
                ];
                
                // Incluir advertencias si las hay
                if (!empty($result['warnings'])) {
                    $response['warnings'] = $result['warnings'];
                }
                
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
     * Actualiza un profesional existente
     */
    public function actualizar($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de profesional requerido", 400);
                return;
            }

            $data = $this->getJsonInput();
            
            if (!$data) {
                $this->jsonError("Datos JSON requeridos", 400);
                return;
            }

            $result = $this->profesionalService->updateProfesional($params['id'], $data);
            
            if ($result['success']) {
                $response = ['profesional' => $result['data']];
                
                // Incluir advertencias si las hay
                if (!empty($result['warnings'])) {
                    $response['warnings'] = $result['warnings'];
                }
                
                $this->jsonResponse($response, $result['message']);
            } else {
                $statusCode = $result['message'] === 'Profesional no encontrado' ? 404 : 400;
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
     * Elimina un profesional
     */
    public function eliminar($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de profesional requerido", 400);
                return;
            }

            // Verificar si se puede eliminar
            $canDelete = $this->profesionalService->canDeleteProfesional($params['id']);
            
            if (!$canDelete['can_delete']) {
                $this->jsonError("No se puede eliminar: " . implode(', ', $canDelete['restrictions']), 409);
                return;
            }

            $result = $this->profesionalService->deleteProfesional($params['id']);
            
            if ($result['success']) {
                $this->jsonResponse(null, $result['message']);
            } else {
                $statusCode = $result['message'] === 'Profesional no encontrado' ? 404 : 500;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Busca profesionales según criterios
     */
    public function buscar($params = [])
    {
        try {
            // Obtener criterios de búsqueda desde query params y body
            $queryParams = $_GET ?? [];
            $bodyData = $this->getJsonInput() ?? [];
            $searchData = array_merge($queryParams, $bodyData);
            
            $result = $this->profesionalService->searchProfesionales($searchData);
            
            if ($result['success']) {
                $response = [
                    'profesionales' => $result['data'],
                    'pagination' => $result['pagination'],
                    'search_criteria' => $result['search_criteria']
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $errorMessage = $result['message'];
                if (isset($result['errors'])) {
                    $errorMessage .= ': ' . implode(', ', $result['errors']);
                }
                $this->jsonError($errorMessage, 400);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Busca profesionales por nombre
     */
    public function buscarPorNombre($params = [])
    {
        try {
            $nombre = $params['nombre'] ?? $_GET['nombre'] ?? '';
            
            if (empty($nombre)) {
                $this->jsonError("Nombre para búsqueda requerido", 400);
                return;
            }
            
            $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->profesionalService->searchByName($nombre, $limit, $offset);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $this->jsonError($result['message'], 400);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene estadísticas de profesionales por especialidad
     */
    public function obtenerEstadisticas($params = [])
    {
        try {
            $result = $this->profesionalService->getEstadisticasByEspecialidad();
            
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
     * Endpoint para validar documento profesional
     */
    public function validarDocumento($params)
    {
        try {
            if (!isset($params['documento']) || empty($params['documento'])) {
                $this->jsonError("Documento requerido para validación", 400);
                return;
            }

            $result = $this->profesionalService->getProfesionalByDocumento($params['documento']);
            
            if ($result['success']) {
                // Documento ya existe
                $response = [
                    'exists' => true,
                    'profesional' => $result['data']
                ];
                $this->jsonResponse($response, "Documento ya registrado");
            } else {
                // Documento disponible
                $response = [
                    'exists' => false,
                    'available' => true
                ];
                $this->jsonResponse($response, "Documento disponible");
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Método de compatibilidad para validar cédula
     */
    public function validarCedula($params)
    {
        // Mapear cédula a documento para compatibilidad
        if (isset($params['cedula'])) {
            $params['documento'] = $params['cedula'];
        }
        return $this->validarDocumento($params);
    }

    /**
     * Obtiene profesionales por usuario
     */
    public function obtenerPorUsuario($params)
    {
        try {
            if (!isset($params['user_id']) || empty($params['user_id'])) {
                $this->jsonError("ID de usuario requerido", 400);
                return;
            }

            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->profesionalService->getProfesionalesByUserId($params['user_id'], $limit, $offset);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $statusCode = $result['message'] === 'Usuario no encontrado' ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene profesionales sin usuario asignado
     */
    public function obtenerSinUsuario($params = [])
    {
        try {
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->profesionalService->getProfesionalesSinUsuario($limit, $offset);
            
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
     * Asigna un usuario a un profesional
     */
    public function asignarUsuario($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de profesional requerido", 400);
                return;
            }

            $data = $this->getJsonInput();
            
            if (!$data || !isset($data['user_id'])) {
                $this->jsonError("ID de usuario requerido en el body", 400);
                return;
            }

            $result = $this->profesionalService->asignarUsuario($params['id'], $data['user_id']);
            
            if ($result['success']) {
                $this->jsonResponse(null, $result['message']);
            } else {
                $statusCode = in_array($result['message'], ['Profesional no encontrado', 'Usuario no encontrado']) ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }
}

?>