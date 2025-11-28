<?php

/**
 * DTO base para Pacientes
 * Representa un paciente con información básica y formateo
 */
class PacienteDto
{
    public $idpaciente;
    public $rutpaciente;
    public $rutpaciente_formatted;
    public $nompaciente;
    public $edadpaciente;
    public $edadpaciente_numeric;
    public $telpaciente;
    public $dirpaciente;
    public $cuidador_name;
    public $cuidador_email;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data)
    {
        $this->idpaciente = $data['idpaciente'] ?? null;
        $this->rutpaciente = $data['rutpaciente'] ?? null;
        $this->nompaciente = $data['nompaciente'] ?? null;
        $this->edadpaciente = $data['edadpaciente'] ?? null;
        $this->telpaciente = $data['telpaciente'] ?? null;
        $this->dirpaciente = $data['dirpaciente'] ?? null;
        $this->cuidador_name = $data['cuidador_name'] ?? null;
        $this->cuidador_email = $data['cuidador_email'] ?? null;
        
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
            'cuidador_name' => $this->cuidador_name,
            'cuidador_email' => $this->cuidador_email
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