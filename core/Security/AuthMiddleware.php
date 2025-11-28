<?php

require_once __DIR__ . '/../../repositories/UserRepository.php';

class AuthMiddleware
{
    /**
     * Middleware simple para verificar autenticación
     */
    public static function verify()
    {
        $token = self::getAuthToken();
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Token de autenticación requerido',
                'code' => 401
            ];
        }

        try {
            $payload = JWTService::validateToken($token);
            
            if (!$payload) {
                return [
                    'success' => false,
                    'message' => 'Token inválido',
                    'code' => 401
                ];
            }

            // Verificar usuario aún existe
            if (isset($payload['user_id'])) {
                $userRepo = new UserRepository();
                $user = $userRepo->findById($payload['user_id']);
                if (!$user) {
                    return [
                        'success' => false,
                        'message' => 'Usuario no encontrado',
                        'code' => 401
                    ];
                }

                return [
                    'success' => true,
                    'user' => $user,
                    'payload' => $payload
                ];
            }

            return [
                'success' => true,
                'payload' => $payload
            ];

        } catch (Exception $e) {
            error_log("Auth error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de autenticación: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Obtiene el token de autorización de los headers
     */
    private static function getAuthToken()
    {
        $headers = getallheaders();
        $authHeader = '';
        
        // Buscar header de autorización (case-insensitive)
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $authHeader = $value;
                break;
            }
        }

        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            return trim(substr($authHeader, 7)); // Remover "Bearer " y espacios
        }

        return null;
    }

    /**
     * Obtiene la IP del cliente
     */
    private static function getClientIP()
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

?>