<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Security/AuthMiddleware.php';
require_once __DIR__ . '/../services/HistorialCuidadorService.php';

/**
 * HistorialCuidadorController - Controlador REST para historiales de cuidador
 * 
 * Maneja las peticiones HTTP y coordina con el servicio.
 * Responsabilidades:
 * - Validar datos de entrada HTTP
 * - Extraer parámetros de la petición
 * - Invocar métodos del servicio
 * - Formatear respuestas HTTP (códigos de estado, JSON)
 * - NO contiene lógica de negocio
 * 
 * Permisos:
 * - Crear/Editar: Solo Administradores y Cuidadores
 * - Leer: Todos los usuarios autenticados
 */
class HistorialCuidadorController extends BaseController
{
    private HistorialCuidadorService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new HistorialCuidadorService();
    }

    /**
     * GET /historiales-cuidador
     * Obtiene todos los historiales con paginación opcional
     * 
     * Query params:
     * - limit: int (opcional)
     * - offset: int (opcional, default: 0)
     */
    public function obtenerTodos(array $params = []): void
    {
        try {
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->service->getAllHistoriales($limit, $offset);
            
            if ($result['success']) {
                $response = [
                    'data' => $result['data'],
                    'pagination' => $result['pagination'] ?? null
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $this->jsonError($result['message'], 500);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * GET /historiales-cuidador/:id
     * Obtiene un historial por ID con detalles completos
     */
    public function obtenerPorId(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de historial requerido", 400);
                return;
            }

            $id = (int)$params['id'];
            $result = $this->service->getHistorialById($id);
            
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
     * GET /historiales-cuidador/paciente/:id
     * Obtiene historiales de un paciente específico
     * 
     * Query params:
     * - limit: int (opcional)
     * - offset: int (opcional, default: 0)
     */
    public function obtenerPorPaciente(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            $idPaciente = (int)$params['id'];
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->service->getHistorialesByPaciente($idPaciente, $limit, $offset);
            
            if ($result['success']) {
                $response = [
                    'data' => $result['data'],
                    'pagination' => $result['pagination'] ?? null
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $statusCode = $result['message'] === 'El paciente no existe' ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * GET /historiales-cuidador/cuidador/:id
     * Obtiene historiales de un cuidador específico
     * 
     * Query params:
     * - limit: int (opcional)
     * - offset: int (opcional, default: 0)
     */
    public function obtenerPorCuidador(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de cuidador requerido", 400);
                return;
            }

            $idCuidador = (int)$params['id'];
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->service->getHistorialesByCuidador($idCuidador, $limit, $offset);
            
            if ($result['success']) {
                $response = [
                    'data' => $result['data'],
                    'pagination' => $result['pagination'] ?? null
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $statusCode = $result['message'] === 'El cuidador no existe' ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * GET /historiales-cuidador/buscar
     * Busca historiales con criterios complejos
     * 
     * Query params:
     * - id_paciente: int (opcional)
     * - id_cuidador: int (opcional)
     * - fecha_desde: string Y-m-d (opcional)
     * - fecha_hasta: string Y-m-d (opcional)
     * - detalle: string (opcional, búsqueda parcial)
     * - limit: int (opcional, default: 50)
     * - offset: int (opcional, default: 0)
     * - order_by: string (opcional, default: fecha_historial)
     * - order_direction: ASC|DESC (opcional, default: DESC)
     */
    public function buscar(array $params): void
    {
        try {
            $result = $this->service->searchHistoriales($params);
            
            if ($result['success']) {
                $response = [
                    'data' => $result['data'],
                    'pagination' => $result['pagination'] ?? null
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
     * POST /historiales-cuidador
     * Crea un nuevo historial de cuidador
     * 
     * Permisos: Solo Administradores y Cuidadores
     * 
     * Body JSON:
     * {
     *   "detalle": "string (requerido, 5-255 caracteres)",
     *   "id_paciente": int (requerido),
     *   "id_cuidador": int (requerido),
     *   "fecha_historial": "string Y-m-d o Y-m-d H:i:s (opcional)"
     * }
     */
    public function crear(): void
    {
        try {
            // Obtener datos del body
            $data = $this->getJsonInput();
            
            if (empty($data)) {
                $this->jsonError("Datos no proporcionados", 400);
                return;
            }

            // Verificar autenticación
            $authResult = AuthMiddleware::verify();
            if (!$authResult['success']) {
                $this->jsonError($authResult['message'], $authResult['code'] ?? 401);
                return;
            }

            $user = $authResult['user'];

            // Verificar permisos: Solo Administradores y Cuidadores pueden crear
            if (!in_array($user->rol, ['Administrador', 'Cuidador'])) {
                $this->jsonError("No tiene permisos para crear historiales", 403);
                return;
            }

            // Agregar el ID del usuario que crea
            $data['created_by'] = $user->id;

            $result = $this->service->createHistorial($data);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message'], 201);
            } else {
                $this->jsonError($result['message'], 400);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * PUT /historiales-cuidador/:id
     * Actualiza un historial existente
     * 
     * Permisos: Solo Administradores y Cuidadores pueden editar
     * 
     * Body JSON:
     * {
     *   "detalle": "string (opcional, 5-255 caracteres)",
     *   "fecha_historial": "string Y-m-d o Y-m-d H:i:s (opcional)"
     * }
     */
    public function actualizar(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de historial requerido", 400);
                return;
            }

            $id = (int)$params['id'];
            
            // Obtener datos del body
            $data = $this->getJsonInput();
            
            if (empty($data)) {
                $this->jsonError("Datos no proporcionados", 400);
                return;
            }

            // Verificar autenticación
            $authResult = AuthMiddleware::verify();
            if (!$authResult['success']) {
                $this->jsonError($authResult['message'], $authResult['code'] ?? 401);
                return;
            }

            $user = $authResult['user'];

            // Verificar permisos: Solo Administradores y Cuidadores pueden actualizar
            if (!in_array($user->rol, ['Administrador', 'Cuidador'])) {
                $this->jsonError("No tiene permisos para actualizar historiales", 403);
                return;
            }

            $result = $this->service->updateHistorial($id, $data, $user->id);
            
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
     * DELETE /historiales-cuidador/:id
     * Elimina un historial
     * 
     * Permisos: Solo Administradores pueden eliminar
     */
    public function eliminar(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de historial requerido", 400);
                return;
            }

            $id = (int)$params['id'];

            // Verificar autenticación
            $authResult = AuthMiddleware::verify();
            if (!$authResult['success']) {
                $this->jsonError($authResult['message'], $authResult['code'] ?? 401);
                return;
            }

            $user = $authResult['user'];

            // Verificar permisos: Solo Administradores pueden eliminar
            if ($user->rol !== 'Administrador') {
                $this->jsonError("No tiene permisos para eliminar historiales", 403);
                return;
            }

            $result = $this->service->deleteHistorial($id);
            
            if ($result['success']) {
                $this->jsonResponse(null, $result['message']);
            } else {
                $statusCode = $result['message'] === 'Historial no encontrado' ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * GET /historiales-cuidador/estadisticas/paciente/:id
     * Obtiene estadísticas de historiales por paciente
     */
    public function estadisticasPorPaciente(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            $idPaciente = (int)$params['id'];
            
            $result = $this->service->getEstadisticasPorPaciente($idPaciente);
            
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
     * GET /historiales-cuidador/cuidador-asignado/:id
     * Obtiene los historiales de pacientes asignados a un cuidador específico
     * 
     * Retorna todos los historiales de los pacientes que tienen al menos un historial
     * creado por el cuidador especificado.
     * 
     * Query params:
     * - limit: int (opcional)
     * - offset: int (opcional, default: 0)
     */
    public function historialesPacientesAsignadosPorCuidador(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de cuidador requerido", 400);
                return;
            }

            $idCuidador = (int)$params['id'];
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            $idPaciente = isset($params['id_paciente']) && is_numeric($params['id_paciente']) ? (int)$params['id_paciente'] : null;
            
            $result = $this->service->getHistorialesPacientesAsignadosByCuidador($idCuidador, $limit, $offset, $idPaciente);
            
            if ($result['success']) {
                $response = [
                    'data' => $result['data'],
                    'pagination' => $result['pagination'] ?? null
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $statusCode = $result['message'] === 'El cuidador no existe' ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }
}
