<?php

/**
 * DTO para búsqueda de historiales médicos
 * Contiene criterios de filtrado y paginación
 */
class HistorialSearchDto
{
    public $nombre_paciente;
    public $rutpaciente;
    public $fecha_desde;
    public $fecha_hasta;
    public $alergia;
    public $diagnostico;
    public $hemotipo;
    public $idpaciente;
    
    // Parámetros de paginación
    public $limit;
    public $offset;
    public $orderBy;
    public $orderDirection;

    public function __construct($data = [])
    {
        $this->nombre_paciente = $data['nombre_paciente'] ?? null;
        $this->rutpaciente = $data['rutpaciente'] ?? null;
        $this->fecha_desde = $data['fecha_desde'] ?? null;
        $this->fecha_hasta = $data['fecha_hasta'] ?? null;
        $this->alergia = $data['alergia'] ?? null;
        $this->diagnostico = $data['diagnostico'] ?? null;
        $this->hemotipo = $data['hemotipo'] ?? null;
        $this->idpaciente = $data['idpaciente'] ?? null;
        
        // Paginación
        $this->limit = isset($data['limit']) ? (int)$data['limit'] : 20;
        $this->offset = isset($data['offset']) ? (int)$data['offset'] : 0;
        $this->orderBy = $data['orderBy'] ?? 'fechahistorial';
        $this->orderDirection = $data['orderDirection'] ?? 'DESC';
        
        $this->sanitize();
    }

    /**
     * Sanitiza los datos de búsqueda
     */
    protected function sanitize()
    {
        // Limpiar strings
        if (!empty($this->nombre_paciente)) {
            $this->nombre_paciente = trim($this->nombre_paciente);
        }
        if (!empty($this->rutpaciente)) {
            $this->rutpaciente = trim($this->rutpaciente);
        }
        if (!empty($this->alergia)) {
            $this->alergia = trim($this->alergia);
        }
        if (!empty($this->diagnostico)) {
            $this->diagnostico = trim($this->diagnostico);
        }
        if (!empty($this->hemotipo)) {
            $this->hemotipo = strtoupper(trim($this->hemotipo));
        }
        
        // Validar fechas
        if (!empty($this->fecha_desde)) {
            if (strtotime($this->fecha_desde) === false) {
                $this->fecha_desde = null;
            }
        }
        if (!empty($this->fecha_hasta)) {
            if (strtotime($this->fecha_hasta) === false) {
                $this->fecha_hasta = null;
            }
        }
        
        // Validar paginación
        if ($this->limit <= 0 || $this->limit > 100) {
            $this->limit = 20;
        }
        if ($this->offset < 0) {
            $this->offset = 0;
        }
        
        // Validar ordenamiento
        $validOrderBy = ['fechahistorial', 'idhistorial', 'nompaciente', 'diagnostico'];
        if (!in_array($this->orderBy, $validOrderBy)) {
            $this->orderBy = 'fechahistorial';
        }
        
        $validDirection = ['ASC', 'DESC'];
        if (!in_array(strtoupper($this->orderDirection), $validDirection)) {
            $this->orderDirection = 'DESC';
        } else {
            $this->orderDirection = strtoupper($this->orderDirection);
        }
        
        // Convertir ID de paciente a entero
        if (!empty($this->idpaciente)) {
            $this->idpaciente = (int) $this->idpaciente;
        }
    }

    /**
     * Obtiene criterios para el repositorio
     */
    public function getCriteriaForRepository()
    {
        $criteria = [];
        
        if (!empty($this->nombre_paciente)) {
            $criteria['nombre_paciente'] = $this->nombre_paciente;
        }
        if (!empty($this->rutpaciente)) {
            $criteria['rutpaciente'] = $this->rutpaciente;
        }
        if (!empty($this->fecha_desde)) {
            $criteria['fecha_desde'] = $this->fecha_desde;
        }
        if (!empty($this->fecha_hasta)) {
            $criteria['fecha_hasta'] = $this->fecha_hasta;
        }
        if (!empty($this->alergia)) {
            $criteria['alergia'] = $this->alergia;
        }
        if (!empty($this->diagnostico)) {
            $criteria['diagnostico'] = $this->diagnostico;
        }
        if (!empty($this->hemotipo)) {
            $criteria['hemotipo'] = $this->hemotipo;
        }
        if (!empty($this->idpaciente)) {
            $criteria['idpaciente'] = $this->idpaciente;
        }
        
        return $criteria;
    }

    /**
     * Verifica si hay criterios de búsqueda activos
     */
    public function hasSearchCriteria()
    {
        return !empty($this->nombre_paciente) ||
               !empty($this->rutpaciente) ||
               !empty($this->fecha_desde) ||
               !empty($this->fecha_hasta) ||
               !empty($this->alergia) ||
               !empty($this->diagnostico) ||
               !empty($this->hemotipo) ||
               !empty($this->idpaciente);
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
            'direction' => $this->orderDirection
        ];
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
            'has_search' => $this->hasSearchCriteria()
        ];
    }
}

?>