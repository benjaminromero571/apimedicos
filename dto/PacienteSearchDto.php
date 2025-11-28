<?php

/**
 * DTO para búsqueda de pacientes
 * Contiene criterios de búsqueda y filtros
 */
class PacienteSearchDto
{
    public $nombre;
    public $rut;
    public $edad_min;
    public $edad_max;
    public $telefono;
    public $direccion;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data)
    {
        $this->nombre = $data['nombre'] ?? null;
        $this->rut = $data['rut'] ?? null;
        $this->edad_min = $data['edad_min'] ?? null;
        $this->edad_max = $data['edad_max'] ?? null;
        $this->telefono = $data['telefono'] ?? null;
        $this->direccion = $data['direccion'] ?? null;
    }

    public function toArray()
    {
        return [
            'nombre' => $this->nombre,
            'rut' => $this->rut,
            'edad_min' => $this->edad_min,
            'edad_max' => $this->edad_max,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion
        ];
    }

    /**
     * Obtiene solo los criterios que tienen valores
     */
    public function getActiveCriteria()
    {
        $criteria = [];

        if (!empty($this->nombre)) {
            $criteria['nombre'] = $this->nombre;
        }

        if (!empty($this->rut)) {
            $criteria['rut'] = $this->rut;
        }

        if (!empty($this->edad_min) && is_numeric($this->edad_min)) {
            $criteria['edad_min'] = (int) $this->edad_min;
        }

        if (!empty($this->edad_max) && is_numeric($this->edad_max)) {
            $criteria['edad_max'] = (int) $this->edad_max;
        }

        if (!empty($this->telefono)) {
            $criteria['telefono'] = $this->telefono;
        }

        if (!empty($this->direccion)) {
            $criteria['direccion'] = $this->direccion;
        }

        return $criteria;
    }

    /**
     * Verifica si hay al menos un criterio de búsqueda
     */
    public function hasSearchCriteria()
    {
        return !empty($this->getActiveCriteria());
    }

    public static function fromArray(array $data)
    {
        return new static($data);
    }
}

?>