<?php

/**
 * UserProfileDto
 * 
 * DTO para el perfil completo del usuario con información adicional
 * Incluye estadísticas, asignaciones y detalles del perfil
 */
class UserProfileDto
{
    public $id;
    public $name;
    public $email;
    public $rol;
    public $rol_formateado;
    public $iniciales;
    public $created_at;
    public $updated_at;
    
    // Estadísticas y datos adicionales
    public $pacientes_asignados_count;
    public $asignaciones_activas;
    public $ultimo_acceso;
    public $perfil_completo;
    
    // Arrays de datos relacionados
    public $pacientes_asignados;
    public $asignaciones_recientes;
    public $estadisticas_actividad;

    /**
     * Constructor
     */
    public function __construct($data = null)
    {
        // Inicializar arrays
        $this->pacientes_asignados = [];
        $this->asignaciones_recientes = [];
        $this->estadisticas_actividad = [];
        
        if ($data) {
            $this->fill($data);
        }
    }

    /**
     * Llena el DTO con datos del usuario
     */
    public function fill($userData)
    {
        if (is_array($userData)) {
            $this->id = $userData['id'] ?? null;
            $this->name = $userData['name'] ?? null;
            $this->email = $userData['email'] ?? null;
            $this->rol = $userData['rol'] ?? null;
            $this->created_at = $userData['created_at'] ?? null;
            $this->updated_at = $userData['updated_at'] ?? null;
            
        } elseif ($userData instanceof UserEntity) {
            $this->id = $userData->id;
            $this->name = $userData->name;
            $this->email = $userData->email;
            $this->rol = $userData->rol;
            $this->created_at = $userData->created_at;
            $this->updated_at = $userData->updated_at;
        }

        // Generar campos computados
        $this->rol_formateado = $this->getRolFormateado();
        $this->iniciales = $this->getIniciales();
        $this->perfil_completo = $this->evaluarPerfilCompleto();
    }

    /**
     * Agrega información de pacientes asignados
     */
    public function setPacientesAsignados($pacientes)
    {
        $this->pacientes_asignados = $pacientes;
        $this->pacientes_asignados_count = count($pacientes);
    }

    /**
     * Agrega información de asignaciones recientes
     */
    public function setAsignacionesRecientes($asignaciones)
    {
        $this->asignaciones_recientes = array_slice($asignaciones, 0, 5); // Últimas 5
        $this->asignaciones_activas = count($asignaciones);
    }

    /**
     * Establece estadísticas de actividad
     */
    public function setEstadisticasActividad($stats)
    {
        $this->estadisticas_actividad = [
            'total_pacientes' => $stats['total_pacientes'] ?? 0,
            'pacientes_activos' => $stats['pacientes_activos'] ?? 0,
            'historiales_creados' => $stats['historiales_creados'] ?? 0,
            'ultima_actividad' => $stats['ultima_actividad'] ?? null,
            'promedio_pacientes_mes' => $stats['promedio_pacientes_mes'] ?? 0
        ];
    }

    /**
     * Establece información del último acceso
     */
    public function setUltimoAcceso($ultimoAcceso)
    {
        $this->ultimo_acceso = $ultimoAcceso;
    }

    /**
     * Obtiene el rol formateado
     */
    private function getRolFormateado()
    {
        $roles = [
            'Administrador' => 'Administrador del Sistema',
            'Medico' => 'Médico',
            'Profesional' => 'Profesional de la Salud',
            'Cuidador' => 'Cuidador/Familiar'
        ];

        return $roles[$this->rol] ?? $this->rol;
    }

    /**
     * Obtiene las iniciales del usuario
     */
    private function getIniciales()
    {
        if (empty($this->name)) {
            return '';
        }

        $palabras = explode(' ', trim($this->name));
        $iniciales = '';

        foreach (array_slice($palabras, 0, 2) as $palabra) {
            if (!empty($palabra)) {
                $iniciales .= strtoupper(substr($palabra, 0, 1));
            }
        }

        return $iniciales ?: substr(strtoupper($this->name), 0, 2);
    }

    /**
     * Evalúa si el perfil está completo
     */
    private function evaluarPerfilCompleto()
    {
        $campos_requeridos = [
            'name' => !empty($this->name),
            'email' => !empty($this->email),
            'rol' => !empty($this->rol)
        ];

        $completitud = array_sum($campos_requeridos) / count($campos_requeridos) * 100;
        
        return [
            'porcentaje' => round($completitud, 0),
            'completo' => $completitud >= 100,
            'campos_faltantes' => array_keys(array_filter($campos_requeridos, function($v) { return !$v; }))
        ];
    }

    /**
     * Obtiene un resumen de la actividad del usuario
     */
    public function getResumenActividad()
    {
        $resumen = [];

        switch ($this->rol) {
            case 'Administrador':
                $resumen = [
                    'descripcion' => 'Administrador con acceso completo al sistema',
                    'responsabilidades' => [
                        'Gestión de usuarios',
                        'Configuración del sistema',
                        'Supervisión de actividades',
                        'Reportes y estadísticas'
                    ]
                ];
                break;
            
            case 'Medico':
                $resumen = [
                    'descripcion' => 'Médico responsable de la atención clínica',
                    'pacientes_asignados' => $this->pacientes_asignados_count ?? 0,
                    'responsabilidades' => [
                        'Diagnóstico y tratamiento de pacientes',
                        'Supervisión de profesionales de salud',
                        'Revisión de historiales clínicos',
                        'Comunicación con familiares y cuidadores'
                    ]
                ];
                break;

            case 'Profesional':
                $resumen = [
                    'descripcion' => 'Profesional de la salud',
                    'pacientes_asignados' => $this->pacientes_asignados_count ?? 0,
                    'responsabilidades' => [
                        'Atención médica de pacientes',
                        'Registro de historiales clínicos',
                        'Seguimiento de tratamientos',
                        'Comunicación con familiares'
                    ]
                ];
                break;

            case 'Cuidador':
                $resumen = [
                    'descripcion' => 'Cuidador o familiar responsable',
                    'pacientes_bajo_cuidado' => $this->pacientes_asignados_count ?? 0,
                    'responsabilidades' => [
                        'Cuidado diario de pacientes',
                        'Seguimiento de indicaciones médicas',
                        'Registro de observaciones',
                        'Comunicación con profesionales'
                    ]
                ];
                break;

            default:
                $resumen = [
                    'descripcion' => 'Usuario del sistema',
                    'responsabilidades' => []
                ];
        }

        return $resumen;
    }

    /**
     * Verifica permisos específicos según el rol
     */
    public function getPermisos()
    {
        $permisos_base = [
            'ver_perfil' => true,
            'editar_perfil' => true,
            'cambiar_password' => true
        ];

        $permisos_por_rol = [
            'Administrador' => [
                'gestionar_usuarios' => true,
                'ver_todos_pacientes' => true,
                'ver_estadisticas_sistema' => true,
                'configurar_sistema' => true,
                'generar_reportes' => true
            ],
            'Medico' => [
                'ver_pacientes_asignados' => true,
                'crear_historiales' => true,
                'editar_historiales' => true,
                'asignar_profesionales' => true,
                'comunicarse_familiares' => true
            ],
            'Profesional' => [
                'crear_pacientes' => true,
                'editar_pacientes_asignados' => true,
                'crear_historiales' => true,
                'ver_historiales_pacientes' => true,
                'asignar_cuidadores' => true
            ],
            'Cuidador' => [
                'ver_pacientes_asignados' => true,
                'registrar_observaciones' => true,
                'ver_historiales_limitados' => true,
                'actualizar_informacion_contacto' => true
            ]
        ];

        return array_merge($permisos_base, $permisos_por_rol[$this->rol] ?? []);
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     */
    public function tienePermiso($permiso)
    {
        $permisos = $this->getPermisos();
        return isset($permisos[$permiso]) && $permisos[$permiso];
    }

    /**
     * Convierte a array completo
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'rol' => $this->rol,
            'rol_formateado' => $this->rol_formateado,
            'iniciales' => $this->iniciales,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'ultimo_acceso' => $this->ultimo_acceso,
            'perfil_completo' => $this->perfil_completo,
            'estadisticas' => [
                'pacientes_asignados' => $this->pacientes_asignados_count,
                'asignaciones_activas' => $this->asignaciones_activas,
                'actividad' => $this->estadisticas_actividad
            ],
            'resumen_actividad' => $this->getResumenActividad(),
            'permisos' => $this->getPermisos(),
            'datos_relacionados' => [
                'pacientes_asignados' => $this->pacientes_asignados,
                'asignaciones_recientes' => $this->asignaciones_recientes
            ]
        ];
    }

    /**
     * Convierte a array resumido (sin datos relacionados pesados)
     */
    public function toArraySummary()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'rol' => $this->rol,
            'rol_formateado' => $this->rol_formateado,
            'iniciales' => $this->iniciales,
            'perfil_completo' => $this->perfil_completo,
            'estadisticas_basicas' => [
                'pacientes_asignados' => $this->pacientes_asignados_count,
                'asignaciones_activas' => $this->asignaciones_activas
            ],
            'ultimo_acceso' => $this->ultimo_acceso
        ];
    }

    /**
     * Crea un UserProfileDto desde un array
     */
    public static function fromArray(array $data)
    {
        return new self($data);
    }

    /**
     * Crea un UserProfileDto desde una UserEntity
     */
    public static function fromEntity(UserEntity $entity)
    {
        return new self($entity);
    }

    /**
     * Verifica si el perfil es válido
     */
    public function isValid()
    {
        return !empty($this->id) && 
               !empty($this->name) && 
               !empty($this->email) && 
               !empty($this->rol);
    }
}

?>