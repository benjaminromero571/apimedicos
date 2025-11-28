<?php

/**
 * Entidad Paciente - Representación pura de datos
 * Solo contiene propiedades y métodos básicos de acceso
 * NO contiene lógica de negocio ni acceso a datos
 */
class PacienteEntity
{
    private $idpaciente;
    private $rutpaciente;
    private $nompaciente;
    private $edadpaciente;
    private $telpaciente;
    private $dirpaciente;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    /**
     * Rellena la entidad con datos
     */
    public function fill(array $data)
    {
        if (isset($data['idpaciente'])) {
            $this->idpaciente = $data['idpaciente'];
        }
        
        if (isset($data['rutpaciente'])) {
            $this->rutpaciente = $data['rutpaciente'];
        }
        
        if (isset($data['nompaciente'])) {
            $this->nompaciente = $data['nompaciente'];
        }
        
        if (isset($data['edadpaciente'])) {
            $this->edadpaciente = $data['edadpaciente'];
        }
        
        if (isset($data['telpaciente'])) {
            $this->telpaciente = $data['telpaciente'];
        }
        
        if (isset($data['dirpaciente'])) {
            $this->dirpaciente = $data['dirpaciente'];
        }
    }

    // Getters
    public function getId()
    {
        return $this->idpaciente;
    }

    public function getRut()
    {
        return $this->rutpaciente;
    }

    public function getNombre()
    {
        return $this->nompaciente;
    }

    public function getEdad()
    {
        return $this->edadpaciente;
    }

    public function getTelefono()
    {
        return $this->telpaciente;
    }

    public function getDireccion()
    {
        return $this->dirpaciente;
    }

    // Setters
    public function setId($idpaciente)
    {
        $this->idpaciente = $idpaciente;
        return $this;
    }

    public function setRut($rutpaciente)
    {
        $this->rutpaciente = $rutpaciente;
        return $this;
    }

    public function setNombre($nompaciente)
    {
        $this->nompaciente = $nompaciente;
        return $this;
    }

    public function setEdad($edadpaciente)
    {
        $this->edadpaciente = $edadpaciente;
        return $this;
    }

    public function setTelefono($telpaciente)
    {
        $this->telpaciente = $telpaciente;
        return $this;
    }

    public function setDireccion($dirpaciente)
    {
        $this->dirpaciente = $dirpaciente;
        return $this;
    }


    /**
     * Convierte la entidad a array
     */
    public function toArray()
    {
        return [
            'idpaciente' => $this->idpaciente,
            'rutpaciente' => $this->rutpaciente,
            'nompaciente' => $this->nompaciente,
            'edadpaciente' => $this->edadpaciente,
            'telpaciente' => $this->telpaciente,
            'dirpaciente' => $this->dirpaciente
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
        return !empty($this->idpaciente);
    }

    /**
     * Obtiene solo los datos modificables
     */
    public function getFillableData()
    {
        return [
            'rutpaciente' => $this->rutpaciente,
            'nompaciente' => $this->nompaciente,
            'edadpaciente' => $this->edadpaciente,
            'telpaciente' => $this->telpaciente,
            'dirpaciente' => $this->dirpaciente
        ];
    }
}

?>