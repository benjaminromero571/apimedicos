<?php

require_once __DIR__ . '/ProfesionalDto.php';

/**
 * DTO detallado para profesionales
 * Incluye información extendida y estadísticas
 */
class ProfesionalDetailDto extends ProfesionalDto
{
    public $estadisticas;
    public $informacion_contacto;
    public $informacion_profesional;
    public $alertas;

    public function __construct($data = [])
    {
        parent::__construct($data);
        
        // Información extendida
        $this->estadisticas = $this->generarEstadisticas($data);
        $this->informacion_contacto = $this->generarInfoContacto();
        $this->informacion_profesional = $this->generarInfoProfesional();
        $this->alertas = $this->identificarAlertas();
    }

    /**
     * Genera estadísticas básicas del profesional
     */
    protected function generarEstadisticas($data = [])
    {
        return [
            'id' => $this->iddoctor,
            'nombre' => $this->nomdoctor,
            'especialidad' => $this->espedoctor,
            'cedula' => $this->cedoctor,
            'telefono_formateado' => $this->telefono_formateado,
            'tiene_telefono' => !empty($this->teldoctor),
            'telefono_tipo' => $this->isTelefonoMovil() ? 'móvil' : 'fijo',
            // Estas estadísticas podrían expandirse con datos reales de asignaciones/citas
            'total_pacientes_asignados' => $data['total_pacientes'] ?? 0,
            'total_citas_programadas' => $data['total_citas'] ?? 0,
            'estado_activo' => true // Por defecto activo
        ];
    }

    /**
     * Genera información de contacto estructurada
     */
    protected function generarInfoContacto()
    {
        $info = [
            'telefono_principal' => $this->telefono_formateado,
            'telefono_original' => $this->teldoctor,
            'tiene_contacto' => !empty($this->teldoctor)
        ];

        if (!empty($this->teldoctor)) {
            $telefonoLimpio = preg_replace('/[^0-9]/', '', $this->teldoctor);
            
            $info['es_movil'] = $this->isTelefonoMovil();
            $info['longitud_telefono'] = strlen($telefonoLimpio);
            
            // Clasificar tipo de teléfono
            if (strlen($telefonoLimpio) == 9 && substr($telefonoLimpio, 0, 1) == '9') {
                $info['tipo'] = 'Móvil chileno';
                $info['operador_posible'] = $this->detectarOperadorMovil($telefonoLimpio);
            } elseif (strlen($telefonoLimpio) == 8) {
                $info['tipo'] = 'Teléfono fijo';
                $info['region_posible'] = $this->detectarRegionFijo($telefonoLimpio);
            } else {
                $info['tipo'] = 'Formato no estándar';
            }
        }

        return $info;
    }

    /**
     * Genera información profesional estructurada
     */
    protected function generarInfoProfesional()
    {
        $info = [
            'nombre_completo' => $this->nombre_completo,
            'primer_nombre' => $this->getPrimerNombre(),
            'iniciales' => $this->getIniciales(),
            'cedula_profesional' => $this->cedoctor,
            'especialidad_principal' => $this->espedoctor,
            'area_medica' => $this->clasificarAreaMedica(),
            'nivel_especialidad' => $this->clasificarNivelEspecialidad()
        ];

        // Verificar completitud de datos
        $info['perfil_completo'] = $this->verificarPerfilCompleto();
        $info['campos_faltantes'] = $this->obtenerCamposFaltantes();

        return $info;
    }

    /**
     * Identifica alertas o información importante
     */
    protected function identificarAlertas()
    {
        $alertas = [];

        // Alertas por datos faltantes
        if (empty($this->teldoctor)) {
            $alertas[] = [
                'tipo' => 'info',
                'mensaje' => 'Sin teléfono de contacto registrado'
            ];
        }

        if (empty($this->cedoctor)) {
            $alertas[] = [
                'tipo' => 'atencion',
                'mensaje' => 'Sin cédula profesional registrada'
            ];
        }

        // Alertas por formato de datos
        if (!empty($this->teldoctor)) {
            $telefonoLimpio = preg_replace('/[^0-9]/', '', $this->teldoctor);
            if (strlen($telefonoLimpio) < 8 || strlen($telefonoLimpio) > 11) {
                $alertas[] = [
                    'tipo' => 'atencion',
                    'mensaje' => 'Formato de teléfono puede ser incorrecto'
                ];
            }
        }

        // Alertas por especialidad
        if (!$this->esEspecialidadReconocida()) {
            $alertas[] = [
                'tipo' => 'info',
                'mensaje' => 'Especialidad no estándar registrada'
            ];
        }

        return $alertas;
    }

    /**
     * Detecta posible operador móvil (básico)
     */
    protected function detectarOperadorMovil($telefono)
    {
        if (strlen($telefono) != 9) return 'Desconocido';
        
        $codigo = substr($telefono, 1, 1);
        
        switch ($codigo) {
            case '6':
            case '7':
            case '8':
                return 'Movistar (posible)';
            case '9':
                return 'Claro (posible)';
            case '5':
                return 'Entel (posible)';
            default:
                return 'Operador no identificado';
        }
    }

    /**
     * Detecta posible región para teléfono fijo (básico)
     */
    protected function detectarRegionFijo($telefono)
    {
        if (strlen($telefono) != 8) return 'No identificada';
        
        $codigo = substr($telefono, 0, 1);
        
        switch ($codigo) {
            case '2':
                return 'Región Metropolitana (posible)';
            case '3':
                return 'Regiones del Norte (posible)';
            case '4':
                return 'Regiones del Centro (posible)';
            case '6':
                return 'Regiones del Sur (posible)';
            default:
                return 'Región no identificada';
        }
    }

    /**
     * Clasifica el área médica por especialidad
     */
    protected function clasificarAreaMedica()
    {
        if (empty($this->espedoctor)) {
            return 'No especificada';
        }

        $especialidad = strtolower($this->espedoctor);
        
        // Medicina Interna
        $medicinaInterna = ['cardiología', 'gastroenterología', 'endocrinología', 'reumatología', 'nefrología'];
        foreach ($medicinaInterna as $area) {
            if (strpos($especialidad, $area) !== false) {
                return 'Medicina Interna';
            }
        }
        
        // Cirugía
        if (strpos($especialidad, 'cirugía') !== false || strpos($especialidad, 'cirujano') !== false) {
            return 'Cirugía';
        }
        
        // Pediatría
        if (strpos($especialidad, 'pediatr') !== false || strpos($especialidad, 'niños') !== false) {
            return 'Pediatría';
        }
        
        // Ginecología/Obstetricia
        if (strpos($especialidad, 'ginecol') !== false || strpos($especialidad, 'obstetr') !== false) {
            return 'Ginecología y Obstetricia';
        }
        
        // Medicina General
        if (strpos($especialidad, 'general') !== false || strpos($especialidad, 'familiar') !== false) {
            return 'Medicina General';
        }
        
        return 'Especialidad Médica';
    }

    /**
     * Clasifica el nivel de especialidad
     */
    protected function clasificarNivelEspecialidad()
    {
        if (empty($this->espedoctor)) {
            return 'No especificado';
        }

        $especialidad = strtolower($this->espedoctor);
        
        if (strpos($especialidad, 'general') !== false || strpos($especialidad, 'familiar') !== false) {
            return 'Atención Primaria';
        }
        
        if (strpos($especialidad, 'cirugía') !== false) {
            return 'Especialidad Quirúrgica';
        }
        
        return 'Especialidad Médica';
    }

    /**
     * Verifica si el perfil está completo
     */
    protected function verificarPerfilCompleto()
    {
        return !empty($this->nomdoctor) && 
               !empty($this->cedoctor) && 
               !empty($this->espedoctor) && 
               !empty($this->teldoctor);
    }

    /**
     * Obtiene lista de campos faltantes
     */
    protected function obtenerCamposFaltantes()
    {
        $faltantes = [];
        
        if (empty($this->nomdoctor)) $faltantes[] = 'Nombre';
        if (empty($this->cedoctor)) $faltantes[] = 'Cédula profesional';
        if (empty($this->espedoctor)) $faltantes[] = 'Especialidad';
        if (empty($this->teldoctor)) $faltantes[] = 'Teléfono';
        
        return $faltantes;
    }

    /**
     * Verifica si la especialidad es reconocida
     */
    protected function esEspecialidadReconocida()
    {
        $especialidadesStandard = [
            'medicina general', 'medicina familiar', 'cardiología', 'neurología',
            'traumatología', 'ginecología', 'obstetricia', 'pediatría', 'psiquiatría',
            'dermatología', 'oftalmología', 'otorrinolaringología', 'urología',
            'gastroenterología', 'endocrinología', 'oncología', 'radiología'
        ];
        
        return in_array(strtolower($this->espedoctor), $especialidadesStandard);
    }

    /**
     * Convierte a array con información extendida
     */
    public function toArray()
    {
        $base = parent::toArray();
        
        return array_merge($base, [
            'estadisticas' => $this->estadisticas,
            'informacion_contacto' => $this->informacion_contacto,
            'informacion_profesional' => $this->informacion_profesional,
            'alertas' => $this->alertas,
            'resumen_profesional' => $this->generarResumenProfesional()
        ]);
    }

    /**
     * Genera un resumen profesional
     */
    protected function generarResumenProfesional()
    {
        $resumen = [];
        
        if (!empty($this->nomdoctor)) {
            $resumen[] = "Dr./Dra. {$this->nomdoctor}";
        }
        
        if (!empty($this->espedoctor)) {
            $resumen[] = "Especialista en {$this->espedoctor}";
        }
        
        if (!empty($this->informacion_profesional['area_medica'])) {
            $resumen[] = "Área: {$this->informacion_profesional['area_medica']}";
        }
        
        if ($this->informacion_contacto['tiene_contacto']) {
            $resumen[] = "Contacto disponible";
        }
        
        return implode(' | ', $resumen);
    }
}

?>