<?php

/**
 * DTO para asignaciones con información detallada
 * Incluye datos del usuario y del paciente en la misma estructura
 */
class AsignacionDetailDto
{
    public $id;
    public $user_id;
    public $paciente_id;
    public $fecha_asignacion;
    public $fecha_asignacion_formatted;
    
    // Datos del usuario
    public $user_name;
    public $user_rol;
    
    // Datos del paciente
    public $nompaciente;
    public $rutpaciente;
    public $edadpaciente;
    public $telpaciente;
    public $dirpaciente;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data)
    {
        // Datos de la asignación
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->paciente_id = $data['paciente_id'] ?? null;
        $this->fecha_asignacion = $data['fecha_asignacion'] ?? null;
        
        // Datos del usuario
        $this->user_name = $data['user_name'] ?? null;
        $this->user_rol = $data['user_rol'] ?? null;
        
        // Datos del paciente
        $this->nompaciente = $data['nompaciente'] ?? null;
        $this->rutpaciente = $data['rutpaciente'] ?? null;
        $this->edadpaciente = $data['edadpaciente'] ?? null;
        $this->telpaciente = $data['telpaciente'] ?? null;
        $this->dirpaciente = $data['dirpaciente'] ?? null;
        
        // Formatear fecha
        if ($this->fecha_asignacion) {
            $this->fecha_asignacion_formatted = $this->formatDate($this->fecha_asignacion);
        }
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'paciente_id' => $this->paciente_id,
            'fecha_asignacion' => $this->fecha_asignacion,
            'fecha_asignacion_formatted' => $this->fecha_asignacion_formatted,
            'usuario' => [
                'id' => $this->user_id,
                'name' => $this->user_name,
                'rol' => $this->user_rol
            ],
            'paciente' => [
                'id' => $this->paciente_id,
                'nombre' => $this->nompaciente,
                'rut' => $this->rutpaciente,
                'edad' => $this->edadpaciente,
                'telefono' => $this->telpaciente,
                'direccion' => $this->dirpaciente
            ]
        ];
    }

    /**
     * Convierte a array simplificado (sin anidación)
     */
    public function toSimpleArray()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
            'user_rol' => $this->user_rol,
            'paciente_id' => $this->paciente_id,
            'nompaciente' => $this->nompaciente,
            'rutpaciente' => $this->rutpaciente,
            'edadpaciente' => $this->edadpaciente,
            'telpaciente' => $this->telpaciente,
            'dirpaciente' => $this->dirpaciente,
            'fecha_asignacion' => $this->fecha_asignacion,
            'fecha_asignacion_formatted' => $this->fecha_asignacion_formatted
        ];
    }

    private function formatDate($date)
    {
        if (empty($date)) {
            return '';
        }

        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('d/m/Y H:i', $timestamp);
        }

        return $date;
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }
}

?>