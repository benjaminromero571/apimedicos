<?php

/**
 * DTO para búsqueda de profesionales
 * Contiene criterios de filtrado y paginación
 */
class ProfesionalSearchDto
{
    public $nombre;
    public $especialidad;
    public $cedula;
    public $telefono;
    
    // Parámetros de paginación
    public $limit;
    public $offset;
    public $orderBy;
    public $orderDirection;

    public function __construct($data = [])
    {
        $this->nombre = $data['nombre'] ?? null;
        $this->especialidad = $data['especialidad'] ?? null;
        $this->cedula = $data['cedula'] ?? null;
        $this->telefono = $data['telefono'] ?? null;
        
        // Paginación
        $this->limit = isset($data['limit']) ? (int)$data['limit'] : 20;
        $this->offset = isset($data['offset']) ? (int)$data['offset'] : 0;
        $this->orderBy = $data['orderBy'] ?? 'nomdoctor';
        $this->orderDirection = $data['orderDirection'] ?? 'ASC';
        
        $this->sanitize();
    }

    /**
     * Sanitiza los datos de búsqueda
     */
    protected function sanitize()
    {
        // Limpiar strings
        if (!empty($this->nombre)) {
            $this->nombre = trim($this->nombre);
        }
        if (!empty($this->especialidad)) {
            $this->especialidad = trim($this->especialidad);
        }
        if (!empty($this->cedula)) {
            $this->cedula = strtoupper(trim($this->cedula));
        }
        if (!empty($this->telefono)) {
            $this->telefono = preg_replace('/[^0-9]/', '', trim($this->telefono));
        }
        
        // Validar paginación
        if ($this->limit <= 0 || $this->limit > 100) {
            $this->limit = 20;
        }
        if ($this->offset < 0) {
            $this->offset = 0;
        }
        
        // Validar ordenamiento
        $validOrderBy = ['nomdoctor', 'espedoctor', 'cedoctor', 'iddoctor'];
        if (!in_array($this->orderBy, $validOrderBy)) {
            $this->orderBy = 'nomdoctor';
        }
        
        $validDirection = ['ASC', 'DESC'];
        if (!in_array(strtoupper($this->orderDirection), $validDirection)) {
            $this->orderDirection = 'ASC';
        } else {
            $this->orderDirection = strtoupper($this->orderDirection);
        }
    }

    /**
     * Obtiene criterios para el repositorio
     */
    public function getCriteriaForRepository()
    {
        $criteria = [];
        
        if (!empty($this->nombre)) {
            $criteria['nombre'] = $this->nombre;
        }
        if (!empty($this->especialidad)) {
            $criteria['especialidad'] = $this->especialidad;
        }
        if (!empty($this->cedula)) {
            $criteria['cedula'] = $this->cedula;
        }
        if (!empty($this->telefono)) {
            $criteria['telefono'] = $this->telefono;
        }
        
        return $criteria;
    }

    /**
     * Verifica si hay criterios de búsqueda activos
     */
    public function hasSearchCriteria()
    {
        return !empty($this->nombre) ||
               !empty($this->especialidad) ||
               !empty($this->cedula) ||
               !empty($this->telefono);
    }

    /**
     * Obtiene información de paginación
     */
    public function getPagination()
    {
        return [
            'limit' => $this->limit,
            'offset' => $this->offset,
            'page' => floor($this->offset / $this->limit) + 1
        ];
    }

    /**
     * Obtiene información de ordenamiento
     */
    public function getOrdering()
    {
        return [
            'field' => $this->orderBy,
            'direction' => $this->orderDirection,
            'order_string' => "{$this->orderBy} {$this->orderDirection}"
        ];
    }

    /**
     * Obtiene descripción legible de los criterios
     */
    public function getSearchDescription()
    {
        $descriptions = [];
        
        if (!empty($this->nombre)) {
            $descriptions[] = "nombre contiene '{$this->nombre}'";
        }
        if (!empty($this->especialidad)) {
            $descriptions[] = "especialidad es '{$this->especialidad}'";
        }
        if (!empty($this->cedula)) {
            $descriptions[] = "cédula contiene '{$this->cedula}'";
        }
        if (!empty($this->telefono)) {
            $descriptions[] = "teléfono contiene '{$this->telefono}'";
        }
        
        if (empty($descriptions)) {
            return 'Sin filtros aplicados';
        }
        
        return 'Búsqueda por: ' . implode(', ', $descriptions);
    }

    /**
     * Valida los criterios de búsqueda
     */
    public function validate()
    {
        $errors = [];
        
        // Validar longitud mínima de términos de búsqueda
        if (!empty($this->nombre) && strlen($this->nombre) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres';
        }
        
        if (!empty($this->cedula) && strlen($this->cedula) < 3) {
            $errors[] = 'La cédula debe tener al menos 3 caracteres';
        }
        
        if (!empty($this->telefono) && strlen($this->telefono) < 4) {
            $errors[] = 'El teléfono debe tener al menos 4 dígitos';
        }
        
        return $errors;
    }

    /**
     * Verifica si la búsqueda es válida
     */
    public function isValid()
    {
        return empty($this->validate());
    }

    /**
     * Convierte a array
     */
    public function toArray()
    {
        return [
            'criteria' => $this->getCriteriaForRepository(),
            'pagination' => $this->getPagination(),
            'ordering' => $this->getOrdering(),
            'has_search' => $this->hasSearchCriteria(),
            'description' => $this->getSearchDescription(),
            'is_valid' => $this->isValid(),
            'validation_errors' => $this->validate()
        ];
    }
}

?>