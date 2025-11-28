<?php

/**
 * DTO para crear nuevos historiales médicos
 * Contiene validaciones específicas para datos médicos
 */
class CreateHistorialDto
{
    public $idpaciente;
    public $fechahistorial;
    public $pesohistorial;
    public $tallahistorial;
    public $fchistorial;
    public $frhistorial;
    public $ahhistorial;
    public $apnphistorial;
    public $hemotipohistorial;
    public $alergiashistorial;
    public $apphistorial;
    public $citahistorial;
    public $nompaciente;
    public $diagnostico;
    
    // Campos de auditoría
    public $created_at;
    public $created_by;
    public $updated_by;

    public function __construct($data = [])
    {
        $this->idpaciente = $data['idpaciente'] ?? null;
        $this->fechahistorial = $data['fechahistorial'] ?? date('Y-m-d H:i:s');
        $this->pesohistorial = $data['pesohistorial'] ?? null;
        $this->tallahistorial = $data['tallahistorial'] ?? null;
        $this->fchistorial = $data['fchistorial'] ?? null;
        $this->frhistorial = $data['frhistorial'] ?? null;
        $this->ahhistorial = $data['ahhistorial'] ?? null;
        $this->apnphistorial = $data['apnphistorial'] ?? null;
        $this->hemotipohistorial = $data['hemotipohistorial'] ?? null;
        $this->alergiashistorial = $data['alergiashistorial'] ?? null;
        $this->apphistorial = $data['apphistorial'] ?? null;
        $this->citahistorial = $data['citahistorial'] ?? null;
        $this->nompaciente = $data['nompaciente'] ?? null;
        $this->diagnostico = $data['diagnostico'] ?? null;
        
        // Campos de auditoría
        $this->created_at = $data['created_at'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
    }

    /**
     * Valida los datos para crear un nuevo historial
     */
    public function validate()
    {
        $errors = [];

        // Validaciones obligatorias
        if (empty($this->idpaciente)) {
            $errors[] = 'ID del paciente es requerido';
        }

        if (empty($this->diagnostico)) {
            $errors[] = 'Diagnóstico es requerido';
        }

        // Validaciones de datos médicos
        if (!empty($this->pesohistorial)) {
            if (!is_numeric($this->pesohistorial)) {
                $errors[] = 'El peso debe ser un valor numérico';
            } elseif ($this->pesohistorial <= 0 || $this->pesohistorial > 1000) {
                $errors[] = 'El peso debe estar entre 0.1 y 1000 kg';
            }
        }

        if (!empty($this->tallahistorial)) {
            if (!is_numeric($this->tallahistorial)) {
                $errors[] = 'La talla debe ser un valor numérico';
            } elseif ($this->tallahistorial <= 0 || $this->tallahistorial > 300) {
                $errors[] = 'La talla debe estar entre 1 y 300 cm';
            }
        }

        if (!empty($this->fchistorial)) {
            if (!is_numeric($this->fchistorial)) {
                $errors[] = 'La frecuencia cardíaca debe ser un valor numérico';
            } elseif ($this->fchistorial <= 0 || $this->fchistorial > 300) {
                $errors[] = 'La frecuencia cardíaca debe estar entre 1 y 300 bpm';
            }
        }

        if (!empty($this->frhistorial)) {
            if (!is_numeric($this->frhistorial)) {
                $errors[] = 'La frecuencia respiratoria debe ser un valor numérico';
            } elseif ($this->frhistorial <= 0 || $this->frhistorial > 100) {
                $errors[] = 'La frecuencia respiratoria debe estar entre 1 y 100 rpm';
            }
        }

        // Validaciones de hemotipo
        if (!empty($this->hemotipohistorial)) {
            $tiposValidos = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            if (!in_array(strtoupper($this->hemotipohistorial), $tiposValidos)) {
                $errors[] = 'Tipo de sangre inválido. Valores válidos: ' . implode(', ', $tiposValidos);
            } else {
                $this->hemotipohistorial = strtoupper($this->hemotipohistorial);
            }
        }

        // Validación de fecha
        if (!empty($this->fechahistorial)) {
            if (strtotime($this->fechahistorial) === false) {
                $errors[] = 'Formato de fecha inválido';
            }
        }

        return $errors;
    }

    /**
     * Sanitiza los datos de entrada
     */
    public function sanitize()
    {
        // Limpiar campos de texto
        $this->apnphistorial = !empty($this->apnphistorial) ? trim($this->apnphistorial) : null;
        $this->alergiashistorial = !empty($this->alergiashistorial) ? trim($this->alergiashistorial) : null;
        $this->apphistorial = !empty($this->apphistorial) ? trim($this->apphistorial) : null;
        $this->citahistorial = !empty($this->citahistorial) ? trim($this->citahistorial) : null;
        $this->nompaciente = !empty($this->nompaciente) ? trim($this->nompaciente) : null;
        $this->diagnostico = !empty($this->diagnostico) ? trim($this->diagnostico) : null;
        $this->ahhistorial = !empty($this->ahhistorial) ? trim($this->ahhistorial) : null;

        // Convertir valores numéricos
        if (!empty($this->pesohistorial)) {
            $this->pesohistorial = (float) $this->pesohistorial;
        }
        if (!empty($this->tallahistorial)) {
            $this->tallahistorial = (float) $this->tallahistorial;
        }
        if (!empty($this->fchistorial)) {
            $this->fchistorial = (int) $this->fchistorial;
        }
        if (!empty($this->frhistorial)) {
            $this->frhistorial = (int) $this->frhistorial;
        }
        if (!empty($this->idpaciente)) {
            $this->idpaciente = (int) $this->idpaciente;
        }
        if (!empty($this->created_by)) {
            $this->created_by = (int) $this->created_by;
        }
        if (!empty($this->updated_by)) {
            $this->updated_by = (int) $this->updated_by;
        }
    }

    /**
     * Convierte a array para el repositorio
     */
    public function toArray()
    {
        return [
            'idpaciente' => $this->idpaciente,
            'fechahistorial' => $this->fechahistorial,
            'pesohistorial' => $this->pesohistorial,
            'tallahistorial' => $this->tallahistorial,
            'fchistorial' => $this->fchistorial,
            'frhistorial' => $this->frhistorial,
            'ahhistorial' => $this->ahhistorial,
            'apnphistorial' => $this->apnphistorial,
            'hemotipohistorial' => $this->hemotipohistorial,
            'alergiashistorial' => $this->alergiashistorial,
            'apphistorial' => $this->apphistorial,
            'citahistorial' => $this->citahistorial,
            'nompaciente' => $this->nompaciente,
            'diagnostico' => $this->diagnostico,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by
        ];
    }

    /**
     * Verifica si los datos son válidos para crear el historial
     */
    public function isValid()
    {
        return empty($this->validate());
    }

    /**
     * Obtiene mensajes de validación como string
     */
    public function getValidationMessage()
    {
        $errors = $this->validate();
        return empty($errors) ? '' : implode('; ', $errors);
    }
}

?>