<?php

/**
 * UserEntity
 * 
 * Entidad para representar usuarios del sistema
 * Contiene solo la estructura de datos sin lógica de negocio
 */
class UserEntity
{
    public $id;
    public $name;
    public $password;
    public $email;
    public $rol;
    public $created_at;
    public $updated_at;

    /**
     * Constructor
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    /**
     * Llena la entidad con datos de un array
     */
    public function fill(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->rol = $data['rol'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    /**
     * Convierte la entidad a array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'password' => $this->password,
            'email' => $this->email,
            'rol' => $this->rol,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    /**
     * Convierte la entidad a array excluyendo el password
     */
    public function toArraySafe()
    {
        $array = $this->toArray();
        unset($array['password']);
        return $array;
    }

    /**
     * Verifica si el usuario tiene un rol específico
     */
    public function hasRole($rol)
    {
        return $this->rol === $rol;
    }

    /**
     * Obtiene las iniciales del usuario
     */
    public function getIniciales()
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
     * Verifica si la entidad es válida (campos requeridos)
     */
    public function isValid()
    {
        return !empty($this->name) && 
               !empty($this->email) && 
               !empty($this->rol);
    }

    /**
     * Crea una nueva instancia desde array de datos
     */
    public static function fromArray(array $data)
    {
        return new self($data);
    }
}

?>