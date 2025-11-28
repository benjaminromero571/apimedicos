<?php

/**
 * DTO para crear nuevos pacientes
 * Contiene solo los datos necesarios para crear un paciente y validación básica
 */
class CreatePacienteDto
{
    public $rutpaciente;
    public $nompaciente;
    public $edadpaciente;
    public $telpaciente;
    public $dirpaciente;
    public $id_cuidador;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data)
    {
        $this->rutpaciente = $data['rutpaciente'] ?? null;
        $this->nompaciente = $data['nompaciente'] ?? null;
        $this->edadpaciente = $data['edadpaciente'] ?? null;
        $this->telpaciente = $data['telpaciente'] ?? null;
        $this->dirpaciente = $data['dirpaciente'] ?? null;
        $this->id_cuidador = !empty($data['id_cuidador']) ? $data['id_cuidador'] : null;
    }

    public function toArray()
    {
        return [
            'rutpaciente' => $this->rutpaciente,
            'nompaciente' => $this->nompaciente,
            'edadpaciente' => $this->edadpaciente,
            'telpaciente' => $this->telpaciente,
            'dirpaciente' => $this->dirpaciente,
            'id_cuidador' => $this->id_cuidador
        ];
    }

    /**
     * Valida que los datos requeridos estén presentes
     */
    public function isValid()
    {
        return !empty($this->rutpaciente) && 
               !empty($this->nompaciente) && 
               !empty($this->edadpaciente) && 
               !empty($this->telpaciente) && 
               !empty($this->dirpaciente);
    }

    /**
     * Obtiene los errores de validación básica
     */
    public function getValidationErrors()
    {
        $errors = [];

        if (empty($this->rutpaciente)) {
            $errors[] = 'rutpaciente es requerido';
        }

        if (empty($this->nompaciente)) {
            $errors[] = 'nompaciente es requerido';
        }

        if (empty($this->edadpaciente)) {
            $errors[] = 'edadpaciente es requerida';
        }

        if (empty($this->telpaciente)) {
            $errors[] = 'telpaciente es requerido';
        }

        if (empty($this->dirpaciente)) {
            $errors[] = 'dirpaciente es requerida';
        }

        return $errors;
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }
}

?>