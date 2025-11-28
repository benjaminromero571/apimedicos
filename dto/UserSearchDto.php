<?php

/**
 * UserSearchDto
 * 
 * DTO para manejar criterios de búsqueda de usuarios
 * Incluye validaciones y normalización de parámetros de búsqueda
 */
class UserSearchDto
{
    public $name;
    public $email;
    public $rol;
    public $search; // Búsqueda general
    public $orderBy;
    public $orderDirection;
    public $limit;
    public $offset;
    public $page;

    private $errors = [];

    /**
     * Constructor
     */
    public function __construct($data = null)
    {
        if ($data) {
            $this->fill($data);
        }
    }

    /**
     * Llena el DTO con datos de búsqueda
     */
    public function fill($data)
    {
        if (is_array($data)) {
            $this->name = isset($data['name']) ? trim($data['name']) : null;
            $this->email = isset($data['email']) ? trim(strtolower($data['email'])) : null;
            $this->rol = $data['rol'] ?? null;
            $this->search = isset($data['search']) ? trim($data['search']) : null;
            $this->orderBy = $data['orderBy'] ?? $data['order_by'] ?? 'name';
            $this->orderDirection = isset($data['orderDirection']) ? 
                strtoupper($data['orderDirection']) : 
                (isset($data['order_direction']) ? strtoupper($data['order_direction']) : 'ASC');
            
            // Paginación
            $this->limit = isset($data['limit']) ? (int)$data['limit'] : 20;
            $this->offset = isset($data['offset']) ? (int)$data['offset'] : 0;
            $this->page = isset($data['page']) ? (int)$data['page'] : 1;
            
            // Si se proporciona página, calcular offset
            if ($this->page > 1 && $this->offset === 0) {
                $this->offset = ($this->page - 1) * $this->limit;
            }
        }
    }

    /**
     * Valida los criterios de búsqueda
     */
    public function validate()
    {
        $this->errors = [];

        $this->validateSearchTerms();
        $this->validateRole();
        $this->validateOrdering();
        $this->validatePagination();

        return empty($this->errors);
    }

    /**
     * Valida los términos de búsqueda
     */
    private function validateSearchTerms()
    {
        // Validar nombre si se proporciona
        if (!empty($this->name)) {
            if (strlen($this->name) < 2) {
                $this->errors['name'] = 'El nombre debe tener al menos 2 caracteres';
            }
            
            if (strlen($this->name) > 100) {
                $this->errors['name'] = 'El nombre no puede exceder 100 caracteres';
            }
        }

        // Validar email si se proporciona
        if (!empty($this->email)) {
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $this->errors['email'] = 'El formato del email no es válido';
            }
        }

        // Validar búsqueda general
        if (!empty($this->search)) {
            if (strlen($this->search) < 2) {
                $this->errors['search'] = 'El término de búsqueda debe tener al menos 2 caracteres';
            }
            
            if (strlen($this->search) > 100) {
                $this->errors['search'] = 'El término de búsqueda no puede exceder 100 caracteres';
            }
            
            // Limpiar caracteres especiales para prevenir inyección
            $this->search = preg_replace('/[<>"\'\\\]/', '', $this->search);
        }
    }

    /**
     * Valida el rol
     */
    private function validateRole()
    {
        if (!empty($this->rol)) {
            $validRoles = ['Administrador', 'Profesional', 'Cuidador', 'Medico'];
            if (!in_array($this->rol, $validRoles)) {
                $this->errors['rol'] = 'El rol especificado no es válido';
            }
        }
    }

    /**
     * Valida el ordenamiento
     */
    private function validateOrdering()
    {
        $validFields = ['id', 'name', 'email', 'rol', 'created_at'];
        $validDirections = ['ASC', 'DESC'];

        if (!in_array($this->orderBy, $validFields)) {
            $this->orderBy = 'name'; // Valor por defecto
        }

        if (!in_array($this->orderDirection, $validDirections)) {
            $this->orderDirection = 'ASC'; // Valor por defecto
        }
    }

    /**
     * Valida la paginación
     */
    private function validatePagination()
    {
        if ($this->limit < 1 || $this->limit > 100) {
            $this->limit = 20; // Valor por defecto
        }

        if ($this->offset < 0) {
            $this->offset = 0;
        }

        if ($this->page < 1) {
            $this->page = 1;
        }
    }

    /**
     * Obtiene los criterios de búsqueda como array
     */
    public function getCriteria()
    {
        $criteria = [];

        if (!empty($this->name)) {
            $criteria['name'] = $this->name;
        }

        if (!empty($this->email)) {
            $criteria['email'] = $this->email;
        }

        if (!empty($this->rol)) {
            $criteria['rol'] = $this->rol;
        }

        if (!empty($this->search)) {
            $criteria['search'] = $this->search;
        }

        return $criteria;
    }

    /**
     * Obtiene el string de ordenamiento
     */
    public function getOrderBy()
    {
        return $this->orderBy . ' ' . $this->orderDirection;
    }

    /**
     * Obtiene parámetros de paginación
     */
    public function getPaginationParams()
    {
        return [
            'limit' => $this->limit,
            'offset' => $this->offset,
            'page' => $this->page
        ];
    }

    /**
     * Verifica si hay criterios de búsqueda
     */
    public function hasSearchCriteria()
    {
        return !empty($this->name) || 
               !empty($this->email) || 
               !empty($this->rol) || 
               !empty($this->search);
    }

    /**
     * Obtiene los errores de validación
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Verifica si el DTO es válido
     */
    public function isValid()
    {
        return $this->validate();
    }

    /**
     * Convierte a array
     */
    public function toArray()
    {
        return [
            'criteria' => $this->getCriteria(),
            'order_by' => $this->getOrderBy(),
            'pagination' => $this->getPaginationParams()
        ];
    }

    /**
     * Crea un UserSearchDto desde un array
     */
    public static function fromArray(array $data)
    {
        return new self($data);
    }
}

?>