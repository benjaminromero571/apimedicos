<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Security/AuthMiddleware.php';
require_once __DIR__ . '/../services/RecetaMedicaService.php';

/**
 * RecetaMedicaController - Controlador REST para recetas médicas
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
 * - Crear: Solo Médicos (solo pueden crear recetas a su nombre) y Administradores
 * - Editar: Solo el Médico propietario de la receta y Administradores
 * - Leer: Todos los usuarios autenticados
 * - Eliminar: Solo Administradores
 */
class RecetaMedicaController extends BaseController
{
    private RecetaMedicaService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new RecetaMedicaService();
    }

    /**
     * GET /recetas-medicas
     * Obtiene todas las recetas con paginación opcional
     * 
     * Query params:
     * - limit: int (opcional)
     * - offset: int (opcional, default: 0)
     */
    public function obtenerTodas(array $params = []): void
    {
        try {
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->service->getAllRecetas($limit, $offset);
            
            if ($result['success']) {
                $response = [
                    'recetas' => $result['data'],
                    'total' => $result['total'],
                    'showing' => $result['showing']
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
     * GET /recetas-medicas/:id
     * Obtiene una receta por ID con detalles completos
     */
    public function obtenerPorId(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de receta requerido", 400);
                return;
            }

            $id = (int)$params['id'];
            $result = $this->service->getRecetaById($id);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $statusCode = $result['message'] === 'Receta no encontrada' ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * GET /recetas-medicas/medico/:id
     * Obtiene recetas de un médico específico
     * 
     * Query params:
     * - limit: int (opcional)
     * - offset: int (opcional, default: 0)
     */
    public function obtenerPorMedico(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de médico requerido", 400);
                return;
            }

            $idMedico = (int)$params['id'];
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->service->getRecetasByMedico($idMedico, $limit, $offset);
            
            if ($result['success']) {
                $response = [
                    'recetas' => $result['data'],
                    'total' => $result['total'],
                    'showing' => $result['showing']
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $statusCode = strpos($result['message'], 'no existe') !== false ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * GET /recetas-medicas/buscar
     * Buscar recetas con filtros múltiples
     * 
     * Query params:
     * - id_medico: int (opcional)
     * - id_historial: int (opcional)
     * - fecha_desde: string YYYY-MM-DD (opcional)
     * - fecha_hasta: string YYYY-MM-DD (opcional)
     * - detalle: string (opcional, búsqueda parcial)
     * - limit: int (opcional)
     * - offset: int (opcional, default: 0)
     */
    public function buscar(array $params): void
    {
        try {
            // Merge query params with route params
            $queryParams = $_GET ?? [];
            $searchParams = array_merge($params, $queryParams);
            
            $searchDto = new RecetaMedicaSearchDto($searchParams);
            $result = $this->service->searchRecetas($searchDto);
            
            if ($result['success']) {
                $response = [
                    'recetas' => $result['data'],
                    'total' => $result['total'],
                    'showing' => $result['showing']
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $this->jsonError($result['message'], 500);
            }

        } catch (InvalidArgumentException $e) {
            $this->jsonError("Error de validación: " . $e->getMessage(), 400);
        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * POST /recetas-medicas
     * Crea una nueva receta médica
     * 
     * IMPORTANTE: Solo médicos pueden crear recetas, y solo a su propio nombre
     * Los administradores pueden crear recetas para cualquier médico
     * 
     * Body JSON:
     * {
     *   "detalle": "Detalle de la receta (mínimo 10 caracteres)",
     *   "fecha": "2024-01-15" (opcional, default: hoy),
    *   "id_medico": 123,
    *   "id_historial": 456
     * }
     */
    public function crear(): void
    {
        try {
            // Verificar autenticación
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->jsonError("No autenticado", 401);
                return;
            }

            // Obtener datos del body
            $inputData = $this->getJsonInput();
            
            // Agregar el ID del usuario autenticado como created_by
            $inputData['created_by'] = $user['id'];
            
            // Validar y crear DTO
            $createDto = new CreateRecetaMedicaDto($inputData);
            
            // Crear la receta (el servicio valida los permisos)
            $result = $this->service->createReceta($createDto, $user['id'], $user['rol']);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message'], 201);
            } else {
                $statusCode = strpos($result['message'], 'Acceso denegado') !== false ? 403 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (InvalidArgumentException $e) {
            $this->jsonError("Error de validación: " . $e->getMessage(), 400);
        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * PUT /recetas-medicas/:id
     * Actualiza una receta existente
     * 
     * IMPORTANTE: Solo el médico propietario puede editar su receta
     * Los administradores pueden editar cualquier receta
     * 
     * Body JSON:
     * {
     *   "detalle": "Nuevo detalle de la receta" (opcional),
    *   "fecha": "2024-01-20" (opcional),
    *   "id_historial": 456 (opcional)
     * }
     */
    public function actualizar(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de receta requerido", 400);
                return;
            }

            // Verificar autenticación
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->jsonError("No autenticado", 401);
                return;
            }

            $id = (int)$params['id'];
            $inputData = $this->getJsonInput();
            
            // Actualizar la receta (el servicio valida los permisos)
            $result = $this->service->updateReceta($id, $inputData, $user['id'], $user['rol']);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                if (strpos($result['message'], 'Acceso denegado') !== false) {
                    $statusCode = 403;
                } elseif ($result['message'] === 'Receta no encontrada') {
                    $statusCode = 404;
                } else {
                    $statusCode = 400;
                }
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /recetas-medicas/:id
     * Elimina una receta
     * 
     * IMPORTANTE: Solo administradores pueden eliminar recetas
     */
    public function eliminar(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de receta requerido", 400);
                return;
            }

            // Verificar autenticación
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->jsonError("No autenticado", 401);
                return;
            }

            $id = (int)$params['id'];
            
            // Eliminar la receta (el servicio valida los permisos)
            $result = $this->service->deleteReceta($id, $user['rol']);
            
            if ($result['success']) {
                $this->jsonResponse(null, $result['message'], 200);
            } else {
                if (strpos($result['message'], 'Acceso denegado') !== false) {
                    $statusCode = 403;
                } elseif ($result['message'] === 'Receta no encontrada') {
                    $statusCode = 404;
                } else {
                    $statusCode = 400;
                }
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * GET /recetas-medicas/estadisticas/medico/:id
     * Obtiene estadísticas de recetas de un médico
     */
    public function estadisticasPorMedico(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de médico requerido", 400);
                return;
            }

            $idMedico = (int)$params['id'];
            $result = $this->service->getEstadisticasByMedico($idMedico);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $statusCode = $result['message'] === 'El médico no existe' ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * GET /recetas-medicas/mis-recetas
     * Obtiene las recetas del médico autenticado
     * 
     * Query params:
     * - limit: int (opcional)
     * - offset: int (opcional, default: 0)
     */
    public function obtenerMisRecetas(array $params = []): void
    {
        try {
            // Verificar autenticación
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->jsonError("No autenticado", 401);
                return;
            }

            // Verificar que el usuario es médico
            if ($user['rol'] !== 'Medico') {
                $this->jsonError("Solo los médicos pueden acceder a este endpoint", 403);
                return;
            }

            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->service->getRecetasByMedico($user['id'], $limit, $offset);
            
            if ($result['success']) {
                $response = [
                    'recetas' => $result['data'],
                    'total' => $result['total'],
                    'showing' => $result['showing']
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
     * Obtiene el usuario autenticado desde el JWT
     */
    private function getAuthenticatedUser(): ?array
    {
        try {
            $headers = function_exists('getallheaders') ? getallheaders() : [];
            $authHeader = $headers['Authorization']
                ?? $headers['authorization']
                ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? ''));
            
            if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return null;
            }

            $token = $matches[1];
            require_once __DIR__ . '/../core/Security/JWTService.php';
            $jwtService = new JWTService();
            $payload = $jwtService->validateToken($token);

            if (!$payload || !is_array($payload)) {
                return null;
            }

            $userId = isset($payload['user_id']) ? (int)$payload['user_id'] : null;
            $role = $payload['rol'] ?? $payload['role'] ?? null;

            if (!$userId || !is_string($role) || trim($role) === '') {
                return null;
            }

            return [
                'id' => $userId,
                'rol' => $role,
                'email' => $payload['email'] ?? null
            ];

        } catch (Exception $e) {
            return null;
        }
    }
}
