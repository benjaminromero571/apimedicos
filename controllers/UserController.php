<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../services/UserService.php';

class UserController extends BaseController
{
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
    }

    /**
     * Obtiene todos los usuarios con ordenamiento opcional
     */
    public function obtenerTodos($params = [])
    {
        try {
            // Parámetros de paginación y ordenamiento
            $orderBy = isset($params['orderBy']) ? $params['orderBy'] : 'name ASC';
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->userService->getAllUsers($orderBy, $limit, $offset);
            
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
     * Obtiene un usuario por ID con detalles completos
     */
    public function obtenerPorId($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de usuario requerido", 400);
                return;
            }

            $result = $this->userService->getUserById($params['id']);
            
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
     * Obtiene el perfil completo del usuario
     */
    public function obtenerPerfil($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de usuario requerido", 400);
                return;
            }

            $result = $this->userService->getUserProfile($params['id']);
            
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
     * Obtiene usuarios por rol
     */
    public function obtenerPorRol($params)
    {
        try {
            if (!isset($params['rol']) || empty($params['rol'])) {
                $this->jsonError("Rol requerido", 400);
                return;
            }

            // Decodificar el rol si viene en URL
            $rol = urldecode($params['rol']);
            
            // Parámetros opcionales de paginación
            $orderBy = isset($params['orderBy']) ? $params['orderBy'] : 'name ASC';
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->userService->getUsersByRole($rol, $orderBy, $limit, $offset);
            
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
     * Busca un usuario por email
     */
    public function obtenerPorEmail($params)
    {
        try {
            $email = $params['email'] ?? $_GET['email'] ?? '';
            
            if (empty($email)) {
                $this->jsonError("Email requerido", 400);
                return;
            }

            $result = $this->userService->getUserByEmail($email);
            
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
     * Obtiene roles disponibles
     */
    public function obtenerRoles($params = [])
    {
        try {
            $result = $this->userService->getAvailableRoles();
            
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
     * Crea un nuevo usuario
     */
    public function crear($params = [])
    {
        try {
            $data = $this->getJsonInput();
            
            if (!$data) {
                $this->jsonError("Datos JSON requeridos", 400);
                return;
            }

            $result = $this->userService->createUser($data);

            if ($result['success']) {
                $response = [
                    'id' => $result['id'],
                    'usuario' => $result['data']
                ];
                
                $this->jsonResponse($response, $result['message'], 201);
            } else {
                $statusCode = isset($result['errors']) ? 400 : 500;
                $errorMessage = $result['message'];
                
                if (isset($result['errors'])) {
                    $errorMessage .= ': ' . implode(', ', array_values($result['errors']));
                }
                
                $this->jsonError($errorMessage, $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Actualiza un usuario existente
     */
    public function actualizar($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de usuario requerido", 400);
                return;
            }

            $data = $this->getJsonInput();
            
            if (!$data) {
                $this->jsonError("Datos JSON requeridos", 400);
                return;
            }

            $result = $this->userService->updateUser($params['id'], $data);
            
            if ($result['success']) {
                $response = ['usuario' => $result['data']];
                $this->jsonResponse($response, $result['message']);
            } else {
                $statusCode = $result['message'] === 'Usuario no encontrado' ? 404 : 400;
                $errorMessage = $result['message'];
                
                if (isset($result['errors'])) {
                    $errorMessage .= ': ' . implode(', ', array_values($result['errors']));
                }
                
                $this->jsonError($errorMessage, $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Elimina un usuario
     */
    public function eliminar($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de usuario requerido", 400);
                return;
            }

            $result = $this->userService->deleteUser($params['id']);
            
            if ($result['success']) {
                $this->jsonResponse(null, $result['message']);
            } else {
                $statusCode = $result['message'] === 'Usuario no encontrado' ? 404 : 409;
                $errorMessage = $result['message'];
                
                if (isset($result['restrictions'])) {
                    $errorMessage .= ': ' . implode(', ', $result['restrictions']);
                }
                
                $this->jsonError($errorMessage, $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Busca usuarios según criterios
     */
    public function buscar($params = [])
    {
        try {
            // Obtener criterios de búsqueda desde query params y body
            $queryParams = $_GET ?? [];
            $bodyData = $this->getJsonInput() ?? [];
            $searchData = array_merge($queryParams, $bodyData);
            
            $result = $this->userService->searchUsers($searchData);
            
            if ($result['success']) {
                $response = [
                    'usuarios' => $result['data'],
                    'pagination' => $result['pagination'],
                    'search_criteria' => $result['search_criteria']
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $errorMessage = $result['message'];
                if (isset($result['errors'])) {
                    $errorMessage .= ': ' . implode(', ', array_values($result['errors']));
                }
                $this->jsonError($errorMessage, 400);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Autentica un usuario
     */
    public function autenticar($params = [])
    {
        try {
            $data = $this->getJsonInput();
            
            if (!$data || !isset($data['email']) || !isset($data['password'])) {
                $this->jsonError("Email y contraseña requeridos", 400);
                return;
            }

            $result = $this->userService->authenticateUser($data['email'], $data['password']);
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $this->jsonError($result['message'], 401);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Cambia la contraseña del usuario
     */
    public function cambiarPassword($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de usuario requerido", 400);
                return;
            }

            $data = $this->getJsonInput();
            
            if (!$data || !isset($data['current_password']) || !isset($data['new_password'])) {
                $this->jsonError("Contraseña actual y nueva contraseña requeridas", 400);
                return;
            }

            $result = $this->userService->changePassword(
                $params['id'], 
                $data['current_password'], 
                $data['new_password']
            );
            
            if ($result['success']) {
                $this->jsonResponse(null, $result['message']);
            } else {
                $statusCode = $result['message'] === 'Usuario no encontrado' ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Cambia la contraseña del usuario por un administrador
     */
    public function cambiarPasswordAdmin($params)
    {
        try {
            if (!isset($params['id']) || empty($params['id'])) {
                $this->jsonError("ID de usuario requerido", 400);
                return;
            }

            $data = $this->getJsonInput();
            
            if (!$data || !isset($data['new_password'])) {
                $this->jsonError("Nueva contraseña requerida", 400);
                return;
            }

            $result = $this->userService->adminChangeUserPassword(
                $params['id'], 
                $data['new_password']
            );
            
            if ($result['success']) {
                $this->jsonResponse(null, $result['message']);
            } else {
                $statusCode = $result['message'] === 'Usuario no encontrado' ? 404 : 400;
                $this->jsonError($result['message'], $statusCode);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene estadísticas de usuarios
     */
    public function obtenerEstadisticas($params = [])
    {
        try {
            $result = $this->userService->getUserStatistics();
            
            if ($result['success']) {
                $this->jsonResponse($result['data'], $result['message']);
            } else {
                $this->jsonError($result['message'], 500);
            }

        } catch (Exception $e) {
            $this->jsonError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }
}

?>