<?php

/**
 * DTO base para todas las asignaciones
 * Representa una asignación con información básica
 */
class AsignacionDto
{
    public $id;
    public $user_id;
    public $paciente_id;
    public $fecha_asignacion;
    public $fecha_asignacion_formatted;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->user_id = $data['user_id'] ?? null;
        $this->paciente_id = $data['paciente_id'] ?? null;
        $this->fecha_asignacion = $data['fecha_asignacion'] ?? null;
        
        // Formatear fecha si existe
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