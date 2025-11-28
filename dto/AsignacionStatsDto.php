<?php

/**
 * DTO para estadísticas de asignaciones
 * Contiene información agregada y métricas del sistema
 */
class AsignacionStatsDto
{
    public $total_asignaciones;
    public $usuarios_con_asignaciones;
    public $pacientes_asignados;
    public $fecha_generacion;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data)
    {
        $this->total_asignaciones = (int)($data['total_asignaciones'] ?? 0);
        $this->usuarios_con_asignaciones = (int)($data['usuarios_con_asignaciones'] ?? 0);
        $this->pacientes_asignados = (int)($data['pacientes_asignados'] ?? 0);
        $this->fecha_generacion = $data['fecha_generacion'] ?? date('Y-m-d H:i:s');
    }

    public function toArray()
    {
        return [
            'total_asignaciones' => $this->total_asignaciones,
            'usuarios_con_asignaciones' => $this->usuarios_con_asignaciones,
            'pacientes_asignados' => $this->pacientes_asignados,
            'fecha_generacion' => $this->fecha_generacion,
            'promedios' => [
                'asignaciones_por_usuario' => $this->getAsignacionesPorUsuario(),
                'usuarios_por_paciente' => $this->getUsuariosPorPaciente()
            ]
        ];
    }

    /**
     * Calcula el promedio de asignaciones por usuario
     */
    public function getAsignacionesPorUsuario()
    {
        if ($this->usuarios_con_asignaciones == 0) {
            return 0;
        }

        return round($this->total_asignaciones / $this->usuarios_con_asignaciones, 2);
    }

    /**
     * Calcula el promedio de usuarios por paciente
     */
    public function getUsuariosPorPaciente()
    {
        if ($this->pacientes_asignados == 0) {
            return 0;
        }

        return round($this->total_asignaciones / $this->pacientes_asignados, 2);
    }

    /**
     * Verifica si hay datos significativos
     */
    public function hasData()
    {
        return $this->total_asignaciones > 0;
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }
}

?>