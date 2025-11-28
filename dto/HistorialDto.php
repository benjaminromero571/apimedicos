<?php

/**
 * DTO para Historial Médico
 * Transferencia de datos de historial médico con validación básica
 */
class HistorialDto
{
    public $idhistorial;
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
    
    // Datos calculados
    public $imc;
    public $categoria_imc;
    
    // Datos del paciente relacionado
    public $nombre_paciente;
    public $rutpaciente;
    
    // Datos de auditoría
    public $created_at;
    public $created_by;
    public $created_by_name;
    public $updated_at;
    public $updated_by;
    public $updated_by_name;

    public function __construct($data = [])
    {
        $this->idhistorial = $data['idhistorial'] ?? null;
        $this->idpaciente = $data['idpaciente'] ?? null;
        $this->fechahistorial = $data['fechahistorial'] ?? null;
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
        
        // Datos adicionales
        $this->nombre_paciente = $data['nombre_paciente'] ?? null;
        $this->rutpaciente = $data['rutpaciente'] ?? null;
        
        // Datos de auditoría
        $this->created_at = $data['created_at'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->created_by_name = $data['created_by_name'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
        $this->updated_by_name = $data['updated_by_name'] ?? null;
        
        // Calcular IMC si es posible
        $this->imc = $this->calcularIMC();
        $this->categoria_imc = $this->obtenerCategoriaIMC();
    }

    /**
     * Crea desde una entidad HistorialEntity
     */
    public static function fromEntity($entity)
    {
        $data = $entity->toArray();
        return new static($data);
    }

    /**
     * Calcula el IMC
     */
    protected function calcularIMC()
    {
        if (empty($this->pesohistorial) || empty($this->tallahistorial)) {
            return null;
        }

        $peso = (float) $this->pesohistorial;
        $talla = (float) $this->tallahistorial / 100; // Convertir cm a metros

        if ($talla <= 0) {
            return null;
        }

        return round($peso / ($talla * $talla), 2);
    }

    /**
     * Obtiene la categoría del IMC
     */
    protected function obtenerCategoriaIMC()
    {
        if ($this->imc === null) {
            return 'No disponible';
        }

        if ($this->imc < 18.5) {
            return 'Bajo peso';
        } elseif ($this->imc < 25) {
            return 'Peso normal';
        } elseif ($this->imc < 30) {
            return 'Sobrepeso';
        } else {
            return 'Obesidad';
        }
    }

    /**
     * Obtiene los signos vitales
     */
    public function getSignosVitales()
    {
        return [
            'peso' => $this->pesohistorial,
            'talla' => $this->tallahistorial,
            'frecuencia_cardiaca' => $this->fchistorial,
            'frecuencia_respiratoria' => $this->frhistorial,
            'presion_arterial' => $this->ahhistorial,
            'imc' => $this->imc,
            'categoria_imc' => $this->categoria_imc
        ];
    }

    /**
     * Obtiene los antecedentes médicos
     */
    public function getAntecedentes()
    {
        return [
            'apnp' => $this->apnphistorial,
            'app' => $this->apphistorial,
            'hemotipo' => $this->hemotipohistorial,
            'alergias' => $this->alergiashistorial
        ];
    }

    /**
     * Formatea la fecha
     */
    public function getFechaFormateada()
    {
        if (empty($this->fechahistorial)) {
            return '';
        }

        $timestamp = is_numeric($this->fechahistorial) ? 
                    $this->fechahistorial : 
                    strtotime($this->fechahistorial);
        
        if ($timestamp !== false) {
            return date('d/m/Y H:i', $timestamp);
        }

        return $this->fechahistorial;
    }

    /**
     * Convierte a array
     */
    public function toArray()
    {
        return [
            'idhistorial' => $this->idhistorial,
            'idpaciente' => $this->idpaciente,
            'fechahistorial' => $this->fechahistorial,
            'fecha_formateada' => $this->getFechaFormateada(),
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
            'imc' => $this->imc,
            'categoria_imc' => $this->categoria_imc,
            'signos_vitales' => $this->getSignosVitales(),
            'antecedentes' => $this->getAntecedentes(),
            'nombre_paciente' => $this->nombre_paciente,
            'rutpaciente' => $this->rutpaciente,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'created_by_name' => $this->created_by_name,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            'updated_by_name' => $this->updated_by_name
        ];
    }
}

?>