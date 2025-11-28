<?php

/**
 * CreateUserDto
 * 
 * DTO para validación de datos al crear nuevos usuarios
 * Incluye validaciones específicas para el contexto de la aplicación
 */
class CreateUserDto
{
    public $name;
    public $email;
    public $password;
    public $rol;
    
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
     * Llena el DTO con datos
     */
    public function fill($data)
    {
        if (is_array($data)) {
            $this->name = isset($data['name']) ? trim($data['name']) : null;
            $this->email = isset($data['email']) ? trim(strtolower($data['email'])) : null;
            $this->password = $data['password'] ?? null;
            $this->rol = $data['rol'] ?? null;
        }
    }

    /**
     * Valida los datos del usuario
     */
    public function validate()
    {
        $this->errors = [];

        $this->validateName();
        $this->validateEmail();
        $this->validatePassword();
        $this->validateRole();

        return empty($this->errors);
    }

    /**
     * Valida el nombre
     */
    private function validateName()
    {
        if (empty($this->name)) {
            $this->errors['name'] = 'El nombre es requerido';
            return;
        }

        if (strlen($this->name) < 2) {
            $this->errors['name'] = 'El nombre debe tener al menos 2 caracteres';
            return;
        }

        if (strlen($this->name) > 100) {
            $this->errors['name'] = 'El nombre no puede exceder 100 caracteres';
            return;
        }

        // Validar caracteres permitidos (letras, espacios, acentos, guiones)
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\.]+$/', $this->name)) {
            $this->errors['name'] = 'El nombre solo puede contener letras, espacios y guiones';
            return;
        }

        // Normalizar espacios múltiples
        $this->name = preg_replace('/\s+/', ' ', $this->name);
        $this->name = trim($this->name);
    }

    /**
     * Valida el email
     */
    private function validateEmail()
    {
        if (empty($this->email)) {
            $this->errors['email'] = 'El email es requerido';
            return;
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'El email no tiene un formato válido';
            return;
        }

        if (strlen($this->email) > 255) {
            $this->errors['email'] = 'El email no puede exceder 255 caracteres';
            return;
        }

        // Validar dominio común
        $allowedDomains = [
            'gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com', 
            'empresa.cl', 'salud.cl', 'hospital.cl'
        ];
        
        $domain = substr(strrchr($this->email, "@"), 1);
        if (!empty($allowedDomains)) {
            // Comentado para permitir cualquier dominio
            // Descomentar si se quiere restringir dominios
            // if (!in_array($domain, $allowedDomains)) {
            //     $this->errors['email'] = 'El dominio del email no está permitido';
            //     return;
            // }
        }
    }

    /**
     * Valida la contraseña
     */
    private function validatePassword()
    {
        if (empty($this->password)) {
            $this->errors['password'] = 'La contraseña es requerida';
            return;
        }

        if (strlen($this->password) < 6) {
            $this->errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
            return;
        }

        if (strlen($this->password) > 255) {
            $this->errors['password'] = 'La contraseña no puede exceder 255 caracteres';
            return;
        }

        // Validaciones de seguridad
        $hasLetter = preg_match('/[a-zA-Z]/', $this->password);
        $hasNumber = preg_match('/\d/', $this->password);
        
        if (!$hasLetter) {
            $this->errors['password'] = 'La contraseña debe contener al menos una letra';
            return;
        }

        if (!$hasNumber) {
            $this->errors['password'] = 'La contraseña debe contener al menos un número';
            return;
        }

        // Verificar contraseñas comunes débiles
        $weakPasswords = [
            '123456', 'password', '123456789', 'qwerty', 
            'abc123', 'password123', '123123', 'admin'
        ];
        
        if (in_array(strtolower($this->password), $weakPasswords)) {
            $this->errors['password'] = 'La contraseña es demasiado común, por favor elige otra';
            return;
        }
    }

    /**
     * Valida el rol
     */
    private function validateRole()
    {
        if (empty($this->rol)) {
            $this->errors['rol'] = 'El rol es requerido';
            return;
        }

        $validRoles = ['Administrador', 'Medico', 'Profesional', 'Cuidador'];
        if (!in_array($this->rol, $validRoles)) {
            $this->errors['rol'] = 'El rol debe ser: ' . implode(', ', $validRoles);
            return;
        }
    }

    /**
     * Hashea la contraseña
     */
    public function hashPassword()
    {
        if (!empty($this->password)) {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        }
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
     * Convierte a array para inserción en BD
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'rol' => $this->rol
        ];
    }

    /**
     * Crea un CreateUserDto desde un array
     */
    public static function fromArray(array $data)
    {
        return new self($data);
    }

    /**
     * Obtiene los roles disponibles
     */
    public static function getAvailableRoles()
    {
        return [
            'Administrador' => 'Administrador del Sistema',
            'Medico' => 'Médico',
            'Profesional' => 'Profesional de la Salud',
            'Cuidador' => 'Cuidador o Familiar'
        ];
    }

    /**
     * Obtiene mensajes de validación personalizados
     */
    public function getValidationMessages()
    {
        return [
            'name_required' => 'El nombre completo es obligatorio',
            'email_required' => 'El correo electrónico es obligatorio',
            'password_required' => 'La contraseña es obligatoria',
            'rol_required' => 'Debe seleccionar un rol',
            'email_unique' => 'Este correo ya está registrado',
            'password_strength' => 'Use una contraseña con al menos 6 caracteres, letras y números'
        ];
    }
}

?>