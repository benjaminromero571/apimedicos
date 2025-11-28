<?php

/**
 * DTO para pacientes con información detallada
 * Incluye historial médico y asignaciones en la misma estructura
 */
class PacienteDetailDto
{
    public $idpaciente;
    public $rutpaciente;
    public $rutpaciente_formatted;
    public $nompaciente;
    public $edadpaciente;
    public $edadpaciente_numeric;
    public $telpaciente;
    public $dirpaciente;
    public $id_cuidador;
    public $cuidador_name;
    public $cuidador_email;
    
    // Información adicional
    public $historial;
    public $asignaciones;
    public $profesionales;
    public $total_historiales;
    public $total_asignaciones;
    public $total_profesionales;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data)
    {
        // Datos básicos del paciente
        $this->idpaciente = $data['idpaciente'] ?? null;
        $this->rutpaciente = $data['rutpaciente'] ?? null;
        $this->nompaciente = $data['nompaciente'] ?? null;
        $this->edadpaciente = $data['edadpaciente'] ?? null;
        $this->telpaciente = $data['telpaciente'] ?? null;
        $this->dirpaciente = $data['dirpaciente'] ?? null;
        $this->id_cuidador = $data['id_cuidador'] ?? null;
        $this->cuidador_name = $data['cuidador_name'] ?? null;
        $this->cuidador_email = $data['cuidador_email'] ?? null;
        
        // Información adicional
        $this->historial = $data['historial'] ?? [];
        $this->asignaciones = $data['asignaciones'] ?? [];
        $this->profesionales = $data['profesionales'] ?? [];
        $this->total_historiales = $data['total_historiales'] ?? 0;
        $this->total_asignaciones = $data['total_asignaciones'] ?? 0;
        $this->total_profesionales = $data['total_profesionales'] ?? 0;
        
        // Formatear RUT si existe
        if ($this->rutpaciente) {
            $this->rutpaciente_formatted = $this->formatRut($this->rutpaciente);
        }
        
        // Edad como número
        if ($this->edadpaciente) {
            $this->edadpaciente_numeric = (int) $this->edadpaciente;
        }
    }

    public function toArray()
    {
        return [
            'idpaciente' => $this->idpaciente,
            'rutpaciente' => $this->rutpaciente,
            'rutpaciente_formatted' => $this->rutpaciente_formatted,
            'nompaciente' => $this->nompaciente,
            'edadpaciente' => $this->edadpaciente,
            'edadpaciente_numeric' => $this->edadpaciente_numeric,
            'telpaciente' => $this->telpaciente,
            'dirpaciente' => $this->dirpaciente,
            'id_cuidador' => $this->id_cuidador,
            'cuidador_name' => $this->cuidador_name,
            'cuidador_email' => $this->cuidador_email,
            'historial' => $this->historial,
            'asignaciones' => $this->asignaciones,
            'profesionales' => $this->profesionales,
            'resumen' => [
                'total_historiales' => $this->total_historiales,
                'total_asignaciones' => $this->total_asignaciones,
                'total_profesionales' => $this->total_profesionales,
                'tiene_historial' => $this->total_historiales > 0,
                'tiene_asignaciones' => $this->total_asignaciones > 0,
                'tiene_profesionales' => $this->total_profesionales > 0,
                'tiene_cuidador' => !empty($this->id_cuidador)
            ]
        ];
    }

    /**
     * Convierte a array simplificado (sin información adicional)
     */
    public function toSimpleArray()
    {
        return [
            'idpaciente' => $this->idpaciente,
            'rutpaciente' => $this->rutpaciente,
            'rutpaciente_formatted' => $this->rutpaciente_formatted,
            'nompaciente' => $this->nompaciente,
            'edadpaciente' => $this->edadpaciente,
            'edadpaciente_numeric' => $this->edadpaciente_numeric,
            'telpaciente' => $this->telpaciente,
            'dirpaciente' => $this->dirpaciente,
            'id_cuidador' => $this->id_cuidador,
            'cuidador_name' => $this->cuidador_name,
            'total_historiales' => $this->total_historiales,
            'total_asignaciones' => $this->total_asignaciones,
            'total_profesionales' => $this->total_profesionales
        ];
    }

    /**
     * Formatea el RUT para mostrar
     */
    private function formatRut($rut)
    {
        if (empty($rut)) {
            return '';
        }

        $rut = str_replace(['.', '-'], '', $rut);
        if (strlen($rut) < 2) {
            return $rut;
        }

        $dv = substr($rut, -1);
        $numero = substr($rut, 0, -1);
        
        return number_format($numero, 0, '', '.') . '-' . $dv;
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }
}

?>