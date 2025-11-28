<?php

/**
 * DTO para estadísticas de pacientes
 * Contiene información agregada y métricas demográficas
 */
class PacienteStatsDto
{
    public $total_pacientes;
    public $edad_promedio;
    public $edad_minima;
    public $edad_maxima;
    public $menores_edad;
    public $adultos_mayores;
    public $con_cuidador;
    public $sin_cuidador;
    public $con_profesionales;
    public $total_profesionales_asignados;
    public $fecha_generacion;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data)
    {
        $this->total_pacientes = (int)($data['total_pacientes'] ?? 0);
        $this->edad_promedio = round((float)($data['edad_promedio'] ?? 0), 1);
        $this->edad_minima = (int)($data['edad_minima'] ?? 0);
        $this->edad_maxima = (int)($data['edad_maxima'] ?? 0);
        $this->menores_edad = (int)($data['menores_edad'] ?? 0);
        $this->adultos_mayores = (int)($data['adultos_mayores'] ?? 0);
        $this->con_cuidador = (int)($data['con_cuidador'] ?? 0);
        $this->sin_cuidador = (int)($data['sin_cuidador'] ?? 0);
        $this->con_profesionales = (int)($data['con_profesionales'] ?? 0);
        $this->total_profesionales_asignados = (int)($data['total_profesionales_asignados'] ?? 0);
        $this->fecha_generacion = $data['fecha_generacion'] ?? date('Y-m-d H:i:s');
    }

    public function toArray()
    {
        return [
            'total_pacientes' => $this->total_pacientes,
            'estadisticas_edad' => [
                'promedio' => $this->edad_promedio,
                'minima' => $this->edad_minima,
                'maxima' => $this->edad_maxima,
                'rango' => $this->getRangoEdad()
            ],
            'grupos_etarios' => [
                'menores_edad' => $this->menores_edad,
                'adultos' => $this->getAdultos(),
                'adultos_mayores' => $this->adultos_mayores
            ],
            'asignaciones_cuidador' => [
                'con_cuidador' => $this->con_cuidador,
                'sin_cuidador' => $this->sin_cuidador,
                'porcentaje_con_cuidador' => $this->getPorcentajeConCuidador()
            ],
            'asignaciones_profesionales' => [
                'con_profesionales' => $this->con_profesionales,
                'sin_profesionales' => $this->getSinProfesionales(),
                'total_profesionales_asignados' => $this->total_profesionales_asignados,
                'promedio_profesionales_por_paciente' => $this->getPromedioProfesionales(),
                'porcentaje_con_profesionales' => $this->getPorcentajeConProfesionales()
            ],
            'porcentajes_demográficos' => [
                'menores_edad_pct' => $this->getPorcentajeMenoresEdad(),
                'adultos_pct' => $this->getPorcentajeAdultos(),
                'adultos_mayores_pct' => $this->getPorcentajeAdultosMayores()
            ],
            'fecha_generacion' => $this->fecha_generacion
        ];
    }

    /**
     * Calcula el rango de edad
     */
    public function getRangoEdad()
    {
        if ($this->edad_maxima > 0 && $this->edad_minima >= 0) {
            return $this->edad_maxima - $this->edad_minima;
        }
        return 0;
    }

    /**
     * Calcula adultos (18-64 años)
     */
    public function getAdultos()
    {
        return $this->total_pacientes - $this->menores_edad - $this->adultos_mayores;
    }

    /**
     * Calcula porcentaje de menores de edad
     */
    public function getPorcentajeMenoresEdad()
    {
        if ($this->total_pacientes == 0) {
            return 0;
        }
        return round(($this->menores_edad / $this->total_pacientes) * 100, 1);
    }

    /**
     * Calcula porcentaje de adultos
     */
    public function getPorcentajeAdultos()
    {
        if ($this->total_pacientes == 0) {
            return 0;
        }
        return round(($this->getAdultos() / $this->total_pacientes) * 100, 1);
    }

    /**
     * Calcula porcentaje de adultos mayores
     */
    public function getPorcentajeAdultosMayores()
    {
        if ($this->total_pacientes == 0) {
            return 0;
        }
        return round(($this->adultos_mayores / $this->total_pacientes) * 100, 1);
    }

    /**
     * Calcula porcentaje de pacientes con cuidador
     */
    public function getPorcentajeConCuidador()
    {
        if ($this->total_pacientes == 0) {
            return 0;
        }
        return round(($this->con_cuidador / $this->total_pacientes) * 100, 1);
    }

    /**
     * Calcula pacientes sin profesionales asignados
     */
    public function getSinProfesionales()
    {
        return $this->total_pacientes - $this->con_profesionales;
    }

    /**
     * Calcula promedio de profesionales por paciente que tiene profesionales asignados
     */
    public function getPromedioProfesionales()
    {
        if ($this->con_profesionales == 0) {
            return 0;
        }
        return round($this->total_profesionales_asignados / $this->con_profesionales, 1);
    }

    /**
     * Calcula porcentaje de pacientes con profesionales asignados
     */
    public function getPorcentajeConProfesionales()
    {
        if ($this->total_pacientes == 0) {
            return 0;
        }
        return round(($this->con_profesionales / $this->total_pacientes) * 100, 1);
    }

    /**
     * Verifica si hay datos significativos
     */
    public function hasData()
    {
        return $this->total_pacientes > 0;
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }
}

?>