<?php

/**
 * Authorization Middleware
 * Maneja autorización basada en roles y permisos
 */
class AuthorizationMiddleware
{
    /**
     * Roles y sus permisos
     */
    private static $rolePermissions = [
        'Administrador' => [
            'usuarios.*',
            'doctores.*',
            'pacientes.*',
            'historiales.*',
            'historiales_cuidador.*',
            'asignaciones.*',
            'reportes.*',
            'configuracion.*',
            'recetas.read',
            'recetas.update',
            'recetas.delete',
            'indicaciones.*'
        ],
        'Profesional' => [
            'pacientes.*',
            'historiales.*',
            'historiales_cuidador.read',
            'asignaciones.read',
            'asignaciones.create',
            'reportes.read',
            'usuarios.read',
            'doctores.read',
            'indicaciones.*'
        ],
        'Medico' => [
            'pacientes.*',
            'historiales.*',
            'historiales_cuidador.read',
            'asignaciones.read',
            'asignaciones.create',
            'reportes.read',
            'usuarios.read',
            'doctores.read',
            'recetas.create',
            'recetas.read',
            'indicaciones.*'
        ],
        'Cuidador' => [
            'pacientes.read',
            'pacientes.update',
            'historiales.read',
            'historiales.create',
            'historiales_cuidador.*',
            'usuarios.read',
            'asignaciones.read',
            'indicaciones.read'
        ]
    ];

    /**
     * Mapeo de rutas a permisos requeridos
     */
    private static $routePermissions = [
        // Usuarios
        'GET:/users' => 'usuarios.read',
        'POST:/users' => 'usuarios.create',
        'PUT:/users' => 'usuarios.update',
        'DELETE:/users' => 'usuarios.delete',
        
        // Doctores
        'GET:/doctores' => 'doctores.read',
        'POST:/doctores' => 'doctores.create',
        'PUT:/doctores' => 'doctores.update',
        'DELETE:/doctores' => 'doctores.delete',

        // Recetas
        'GET:/recetas-medicas' => 'recetas.read',
        'POST:/recetas-medicas' => 'recetas.create',
        'PUT:/recetas-medicas' => 'recetas.update',
        'DELETE:/recetas-medicas' => 'recetas.delete',
        
        // Pacientes
        'GET:/pacientes' => 'pacientes.read',
        'POST:/pacientes' => 'pacientes.create',
        'PUT:/pacientes' => 'pacientes.update',
        'DELETE:/pacientes' => 'pacientes.delete',
        
        // Historiales
        'GET:/historiales' => 'historiales.read',
        'POST:/historiales' => 'historiales.create',
        'PUT:/historiales' => 'historiales.update',
        'DELETE:/historiales' => 'historiales.delete',
        
        // Asignaciones
        'GET:/asignaciones' => 'asignaciones.read',
        'POST:/asignaciones' => 'asignaciones.create',
        'DELETE:/asignaciones' => 'asignaciones.delete',

        // Indicaciones Médicas
        'GET:/indicaciones-medicas' => 'indicaciones.read',
        'POST:/indicaciones-medicas' => 'indicaciones.create',
        'PUT:/indicaciones-medicas' => 'indicaciones.update',
        'DELETE:/indicaciones-medicas' => 'indicaciones.delete'
    ];

    /**
     * Verifica si un usuario tiene permiso para acceder a una ruta
     */
    public static function authorize($method, $path, $userRole = null)
    {
        // Si no hay usuario autenticado, usar el global
        if ($userRole === null) {
            if (!isset($GLOBALS['current_user_payload'])) {
                return [
                    'success' => false,
                    'message' => 'Usuario no autenticado',
                    'code' => 401
                ];
            }
            $userRole = $GLOBALS['current_user_payload']['role'] ?? 'Cuidador';
        }

        // Limpiar la ruta de parámetros para el mapeo
        $cleanPath = self::cleanPathForMapping($path);
        $routeKey = $method . ':' . $cleanPath;

        // Verificar si la ruta requiere permisos específicos
        if (!isset(self::$routePermissions[$routeKey])) {
            // Si no está mapeada, permitir acceso (backward compatibility)
            return ['success' => true];
        }

        $requiredPermission = self::$routePermissions[$routeKey];

        // Verificar si el rol tiene el permiso
        if (self::hasPermission($userRole, $requiredPermission)) {
            return ['success' => true];
        }

        // Log del intento de acceso no autorizado
        error_log("Unauthorized access attempt from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . 
                 " - User role: $userRole, Required permission: $requiredPermission, Route: $routeKey");

        return [
            'success' => false,
            'message' => 'No tienes permisos para acceder a este recurso',
            'code' => 403
        ];
    }

    /**
     * Verifica si un rol tiene un permiso específico
     */
    private static function hasPermission($role, $permission)
    {
        if (!isset(self::$rolePermissions[$role])) {
            return false;
        }

        $rolePerms = self::$rolePermissions[$role];

        foreach ($rolePerms as $rolePerm) {
            // Verificar permiso exacto
            if ($rolePerm === $permission) {
                return true;
            }

            // Verificar wildcard (ej: "usuarios.*" coincide con "usuarios.read")
            if (strpos($rolePerm, '*') !== false) {
                $pattern = str_replace('*', '.*', $rolePerm);
                if (preg_match('/^' . $pattern . '$/', $permission)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Limpia la ruta de parámetros para mapeo de permisos
     */
    private static function cleanPathForMapping($path)
    {
        // Remover parámetros numéricos y específicos para generalizar las rutas
        $cleanPath = preg_replace('/\/\d+$/', '', $path); // /pacientes/123 -> /pacientes
        $cleanPath = preg_replace('/\/\d+\//', '/', $cleanPath); // /users/123/pacientes -> /users/pacientes
        $cleanPath = preg_replace('/\/[a-zA-Z0-9_-]+\/\d+$/', '', $cleanPath); // /pacientes/rut/12345 -> /pacientes
        
        return $cleanPath;
    }

    /**
     * Middleware principal para autorización
     */
    public static function handle($requiredRole = null, $requiredPermission = null)
    {
        if (!isset($GLOBALS['current_user_payload'])) {
            return [
                'success' => false,
                'message' => 'Usuario no autenticado',
                'code' => 401
            ];
        }

        $userRole = $GLOBALS['current_user_payload']['role'] ?? 'Cuidador';

        // Verificar rol requerido
        if ($requiredRole && !self::hasRole($userRole, $requiredRole)) {
            return [
                'success' => false,
                'message' => 'Rol insuficiente para acceder a este recurso',
                'code' => 403
            ];
        }

        // Verificar permiso requerido
        if ($requiredPermission && !self::hasPermission($userRole, $requiredPermission)) {
            return [
                'success' => false,
                'message' => 'Permisos insuficientes para acceder a este recurso',
                'code' => 403
            ];
        }

        return ['success' => true];
    }

    /**
     * Verifica si un usuario tiene un rol específico o superior
     */
    private static function hasRole($userRole, $requiredRole)
    {
        $roleHierarchy = [
            'Cuidador' => 1,
            'Profesional' => 2,
            'Medico' => 3,
            'Administrador' => 4
        ];

        $userLevel = $roleHierarchy[$userRole] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 999;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Obtiene los permisos de un rol
     */
    public static function getRolePermissions($role)
    {
        return self::$rolePermissions[$role] ?? [];
    }
}

?>