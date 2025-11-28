<?php

/**
 * DTO para Profesional/Doctor
 * Transferencia de datos básicos de profesionales con formateo
 */
class ProfesionalDto
{
    // Nuevos campos
    public $id;
    public $nombre;
    public $telefono;
    public $documento;
    public $especialidad;
    public $id_user;
    
    // Campos de compatibilidad
    public $iddoctor;
    public $nomdoctor;
    public $teldoctor;
    public $cedoctor;
    public $espedoctor;
    
    // Datos formateados
    public $telefono_formateado;
    public $nombre_completo;

    public function __construct($data = [])
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
        
        // Generar datos formateados
        $this->telefono_formateado = $this->formatTelefono();
        $this->nombre_completo = $this->formatNombreCompleto();
    }

    /**
     * Crea desde una entidad ProfesionalEntity
     */
    public static function fromEntity($entity)
    {
        $data = $entity->toArray();
        return new static($data);
    }

    /**
     * Formatea el teléfono según estándares chilenos
     */
    protected function formatTelefono()
    {
        $tel_field = $this->telefono ?? $this->teldoctor;
        
        if (empty($tel_field)) {
            return '';
        }

        $tel = (string) $tel_field;
        
        // Limpiar el teléfono de caracteres no numéricos
        $tel = preg_replace('/[^0-9]/', '', $tel);
        
        // Si tiene 9 dígitos y empieza con 9, formato móvil: +56 9 1234 5678
        if (strlen($tel) == 9 && substr($tel, 0, 1) == '9') {
            return '+56 ' . substr($tel, 0, 1) . ' ' . substr($tel, 1, 4) . ' ' . substr($tel, 5);
        }
        
        // Si tiene 8 dígitos, teléfono fijo: +56 2 1234 5678
        if (strlen($tel) == 8) {
            return '+56 2 ' . substr($tel, 0, 4) . ' ' . substr($tel, 4);
        }
        
        // Si tiene 11 dígitos y empieza con 569, formato internacional
        if (strlen($tel) == 11 && substr($tel, 0, 3) == '569') {
            return '+56 9 ' . substr($tel, 3, 4) . ' ' . substr($tel, 7);
        }
        
        // Si no coincide con ningún formato, devolver original
        return $tel_field;
    }

    /**
     * Formatea el nombre completo con especialidad
     */
    protected function formatNombreCompleto()
    {
        $nombre = $this->nombre ?? $this->nomdoctor;
        $esp = $this->especialidad ?? $this->espedoctor;
        
        if (!empty($esp)) {
            $nombre .= ' - ' . $esp;
        }
        return $nombre;
    }

    /**
     * Obtiene solo el primer nombre
     */
    public function getPrimerNombre()
    {
        $nombre_field = $this->nombre ?? $this->nomdoctor;
        
        if (empty($nombre_field)) {
            return '';
        }
        
        $nombres = explode(' ', trim($nombre_field));
        return $nombres[0];
    }

    /**
     * Obtiene las iniciales del nombre
     */
    public function getIniciales()
    {
        $nombre_field = $this->nombre ?? $this->nomdoctor;
        
        if (empty($nombre_field)) {
            return '';
        }
        
        $nombres = explode(' ', trim($nombre_field));
        $iniciales = '';
        
        foreach ($nombres as $nombre) {
            if (!empty($nombre)) {
                $iniciales .= strtoupper(substr($nombre, 0, 1));
            }
        }
        
        return $iniciales;
    }

    /**
     * Verifica si tiene una especialidad específica
     */
    public function hasEspecialidad($especialidad)
    {
        $esp_field = $this->especialidad ?? $this->espedoctor;
        return strcasecmp($esp_field, $especialidad) === 0;
    }

    /**
     * Verifica si es móvil el teléfono
     */
    public function isTelefonoMovil()
    {
        $tel_field = $this->telefono ?? $this->teldoctor;
        
        if (empty($tel_field)) {
            return false;
        }
        
        $tel = preg_replace('/[^0-9]/', '', $tel_field);
        return strlen($tel) == 9 && substr($tel, 0, 1) == '9';
    }

    /**
     * Obtiene información básica para listados
     */
    public function getBasicInfo()
    {
        return [
            'id' => $this->id ?? $this->iddoctor,
            'nombre' => $this->nombre ?? $this->nomdoctor,
            'especialidad' => $this->especialidad ?? $this->espedoctor,
            'iniciales' => $this->getIniciales()
        ];
    }

    /**
     * Convierte a array
     */
    public function toArray()
    {
        return [
            // Nuevos campos
            'id' => $this->id,
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'telefono_formateado' => $this->telefono_formateado,
            'documento' => $this->documento,
            'especialidad' => $this->especialidad,
            'id_user' => $this->id_user,
            
            // Campos de compatibilidad
            'iddoctor' => $this->id ?? $this->iddoctor,
            'nomdoctor' => $this->nombre ?? $this->nomdoctor,
            'teldoctor' => $this->telefono ?? $this->teldoctor,
            'cedoctor' => $this->documento ?? $this->cedoctor,
            'espedoctor' => $this->especialidad ?? $this->espedoctor,
            
            // Datos calculados
            'nombre_completo' => $this->nombre_completo,
            'primer_nombre' => $this->getPrimerNombre(),
            'iniciales' => $this->getIniciales(),
            'telefono_movil' => $this->isTelefonoMovil(),
            'info_basica' => $this->getBasicInfo()
        ];
    }
}

?>