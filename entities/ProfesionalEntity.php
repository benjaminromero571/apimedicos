<?php

/**
 * Entidad Profesional - Representación pura de datos de doctores/profesionales
 * Solo contiene propiedades y métodos básicos de acceso
 * NO contiene lógica de negocio ni acceso a datos
 */
class ProfesionalEntity
{
    private $id;
    private $nombre;
    private $telefono;
    private $documento;
    private $especialidad;
    private $id_user;

    // Mantener compatibilidad con nombres anteriores
    private $iddoctor;
    private $nomdoctor;
    private $teldoctor;
    private $cedoctor;
    private $espedoctor;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    /**
     * Rellena la entidad con datos
     */
    public function fill(array $data)
    {
        // Nuevos campos
        $this->id = $data['id'] ?? null;
        $this->nombre = $data['nombre'] ?? null;
        $this->telefono = $data['telefono'] ?? null;
        $this->documento = $data['documento'] ?? null;
        $this->especialidad = $data['especialidad'] ?? null;
        $this->id_user = $data['id_user'] ?? null;

        // Compatibilidad con campos anteriores
        $this->iddoctor = $data['iddoctor'] ?? $this->id;
        $this->nomdoctor = $data['nomdoctor'] ?? $this->nombre;
        $this->teldoctor = $data['teldoctor'] ?? $this->telefono;
        $this->cedoctor = $data['cedoctor'] ?? $this->documento;
        $this->espedoctor = $data['espedoctor'] ?? $this->especialidad;

        // Si vienen datos con nombres antiguos, mapear a nuevos
        if (!$this->id && $this->iddoctor) {
            $this->id = $this->iddoctor;
        }
        if (!$this->nombre && $this->nomdoctor) {
            $this->nombre = $this->nomdoctor;
        }
        if (!$this->telefono && $this->teldoctor) {
            $this->telefono = $this->teldoctor;
        }
        if (!$this->documento && $this->cedoctor) {
            $this->documento = $this->cedoctor;
        }
        if (!$this->especialidad && $this->espedoctor) {
            $this->especialidad = $this->espedoctor;
        }
    }

    // Getters básicos - nuevos nombres
    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function getTelefono()
    {
        return $this->telefono;
    }

    public function getDocumento()
    {
        return $this->documento;
    }

    public function getEspecialidad()
    {
        return $this->especialidad;
    }

    public function getIdUser()
    {
        return $this->id_user;
    }

    // Getters compatibilidad - nombres anteriores
    public function getIddoctor()
    {
        return $this->getId();
    }

    public function getNomdoctor()
    {
        return $this->getNombre();
    }

    public function getTeldoctor()
    {
        return $this->getTelefono();
    }

    public function getCedoctor()
    {
        return $this->getDocumento();
    }

    public function getEspedoctor()
    {
        return $this->getEspecialidad();
    }

    // Método de compatibilidad
    public function getCedula()
    {
        return $this->getDocumento();
    }

    // Setters básicos - nuevos nombres
    public function setId($id)
    {
        $this->id = $id;
        $this->iddoctor = $id; // Mantener compatibilidad
        return $this;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
        $this->nomdoctor = $nombre; // Mantener compatibilidad
        return $this;
    }

    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
        $this->teldoctor = $telefono; // Mantener compatibilidad
        return $this;
    }

    public function setDocumento($documento)
    {
        $this->documento = $documento;
        $this->cedoctor = $documento; // Mantener compatibilidad
        return $this;
    }

    public function setEspecialidad($especialidad)
    {
        $this->especialidad = $especialidad;
        $this->espedoctor = $especialidad; // Mantener compatibilidad
        return $this;
    }

    public function setIdUser($id_user)
    {
        $this->id_user = $id_user;
        return $this;
    }

    // Setters compatibilidad - nombres anteriores
    public function setIddoctor($iddoctor)
    {
        return $this->setId($iddoctor);
    }

    public function setNomdoctor($nomdoctor)
    {
        return $this->setNombre($nomdoctor);
    }

    public function setTeldoctor($teldoctor)
    {
        return $this->setTelefono($teldoctor);
    }

    public function setCedoctor($cedoctor)
    {
        return $this->setDocumento($cedoctor);
    }

    public function setEspedoctor($espedoctor)
    {
        return $this->setEspecialidad($espedoctor);
    }

    /**
     * Convierte la entidad a array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'documento' => $this->documento,
            'especialidad' => $this->especialidad,
            'id_user' => $this->id_user,
            // Campos de compatibilidad
            'iddoctor' => $this->id,
            'nomdoctor' => $this->nombre,
            'teldoctor' => $this->telefono,
            'cedoctor' => $this->documento,
            'espedoctor' => $this->especialidad
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
        return !empty($this->id);
    }

    /**
     * Obtiene solo los datos modificables
     */
    public function getFillableData()
    {
        return [
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'documento' => $this->documento,
            'especialidad' => $this->especialidad,
            'id_user' => $this->id_user
        ];
    }

    /**
     * Verifica si tiene información completa básica
     */
    public function isComplete()
    {
        return !empty($this->nombre) && 
               !empty($this->documento) && 
               !empty($this->especialidad);
    }

    /**
     * Obtiene información básica para listado
     */
    public function getBasicInfo()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'especialidad' => $this->especialidad
        ];
    }
}

?>