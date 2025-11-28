<?php

/**
 * DTO para crear nuevas asignaciones
 * Contiene solo los datos necesarios para crear una asignación
 */
class CreateAsignacionDto
{
    public $user_id;
    public $paciente_id;
    public $fecha_asignacion;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data)
    {
        $this->user_id = $data['user_id'] ?? null;
        $this->paciente_id = $data['paciente_id'] ?? null;
        $this->fecha_asignacion = $data['fecha_asignacion'] ?? date('Y-m-d H:i:s');
    }

    public function toArray()
    {
        return [
            'user_id' => $this->user_id,
            'paciente_id' => $this->paciente_id,
            'fecha_asignacion' => $this->fecha_asignacion
        ];
    }

    /**
     * Valida que los datos requeridos estén presentes
     */
    public function isValid()
    {
        return !empty($this->user_id) && !empty($this->paciente_id);
    }

    /**
     * Obtiene los errores de validación básica
     */
    public function getValidationErrors()
    {
        $errors = [];

        if (empty($this->user_id)) {
            $errors[] = 'user_id es requerido';
        }

        if (empty($this->paciente_id)) {
            $errors[] = 'paciente_id es requerido';
        }

        return $errors;
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }
}

?>