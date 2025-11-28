<?php

require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../dto/UserDto.php';
require_once __DIR__ . '/../dto/CreateUserDto.php';
require_once __DIR__ . '/../dto/UserSearchDto.php';
require_once __DIR__ . '/../dto/UserProfileDto.php';

/**
 * UserService
 * 
 * Servicio para gestión de usuarios
 * Contiene toda la lógica de negocio relacionada con usuarios, roles y permisos
 */
class UserService
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    /**
     * Obtiene todos los usuarios con ordenamiento
     */
    public function getAllUsers($orderBy = 'name ASC', $limit = null, $offset = 0)
    {
        try {
            $entities = $this->userRepository->getAll($orderBy, $limit, $offset);
            
            if ($entities === false) {
                return [
                    'success' => false,
                    'message' => 'No se pudieron obtener los usuarios',
                    'data' => []
                ];
            }

            $users = [];
            foreach ($entities as $entity) {
                $users[] = UserDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Usuarios obtenidos correctamente',
                'data' => $users,
                'total' => count($users)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener usuarios: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene un usuario por ID con detalles completos
     */
    public function getUserById($id)
    {
        try {
            $entity = $this->userRepository->findById($id);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'data' => null
                ];
            }

            $userDetail = new UserProfileDto($entity->toArray());
            
            // Agregar información adicional
            $this->enrichUserProfile($userDetail);

            return [
                'success' => true,
                'message' => 'Usuario encontrado correctamente',
                'data' => $userDetail
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener usuario: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Busca un usuario por email
     */
    public function getUserByEmail($email)
    {
        try {
            $entity = $this->userRepository->findByEmail($email);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'data' => null
                ];
            }

            $userDto = UserDto::fromEntity($entity);

            return [
                'success' => true,
                'message' => 'Usuario encontrado correctamente',
                'data' => $userDto
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar usuario: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Obtiene usuarios por rol
     */
    public function getUsersByRole($rol, $orderBy = 'name ASC', $limit = null, $offset = 0)
    {
        try {
            // Validar rol
            if (!$this->isValidRole($rol)) {
                return [
                    'success' => false,
                    'message' => 'Rol especificado no es válido',
                    'data' => []
                ];
            }

            $entities = $this->userRepository->getByRol($rol, $orderBy, $limit, $offset);
            
            $users = [];
            foreach ($entities as $entity) {
                $users[] = UserDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => "Usuarios con rol '{$rol}' obtenidos correctamente",
                'data' => $users,
                'total' => count($users),
                'rol' => $rol
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener usuarios por rol: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Crea un nuevo usuario
     */
    public function createUser($data)
    {
        try {
            $createDto = CreateUserDto::fromArray($data);
            
            // Validar datos básicos del DTO
            if (!$createDto->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $createDto->getErrors()
                ];
            }

            // Verificar email único
            if ($this->userRepository->emailExists($createDto->email)) {
                return [
                    'success' => false,
                    'message' => 'El email ya está registrado',
                    'errors' => ['email' => 'Este correo electrónico ya está en uso']
                ];
            }

            // Hashear contraseña
            $createDto->hashPassword();
            
            // Crear usuario
            $newEntity = $this->userRepository->create($createDto->toArray());
            
            if (!$newEntity) {
                return [
                    'success' => false,
                    'message' => 'Error al crear el usuario'
                ];
            }

            $userDto = UserDto::fromEntity($newEntity);

            return [
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data' => $userDto,
                'id' => $newEntity->id
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un usuario existente
     */
    public function updateUser($id, $data)
    {
        try {
            // Verificar que el usuario existe
            if (!$this->userRepository->exists($id)) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ];
            }

            // Validar datos de actualización
            $errors = $this->validateUpdateData($data, $id);
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $errors
                ];
            }

            // Si se actualiza la contraseña, hashearla
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                // No actualizar password si viene vacío
                unset($data['password']);
            }

            // Actualizar usuario
            $updatedEntity = $this->userRepository->update($id, $data);
            
            if (!$updatedEntity) {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar usuario'
                ];
            }

            $userDto = UserDto::fromEntity($updatedEntity);

            return [
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => $userDto
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un usuario
     */
    public function deleteUser($id)
    {
        try {
            // Verificar que el usuario existe
            if (!$this->userRepository->exists($id)) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ];
            }

            // Verificar si se puede eliminar (restricciones de negocio)
            $canDelete = $this->canDeleteUser($id);
            if (!$canDelete['can_delete']) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar el usuario',
                    'restrictions' => $canDelete['restrictions']
                ];
            }

            $success = $this->userRepository->delete($id);
            
            if (!$success) {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar usuario'
                ];
            }

            return [
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Busca usuarios según criterios
     */
    public function searchUsers($searchData)
    {
        try {
            $searchDto = UserSearchDto::fromArray($searchData);
            
            if (!$searchDto->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Criterios de búsqueda inválidos',
                    'errors' => $searchDto->getErrors()
                ];
            }

            $criteria = $searchDto->getCriteria();
            $orderBy = $searchDto->getOrderBy();
            $pagination = $searchDto->getPaginationParams();

            $entities = $this->userRepository->search(
                $criteria,
                $orderBy,
                $pagination['limit'],
                $pagination['offset']
            );

            $users = [];
            foreach ($entities as $entity) {
                $users[] = UserDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Búsqueda completada correctamente',
                'data' => $users,
                'pagination' => $pagination,
                'search_criteria' => $criteria
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Autentica un usuario
     */
    public function authenticateUser($email, $password)
    {
        try {
            $entity = $this->userRepository->findByEmail($email);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Credenciales inválidas',
                    'data' => null
                ];
            }

            // Verificar contraseña
            if (!$this->verifyPassword($password, $entity->password)) {
                return [
                    'success' => false,
                    'message' => 'Credenciales inválidas',
                    'data' => null
                ];
            }

            $userDto = UserDto::fromEntity($entity);

            return [
                'success' => true,
                'message' => 'Autenticación exitosa',
                'data' => $userDto
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en la autenticación: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Obtiene estadísticas de usuarios
     */
    public function getUserStatistics()
    {
        try {
            $totalUsers = $this->userRepository->getTotalCount();
            $statsByRole = $this->userRepository->getStatsByRole();

            $stats = [
                'total_usuarios' => $totalUsers,
                'por_rol' => $statsByRole,
                'porcentajes' => []
            ];

            // Calcular porcentajes
            foreach ($statsByRole as $rolStat) {
                $percentage = $totalUsers > 0 ? round(($rolStat['count'] / $totalUsers) * 100, 1) : 0;
                $stats['porcentajes'][$rolStat['rol']] = $percentage;
            }

            return [
                'success' => true,
                'message' => 'Estadísticas obtenidas correctamente',
                'data' => $stats
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene el perfil completo del usuario con información adicional
     */
    public function getUserProfile($id)
    {
        try {
            $entity = $this->userRepository->findById($id);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'data' => null
                ];
            }

            $profileDto = new UserProfileDto($entity->toArray());
            
            // Enriquecer con información adicional
            $this->enrichUserProfile($profileDto);

            return [
                'success' => true,
                'message' => 'Perfil obtenido correctamente',
                'data' => $profileDto
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener perfil: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Enriquece el perfil del usuario con información adicional
     */
    private function enrichUserProfile(UserProfileDto $profileDto)
    {
        try {
            // Obtener pacientes asignados
            $pacientesAsignados = $this->userRepository->getPacientesAsignados($profileDto->id, 10, 0);
            $profileDto->setPacientesAsignados($pacientesAsignados);

            // Obtener asignaciones recientes
            $asignacionesRecientes = $this->userRepository->getAsignaciones($profileDto->id, 5, 0);
            $profileDto->setAsignacionesRecientes($asignacionesRecientes);

            // Estadísticas de actividad
            $estadisticas = [
                'total_pacientes' => count($pacientesAsignados),
                'pacientes_activos' => count($pacientesAsignados), // Simplificado
                'historiales_creados' => 0, // TODO: Implementar cuando esté disponible
                'ultima_actividad' => null,
                'promedio_pacientes_mes' => round(count($pacientesAsignados) / max(1, $this->getMonthsSinceRegistration($profileDto->created_at)), 1)
            ];
            
            $profileDto->setEstadisticasActividad($estadisticas);

        } catch (Exception $e) {
            // En caso de error, continuar con perfil básico
            error_log("Error enriching user profile: " . $e->getMessage());
        }
    }

    /**
     * Valida datos de actualización
     */
    private function validateUpdateData($data, $excludeId = null)
    {
        $errors = [];

        // Validar nombre si se proporciona
        if (isset($data['name'])) {
            if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
                $errors['name'] = 'El nombre debe tener al menos 2 caracteres';
            }
        }

        // Validar email si se proporciona
        if (isset($data['email'])) {
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Email no válido';
            } elseif ($this->userRepository->emailExists($data['email'], $excludeId)) {
                $errors['email'] = 'Este email ya está en uso';
            }
        }

        // Validar contraseña si se proporciona
        if (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
            }
        }

        // Validar rol si se proporciona
        if (isset($data['rol']) && !$this->isValidRole($data['rol'])) {
            $errors['rol'] = 'Rol no válido';
        }

        return $errors;
    }

    /**
     * Verifica si un usuario se puede eliminar
     */
    private function canDeleteUser($id)
    {
        $restrictions = [];

        // Verificar si tiene pacientes asignados
        $pacientesAsignados = $this->userRepository->getPacientesAsignados($id, 1, 0);
        if (!empty($pacientesAsignados)) {
            $restrictions[] = 'Tiene pacientes asignados';
        }

        // Verificar si es el último admin (si es admin)
        $user = $this->userRepository->findById($id);
        if ($user && $user->rol === 'Administrador') {
            $adminCount = $this->userRepository->countByRole('Administrador');
            if ($adminCount <= 1) {
                $restrictions[] = 'Es el único administrador del sistema';
            }
        }

        return [
            'can_delete' => empty($restrictions),
            'restrictions' => $restrictions
        ];
    }

    /**
     * Verifica si un rol es válido
     */
    private function isValidRole($rol)
    {
        $validRoles = $this->userRepository->getAvailableRoles();
        return in_array($rol, $validRoles);
    }

    /**
     * Verifica una contraseña
     */
    private function verifyPassword($password, $hashedPassword)
    {
        // Si la contraseña está hasheada, usar password_verify
        if (password_get_info($hashedPassword)['algo']) {
            return password_verify($password, $hashedPassword);
        }
        
        // Para compatibilidad con contraseñas en texto plano (legacy)
        return $password === $hashedPassword;
    }

    /**
     * Calcula meses desde el registro
     */
    private function getMonthsSinceRegistration($createdAt)
    {
        if (empty($createdAt)) {
            return 1;
        }

        $created = new DateTime($createdAt);
        $now = new DateTime();
        $diff = $now->diff($created);
        
        return max(1, ($diff->y * 12) + $diff->m);
    }

    /**
     * Obtiene roles disponibles
     */
    public function getAvailableRoles()
    {
        return [
            'success' => true,
            'message' => 'Roles obtenidos correctamente',
            'data' => CreateUserDto::getAvailableRoles()
        ];
    }

    /**
     * Cambia la contraseña del usuario
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            $entity = $this->userRepository->findById($userId);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ];
            }

            // Verificar contraseña actual
            if (!$this->verifyPassword($currentPassword, $entity->password)) {
                return [
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ];
            }

            // Validar nueva contraseña
            if (strlen($newPassword) < 6) {
                return [
                    'success' => false,
                    'message' => 'La nueva contraseña debe tener al menos 6 caracteres'
                ];
            }

            // Actualizar contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updated = $this->userRepository->update($userId, ['password' => $hashedPassword]);
            
            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar la contraseña'
                ];
            }

            return [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cambiar contraseña: ' . $e->getMessage()
            ];
        }
    }

    /** Cambia la contraseña de un usuario por un administrador */
    public function adminChangeUserPassword($userId, $newPassword)
    {
        try {
            $entity = $this->userRepository->findById($userId);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ];
            }

            // Validar nueva contraseña
            if (strlen($newPassword) < 6) {
                return [
                    'success' => false,
                    'message' => 'La nueva contraseña debe tener al menos 6 caracteres'
                ];
            }

            // Actualizar contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updated = $this->userRepository->update($userId, ['password' => $hashedPassword]);
            
            if (!$updated) {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar la contraseña'
                ];
            }

            return [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente por el administrador'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cambiar contraseña por administrador: ' . $e->getMessage()
            ];
        }
    }
}

?>