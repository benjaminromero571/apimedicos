<?php

/**
 * Entidad Asignacion - Representación pura de datos
 * Solo contiene propiedades y métodos básicos de acceso
 * NO contiene lógica de negocio ni acceso a datos
 */
class AsignacionEntity
{
    private $id;
    private $user_id;
    private $paciente_id;
    private $fecha_asignacion;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    /**
     * Rellena la entidad con datos
     */
    public function fill(array $data)
    {
        if (isset($data['id'])) {
            $this->id = $data['id'];
        }
        
        if (isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        
        if (isset($data['paciente_id'])) {
            $this->paciente_id = $data['paciente_id'];
        }
        
        if (isset($data['fecha_asignacion'])) {
            $this->fecha_asignacion = $data['fecha_asignacion'];
        }
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getPacienteId()
    {
        return $this->paciente_id;
    }

    public function getFechaAsignacion()
    {
        return $this->fecha_asignacion;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function setPacienteId($paciente_id)
    {
        $this->paciente_id = $paciente_id;
        return $this;
    }

    public function setFechaAsignacion($fecha_asignacion)
    {
        $this->fecha_asignacion = $fecha_asignacion;
        return $this;
    }

    /**
     * Convierte la entidad a array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'paciente_id' => $this->paciente_id,
            'fecha_asignacion' => $this->fecha_asignacion
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
            'user_id' => $this->user_id,
            'paciente_id' => $this->paciente_id,
            'fecha_asignacion' => $this->fecha_asignacion
        ];
    }
}

?>