<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/Security/AuthMiddleware.php';
require_once __DIR__ . '/../services/IndicacionMedicaService.php';

/**
 * IndicacionMedicaController - Controlador REST para indicaciones médicas
 * 
 * Maneja las peticiones HTTP y coordina con el servicio.
 * 
 * Permisos:
 * - Crear: Administrador, Médico, Profesional (user_id se obtiene del JWT)
 * - Leer: Todos los autenticados. Cuidador solo ve indicaciones de pacientes asignados.
 * - Actualizar: Administrador edita cualquiera. Médico/Profesional solo las propias.
 * - Eliminar: Administrador elimina cualquiera. Médico/Profesional solo las propias.
 */
class IndicacionMedicaController extends BaseController
{
    private IndicacionMedicaService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new IndicacionMedicaService();
    }

    /**
     * GET /indicaciones-medicas
     * Obtiene todas las indicaciones con paginación opcional
     * 
     * Query params:
     * - limit: int (opcional)
     * - offset: int (opcional, default: 0)
     */
    public function obtenerTodas(array $params = []): void
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->jsonError("No autenticado", 401);
                return;
            }

            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->service->getAllIndicaciones($user['id'], $user['rol'], $limit, $offset);
            
            if ($result['success']) {
                $response = [
                    'indicaciones' => $result['data'],
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
     * GET /indicaciones-medicas/:id
     * Obtiene una indicación por ID con detalles completos
     */
    public function obtenerPorId(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de indicación requerido", 400);
                return;
            }

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->jsonError("No autenticado", 401);
                return;
            }

            $id = (int)$params['id'];
            $result = $this->service->getIndicacionById($id, $user['id'], $user['rol']);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                if (strpos($result['message'], 'Acceso denegado') !== false) {
                    $statusCode = 403;
                } elseif ($result['message'] === 'Indicación no encontrada') {
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
     * GET /indicaciones-medicas/paciente/:id
     * Obtiene indicaciones de un paciente específico
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

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->jsonError("No autenticado", 401);
                return;
            }

            $pacienteId = (int)$params['id'];
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->service->getIndicacionesByPaciente($pacienteId, $user['id'], $user['rol'], $limit, $offset);
            
            if ($result['success']) {
                $response = [
                    'indicaciones' => $result['data'],
                    'total' => $result['total'],
                    'showing' => $result['showing']
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                if (strpos($result['message'], 'Acceso denegado') !== false) {
                    $statusCode = 403;
                } elseif (strpos($result['message'], 'no existe') !== false) {
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
     * GET /indicaciones-medicas/buscar
     * Buscar indicaciones con filtros múltiples
     * 
     * Query params:
     * - paciente_id: int (opcional)
     * - user_id: int (opcional)
     * - fecha_desde: string YYYY-MM-DD (opcional)
     * - fecha_hasta: string YYYY-MM-DD (opcional)
     * - indicaciones: string (opcional, búsqueda parcial)
     * - limit: int (opcional)
     * - offset: int (opcional, default: 0)
     */
    public function buscar(array $params): void
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->jsonError("No autenticado", 401);
                return;
            }

            // Merge query params with route params
            $queryParams = $_GET ?? [];
            $searchParams = array_merge($params, $queryParams);
            
            $searchDto = new IndicacionMedicaSearchDto($searchParams);
            $result = $this->service->searchIndicaciones($searchDto, $user['id'], $user['rol']);
            
            if ($result['success']) {
                $response = [
                    'indicaciones' => $result['data'],
                    'total' => $result['total'],
                    'showing' => $result['showing']
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                if (strpos($result['message'], 'Acceso denegado') !== false) {
                    $statusCode = 403;
                } else {
                    $statusCode = 400;
                }
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (InvalidArgumentException $e) {
            $this->jsonError("Error de validación: " . $e->getMessage(), 400);
        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * POST /indicaciones-medicas
     * Crea una nueva indicación médica
     * 
     * El user_id se obtiene automáticamente del JWT del usuario autenticado.
     * 
     * Body JSON:
     * {
     *   "paciente_id": 123,
     *   "indicaciones": "Texto de las indicaciones (mínimo 10 caracteres)"
     * }
     */
    public function crear(): void
    {
        try {
            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->jsonError("No autenticado", 401);
                return;
            }

            $inputData = $this->getJsonInput();
            
            // Inyectar user_id y created_by desde el JWT
            $inputData['user_id'] = $user['id'];
            $inputData['created_by'] = $user['id'];
            
            $createDto = new CreateIndicacionMedicaDto($inputData);
            $result = $this->service->createIndicacion($createDto, $user['id'], $user['rol']);
            
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
     * PUT /indicaciones-medicas/:id
     * Actualiza una indicación existente
     * 
     * Body JSON:
     * {
     *   "indicaciones": "Nuevo texto de las indicaciones" (opcional),
     *   "paciente_id": 456 (opcional)
     * }
     */
    public function actualizar(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de indicación requerido", 400);
                return;
            }

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->jsonError("No autenticado", 401);
                return;
            }

            $id = (int)$params['id'];
            $inputData = $this->getJsonInput();
            
            $result = $this->service->updateIndicacion($id, $inputData, $user['id'], $user['rol']);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                if (strpos($result['message'], 'Acceso denegado') !== false) {
                    $statusCode = 403;
                } elseif ($result['message'] === 'Indicación no encontrada') {
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
     * DELETE /indicaciones-medicas/:id
     * Elimina una indicación
     */
    public function eliminar(array $params): void
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de indicación requerido", 400);
                return;
            }

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                $this->jsonError("No autenticado", 401);
                return;
            }

            $id = (int)$params['id'];
            $result = $this->service->deleteIndicacion($id, $user['id'], $user['rol']);
            
            if ($result['success']) {
                $this->jsonResponse(null, $result['message'], 200);
            } else {
                if (strpos($result['message'], 'Acceso denegado') !== false) {
                    $statusCode = 403;
                } elseif ($result['message'] === 'Indicación no encontrada') {
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
