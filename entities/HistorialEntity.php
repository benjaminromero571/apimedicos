<?php

/**
 * Entidad Historial - Representación pura de datos médicos
 * Solo contiene propiedades y métodos básicos de acceso
 * NO contiene lógica de negocio ni acceso a datos
 */
class HistorialEntity
{
    private $idhistorial;
    private $pesohistorial;
    private $tallahistorial;
    private $fchistorial;        // Frecuencia cardíaca
    private $frhistorial;        // Frecuencia respiratoria
    private $ahhistorial;        // Presión arterial
    private $apnphistorial;      // Antecedentes Personales No Patológicos
    private $hemotipohistorial;  // Tipo de sangre
    private $alergiashistorial;
    private $apphistorial;       // Antecedentes Personales Patológicos
    private $citahistorial;
    private $idpaciente;
    private $nompaciente;
    private $fechahistorial;
    private $diagnostico;
    
    // Campos de auditoría
    private $created_at;
    private $created_by;
    private $created_by_name;
    private $updated_at;
    private $updated_by;
    private $updated_by_name;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    /**
     * Rellena la entidad con datos
     */
    public function fill(array $data)
    {
        $this->idhistorial = $data['idhistorial'] ?? null;
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
        $this->idpaciente = $data['idpaciente'] ?? null;
        $this->nompaciente = $data['nompaciente'] ?? null;
        $this->fechahistorial = $data['fechahistorial'] ?? null;
        $this->diagnostico = $data['diagnostico'] ?? null;
        
        // Campos de auditoría
        $this->created_at = $data['created_at'] ?? null;
        $this->created_by = $data['created_by'] ?? null;
        $this->created_by_name = $data['created_by_name'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->updated_by = $data['updated_by'] ?? null;
        $this->updated_by_name = $data['updated_by_name'] ?? null;
    }

    // Getters básicos
    public function getId()
    {
        return $this->idhistorial;
    }

    public function getPeso()
    {
        return $this->pesohistorial;
    }

    public function getTalla()
    {
        return $this->tallahistorial;
    }

    public function getFrecuenciaCardiaca()
    {
        return $this->fchistorial;
    }

    public function getFrecuenciaRespiratoria()
    {
        return $this->frhistorial;
    }

    public function getPresionArterial()
    {
        return $this->ahhistorial;
    }

    public function getApnp()
    {
        return $this->apnphistorial;
    }

    public function getHemotipo()
    {
        return $this->hemotipohistorial;
    }

    public function getAlergias()
    {
        return $this->alergiashistorial;
    }

    public function getApp()
    {
        return $this->apphistorial;
    }

    public function getCita()
    {
        return $this->citahistorial;
    }

    public function getIdPaciente()
    {
        return $this->idpaciente;
    }

    public function getNomPaciente()
    {
        return $this->nompaciente;
    }

    public function getFecha()
    {
        return $this->fechahistorial;
    }

    public function getDiagnostico()
    {
        return $this->diagnostico;
    }
    
    public function getCreatedAt()
    {
        return $this->created_at;
    }
    
    public function getCreatedBy()
    {
        return $this->created_by;
    }
    
    public function getCreatedByName()
    {
        return $this->created_by_name;
    }
    
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    
    public function getUpdatedBy()
    {
        return $this->updated_by;
    }
    
    public function getUpdatedByName()
    {
        return $this->updated_by_name;
    }

    // Setters básicos
    public function setId($idhistorial)
    {
        $this->idhistorial = $idhistorial;
        return $this;
    }

    public function setPeso($pesohistorial)
    {
        $this->pesohistorial = $pesohistorial;
        return $this;
    }

    public function setTalla($tallahistorial)
    {
        $this->tallahistorial = $tallahistorial;
        return $this;
    }

    public function setFrecuenciaCardiaca($fchistorial)
    {
        $this->fchistorial = $fchistorial;
        return $this;
    }

    public function setFrecuenciaRespiratoria($frhistorial)
    {
        $this->frhistorial = $frhistorial;
        return $this;
    }

    public function setPresionArterial($ahhistorial)
    {
        $this->ahhistorial = $ahhistorial;
        return $this;
    }

    public function setApnp($apnphistorial)
    {
        $this->apnphistorial = $apnphistorial;
        return $this;
    }

    public function setHemotipo($hemotipohistorial)
    {
        $this->hemotipohistorial = $hemotipohistorial;
        return $this;
    }

    public function setAlergias($alergiashistorial)
    {
        $this->alergiashistorial = $alergiashistorial;
        return $this;
    }

    public function setApp($apphistorial)
    {
        $this->apphistorial = $apphistorial;
        return $this;
    }

    public function setCita($citahistorial)
    {
        $this->citahistorial = $citahistorial;
        return $this;
    }

    public function setIdPaciente($idpaciente)
    {
        $this->idpaciente = $idpaciente;
        return $this;
    }

    public function setNomPaciente($nompaciente)
    {
        $this->nompaciente = $nompaciente;
        return $this;
    }

    public function setFecha($fechahistorial)
    {
        $this->fechahistorial = $fechahistorial;
        return $this;
    }

    public function setDiagnostico($diagnostico)
    {
        $this->diagnostico = $diagnostico;
        return $this;
    }
    
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }
    
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
        return $this;
    }
    
    public function setCreatedByName($created_by_name)
    {
        $this->created_by_name = $created_by_name;
        return $this;
    }
    
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }
    
    public function setUpdatedBy($updated_by)
    {
        $this->updated_by = $updated_by;
        return $this;
    }
    
    public function setUpdatedByName($updated_by_name)
    {
        $this->updated_by_name = $updated_by_name;
        return $this;
    }

    /**
     * Convierte la entidad a array
     */
    public function toArray()
    {
        return [
            'idhistorial' => $this->idhistorial,
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
            'idpaciente' => $this->idpaciente,
            'nompaciente' => $this->nompaciente,
            'fechahistorial' => $this->fechahistorial,
            'diagnostico' => $this->diagnostico,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'created_by_name' => $this->created_by_name,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            'updated_by_name' => $this->updated_by_name
        ];
    }

    /**
     * Convierte la entidad a JSON
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Crea una instancia desde un array
     */
    public static function fromArray(array $data)
    {
        return new static($data);
    }

    /**
     * Verifica si la entidad tiene un ID válido
     */
    public function exists()
    {
        return !empty($this->idhistorial);
    }

    /**
     * Obtiene solo los datos modificables
     */
    public function getFillableData()
    {
        return [
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
            'idpaciente' => $this->idpaciente,
            'nompaciente' => $this->nompaciente,
            'fechahistorial' => $this->fechahistorial,
            'diagnostico' => $this->diagnostico
        ];
    }
}

?>