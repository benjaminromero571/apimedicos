<?php

require_once __DIR__ . '/../entities/UserEntity.php';

/**
 * UserDto
 * 
 * DTO para representación de usuarios sin información sensible
 * Incluye métodos de transformación y formateo
 */
class UserDto
{
    public $id;
    public $name;
    public $email;
    public $rol;
    public $rol_formateado;
    public $iniciales;
    public $created_at;
    public $updated_at;

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
     * Llena el DTO con datos
     */
    public function fill($data)
    {
        if (is_array($data)) {
            $this->id = $data['id'] ?? null;
            $this->name = $data['name'] ?? null;
            $this->email = $data['email'] ?? null;
            $this->rol = $data['rol'] ?? null;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        } elseif ($data instanceof UserEntity) {
            $this->id = $data->id;
            $this->name = $data->name;
            $this->email = $data->email;
            $this->rol = $data->rol;
            $this->created_at = $data->created_at;
            $this->updated_at = $data->updated_at;
        }

        // Generar campos computados
        $this->rol_formateado = $this->getRolFormateado();
        $this->iniciales = $this->getIniciales();
    }

    /**
     * Obtiene el rol formateado
     */
    private function getRolFormateado()
    {
        $roles = [
            'Administrador' => 'Administrador',
            'Medico' => 'Médico',
            'Profesional' => 'Profesional de Salud',
            'Cuidador' => 'Cuidador/Familiar'
        ];

        return $roles[$this->rol] ?? $this->rol;
    }

    /**
     * Obtiene las iniciales del usuario
     */
    private function getIniciales()
    {
        if (empty($this->name)) {
            return '';
        }

        $palabras = explode(' ', trim($this->name));
        $iniciales = '';

        foreach (array_slice($palabras, 0, 2) as $palabra) {
            if (!empty($palabra)) {
                $iniciales .= strtoupper(substr($palabra, 0, 1));
            }
        }

        return $iniciales ?: substr(strtoupper($this->name), 0, 2);
    }

    /**
     * Convierte a array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'rol' => $this->rol,
            'rol_formateado' => $this->rol_formateado,
            'iniciales' => $this->iniciales,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    /**
     * Convierte a array resumido (sin información sensible)
     */
    public function toArraySummary()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'rol' => $this->rol,
            'rol_formateado' => $this->rol_formateado,
            'iniciales' => $this->iniciales
        ];
    }

    /**
     * Convierte a JSON
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Crea un UserDto desde un array
     */
    public static function fromArray(array $data)
    {
        return new self($data);
    }

    /**
     * Crea un UserDto desde una UserEntity
     */
    public static function fromEntity(UserEntity $entity)
    {
        return new self($entity);
    }

    /**
     * Valida si el DTO es válido
     */
    public function isValid()
    {
        return !empty($this->id) && 
               !empty($this->name) && 
               !empty($this->email) && 
               !empty($this->rol);
    }
}

?>