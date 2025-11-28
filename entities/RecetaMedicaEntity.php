<?php

declare(strict_types=1);

/**
 * Entidad RecetaMedica - Representación pura de datos de recetas médicas
 * 
 * Esta entidad representa las recetas médicas emitidas por los médicos
 * para los pacientes del sistema.
 * 
 * Arquitectura: Solo contiene propiedades y métodos básicos de acceso.
 * NO contiene lógica de negocio ni acceso a datos.
 */
class RecetaMedicaEntity
{
    private ?int $id;
    private string $detalle;
    private string $fecha;
    private int $id_medico;
    
    // Campos de auditoría
    private string $created_at;
    private int $created_by;
    private string $updated_at;
    private int $updated_by;
    
    // Campos relacionados (joins)
    private ?string $nombre_medico = null;
    private ?string $email_medico = null;
    private ?string $created_by_name = null;
    private ?string $updated_by_name = null;

    /**
     * Constructor que acepta un array de datos
     */
    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    /**
     * Rellena la entidad con datos del array
     * Útil para mapear resultados de base de datos
     */
    public function fill(array $data): void
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->detalle = $data['detalle'] ?? '';
        $this->fecha = $data['fecha'] ?? date('Y-m-d');
        $this->id_medico = isset($data['id_medico']) ? (int)$data['id_medico'] : 0;
        
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->created_by = isset($data['created_by']) ? (int)$data['created_by'] : 0;
        $this->updated_at = $data['updated_at'] ?? date('Y-m-d H:i:s');
        $this->updated_by = isset($data['updated_by']) ? (int)$data['updated_by'] : 0;
        
        // Campos relacionados opcionales
        $this->nombre_medico = $data['nombre_medico'] ?? null;
        $this->email_medico = $data['email_medico'] ?? null;
        $this->created_by_name = $data['created_by_name'] ?? null;
        $this->updated_by_name = $data['updated_by_name'] ?? null;
    }

    /**
     * Convierte la entidad a un array asociativo
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'detalle' => $this->detalle,
            'fecha' => $this->fecha,
            'id_medico' => $this->id_medico,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            'nombre_medico' => $this->nombre_medico,
            'email_medico' => $this->email_medico,
            'created_by_name' => $this->created_by_name,
            'updated_by_name' => $this->updated_by_name
        ];
    }

    // ============ Getters ============
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDetalle(): string
    {
        return $this->detalle;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function getIdMedico(): int
    {
        return $this->id_medico;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function getCreatedBy(): int
    {
        return $this->created_by;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    public function getUpdatedBy(): int
    {
        return $this->updated_by;
    }

    public function getNombreMedico(): ?string
    {
        return $this->nombre_medico;
    }

    public function getEmailMedico(): ?string
    {
        return $this->email_medico;
    }

    public function getCreatedByName(): ?string
    {
        return $this->created_by_name;
    }

    public function getUpdatedByName(): ?string
    {
        return $this->updated_by_name;
    }

    // ============ Setters ============
    
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setDetalle(string $detalle): void
    {
        $this->detalle = $detalle;
    }

    public function setFecha(string $fecha): void
    {
        $this->fecha = $fecha;
    }

    public function setIdMedico(int $id_medico): void
    {
        $this->id_medico = $id_medico;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function setCreatedBy(int $created_by): void
    {
        $this->created_by = $created_by;
    }

    public function setUpdatedAt(string $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    public function setUpdatedBy(int $updated_by): void
    {
        $this->updated_by = $updated_by;
    }

    public function setNombreMedico(?string $nombre_medico): void
    {
        $this->nombre_medico = $nombre_medico;
    }

    public function setEmailMedico(?string $email_medico): void
    {
        $this->email_medico = $email_medico;
    }

    public function setCreatedByName(?string $created_by_name): void
    {
        $this->created_by_name = $created_by_name;
    }

    public function setUpdatedByName(?string $updated_by_name): void
    {
        $this->updated_by_name = $updated_by_name;
    }

    /**
     * Valida que la entidad tenga datos válidos
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return !empty($this->detalle) 
            && !empty($this->fecha)
            && $this->id_medico > 0;
    }

    /**
     * Retorna los errores de validación
     * 
     * @return array
     */
    public function getValidationErrors(): array
    {
        $errors = [];
        
        if (empty($this->detalle)) {
            $errors[] = 'El detalle de la receta es requerido';
        }
        
        if (empty($this->fecha)) {
            $errors[] = 'La fecha de la receta es requerida';
        }
        
        if ($this->id_medico <= 0) {
            $errors[] = 'El ID del médico es inválido';
        }
        
        return $errors;
    }
}
