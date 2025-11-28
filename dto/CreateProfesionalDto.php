<?php

/**
 * DTO para crear nuevos profesionales
 * Contiene validaciones específicas para datos de profesionales médicos
 */
class CreateProfesionalDto
{
    // Nuevos campos
    public $nombre;
    public $telefono;
    public $documento;
    public $especialidad;
    public $id_user;

    // Campos de compatibilidad
    public $nomdoctor;
    public $teldoctor;
    public $cedoctor;
    public $espedoctor;

    public function __construct($data = [])
    {
        // Nuevos campos
        $this->nombre = $data['nombre'] ?? null;
        $this->telefono = $data['telefono'] ?? null;
        $this->documento = $data['documento'] ?? null;
        $this->especialidad = $data['especialidad'] ?? null;
        $this->id_user = $data['id_user'] ?? null;

        // Compatibilidad con campos anteriores
        $this->nomdoctor = $data['nomdoctor'] ?? $this->nombre;
        $this->teldoctor = $data['teldoctor'] ?? $this->telefono;
        $this->cedoctor = $data['cedoctor'] ?? $this->documento;
        $this->espedoctor = $data['espedoctor'] ?? $this->especialidad;

        // Si vienen datos con nombres antiguos, mapear a nuevos
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

    /**
     * Valida los datos para crear un nuevo profesional
     */
    public function validate()
    {
        $errors = [];

        // Validaciones obligatorias
        if (empty($this->nombre)) {
            $errors[] = 'Nombre del profesional es requerido';
        } elseif (strlen(trim($this->nombre)) < 3) {
            $errors[] = 'El nombre debe tener al menos 3 caracteres';
        } elseif (strlen(trim($this->nombre)) > 100) {
            $errors[] = 'El nombre no puede exceder los 100 caracteres';
        }

        if (empty($this->documento)) {
            $errors[] = 'Documento profesional es requerido';
        } elseif (strlen(trim($this->documento)) < 5) {
            $errors[] = 'El documento debe tener al menos 5 caracteres';
        } elseif (strlen(trim($this->documento)) > 20) {
            $errors[] = 'El documento no puede exceder los 20 caracteres';
        }

        if (empty($this->especialidad)) {
            $errors[] = 'Especialidad es requerida';
        } elseif (strlen(trim($this->especialidad)) < 3) {
            $errors[] = 'La especialidad debe tener al menos 3 caracteres';
        } elseif (strlen(trim($this->especialidad)) > 50) {
            $errors[] = 'La especialidad no puede exceder los 50 caracteres';
        }

        // Validar id_user si se proporciona
        if (!empty($this->id_user)) {
            if (!is_numeric($this->id_user) || $this->id_user <= 0) {
                $errors[] = 'ID de usuario debe ser un número válido';
            } else {
                // Validar que el usuario existe
                require_once __DIR__ . '/../repositories/UserRepository.php';
                $userRepo = new UserRepository();
                if (!$userRepo->exists($this->id_user)) {
                    $errors[] = 'El usuario especificado no existe';
                }
            }
        }

        // Validaciones de teléfono
        if (!empty($this->telefono)) {
            $telefonoLimpio = preg_replace('/[^0-9]/', '', $this->telefono);
            
            if (empty($telefonoLimpio)) {
                $errors[] = 'El teléfono debe contener números';
            } elseif (strlen($telefonoLimpio) < 8 || strlen($telefonoLimpio) > 11) {
                $errors[] = 'El teléfono debe tener entre 8 y 11 dígitos';
            }
            
            // Validar formato chileno específico
            if (!$this->isValidChileanPhone($telefonoLimpio)) {
                $errors[] = 'Formato de teléfono chileno inválido';
            }
        }

        // Validaciones de nombre (solo letras, espacios, algunos caracteres especiales)
        if (!empty($this->nombre)) {
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\'\.]+$/u', $this->nombre)) {
                $errors[] = 'El nombre solo puede contener letras, espacios, guiones y apostrofes';
            }
        }

        // Validaciones de especialidad
        if (!empty($this->especialidad)) {
            $especialidadesValidas = $this->getEspecialidadesValidas();
            if (!in_array(strtolower(trim($this->especialidad)), array_map('strtolower', $especialidadesValidas))) {
                // Solo advertencia, no error crítico
                // $errors[] = 'Especialidad no reconocida. Especialidades válidas: ' . implode(', ', $especialidadesValidas);
            }
        }

        return $errors;
    }

    /**
     * Valida formato de teléfono chileno
     */
    private function isValidChileanPhone($telefono)
    {
        // Móvil: 9 dígitos empezando con 9
        if (strlen($telefono) == 9 && substr($telefono, 0, 1) == '9') {
            return true;
        }
        
        // Fijo: 8 dígitos
        if (strlen($telefono) == 8) {
            return true;
        }
        
        // Internacional: 11 dígitos empezando con 569
        if (strlen($telefono) == 11 && substr($telefono, 0, 3) == '569') {
            return true;
        }
        
        return false;
    }

    /**
     * Lista de especialidades médicas comunes en Chile
     */
    private function getEspecialidadesValidas()
    {
        return [
            'Medicina General',
            'Medicina Familiar',
            'Cardiología',
            'Neurología',
            'Traumatología',
            'Ginecología',
            'Obstetricia',
            'Pediatría',
            'Psiquiatría',
            'Dermatología',
            'Oftalmología',
            'Otorrinolaringología',
            'Urología',
            'Gastroenterología',
            'Endocrinología',
            'Oncología',
            'Radiología',
            'Anestesiología',
            'Medicina Intensiva',
            'Geriatría',
            'Reumatología',
            'Nefrología',
            'Neumología',
            'Hematología',
            'Infectología',
            'Medicina Nuclear',
            'Patología',
            'Medicina Física y Rehabilitación',
            'Cirugía General',
            'Cirugía Vascular',
            'Neurocirugía',
            'Cirugía Plástica',
            'Cirugía Torácica',
            'Cirugía Pediátrica'
        ];
    }

    /**
     * Sanitiza los datos de entrada
     */
    public function sanitize()
    {
        // Limpiar y formatear nombre
        if (!empty($this->nombre)) {
            $this->nombre = trim($this->nombre);
            $this->nombre = preg_replace('/\s+/', ' ', $this->nombre); // Múltiples espacios a uno
            $this->nombre = ucwords(strtolower($this->nombre)); // Capitalizar palabras
            $this->nomdoctor = $this->nombre; // Mantener compatibilidad
        }
        
        // Limpiar documento
        if (!empty($this->documento)) {
            $this->documento = strtoupper(trim($this->documento));
            $this->cedoctor = $this->documento; // Mantener compatibilidad
        }
        
        // Limpiar especialidad
        if (!empty($this->especialidad)) {
            $this->especialidad = trim($this->especialidad);
            $this->especialidad = ucwords(strtolower($this->especialidad));
            $this->espedoctor = $this->especialidad; // Mantener compatibilidad
        }
        
        // Limpiar teléfono (mantener solo números)
        if (!empty($this->telefono)) {
            $this->telefono = preg_replace('/[^0-9]/', '', $this->telefono);
            $this->teldoctor = $this->telefono; // Mantener compatibilidad
        }

        // Limpiar id_user
        if (!empty($this->id_user)) {
            $this->id_user = (int)$this->id_user;
        }
    }

    /**
     * Convierte a array para el repositorio
     */
    public function toArray()
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
     * Convierte a array con compatibilidad
     */
    public function toArrayCompat()
    {
        return [
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'documento' => $this->documento,
            'especialidad' => $this->especialidad,
            'id_user' => $this->id_user,
            // Compatibilidad
            'nomdoctor' => $this->nombre,
            'teldoctor' => $this->telefono,
            'cedoctor' => $this->documento,
            'espedoctor' => $this->especialidad
        ];
    }

    /**
     * Verifica si los datos son válidos para crear el profesional
     */
    public function isValid()
    {
        return empty($this->validate());
    }

    /**
     * Obtiene mensajes de validación como string
     */
    public function getValidationMessage()
    {
        $errors = $this->validate();
        return empty($errors) ? '' : implode('; ', $errors);
    }

    /**
     * Verifica si la especialidad es reconocida
     */
    public function isEspecialidadReconocida()
    {
        if (empty($this->especialidad)) {
            return false;
        }
        
        $especialidadesValidas = $this->getEspecialidadesValidas();
        return in_array(strtolower(trim($this->especialidad)), array_map('strtolower', $especialidadesValidas));
    }

    /**
     * Obtiene sugerencias de especialidades similares
     */
    public function getSugerenciasEspecialidad()
    {
        if (empty($this->especialidad)) {
            return [];
        }
        
        $especialidadesValidas = $this->getEspecialidadesValidas();
        $input = strtolower(trim($this->especialidad));
        $sugerencias = [];
        
        foreach ($especialidadesValidas as $especialidad) {
            $especialidadLower = strtolower($especialidad);
            
            // Buscar coincidencias parciales
            if (strpos($especialidadLower, $input) !== false || 
                strpos($input, $especialidadLower) !== false) {
                $sugerencias[] = $especialidad;
            }
        }
        
        return array_slice($sugerencias, 0, 5); // Máximo 5 sugerencias
    }
}

?>