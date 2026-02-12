<?php

declare(strict_types=1);

/**
 * Entidad IndicacionMedica - Representación pura de datos de indicaciones médicas
 * 
 * Esta entidad representa las indicaciones médicas emitidas por médicos/profesionales
 * para los pacientes del sistema.
 * 
 * Arquitectura: Solo contiene propiedades y métodos básicos de acceso.
 * NO contiene lógica de negocio ni acceso a datos.
 */
class IndicacionMedicaEntity
{
    private ?int $id;
    private int $paciente_id;
    private int $user_id;
    private string $indicaciones;
    
    // Campos de auditoría
    private string $created_at;
    private ?int $created_by;
    private string $updated_at;
    private ?int $updated_by;
    
    // Campos relacionados (joins)
    private ?string $nombre_paciente = null;
    private ?string $nombre_user = null;
    private ?string $email_user = null;
    private ?string $created_by_name = null;
    private ?string $updated_by_name = null;

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data): void
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->paciente_id = isset($data['paciente_id']) ? (int)$data['paciente_id'] : 0;
        $this->user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
        $this->indicaciones = $data['indicaciones'] ?? '';
        
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->created_by = isset($data['created_by']) ? (int)$data['created_by'] : null;
        $this->updated_at = $data['updated_at'] ?? date('Y-m-d H:i:s');
        $this->updated_by = isset($data['updated_by']) ? (int)$data['updated_by'] : null;
        
        $this->nombre_paciente = $data['nombre_paciente'] ?? null;
        $this->nombre_user = $data['nombre_user'] ?? null;
        $this->email_user = $data['email_user'] ?? null;
        $this->created_by_name = $data['created_by_name'] ?? null;
        $this->updated_by_name = $data['updated_by_name'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'paciente_id' => $this->paciente_id,
            'user_id' => $this->user_id,
            'indicaciones' => $this->indicaciones,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            'nombre_paciente' => $this->nombre_paciente,
            'nombre_user' => $this->nombre_user,
            'email_user' => $this->email_user,
            'created_by_name' => $this->created_by_name,
            'updated_by_name' => $this->updated_by_name
        ];
    }

    // ============ Getters ============
    public function getId(): ?int { return $this->id; }
    public function getPacienteId(): int { return $this->paciente_id; }
    public function getUserId(): int { return $this->user_id; }
    public function getIndicaciones(): string { return $this->indicaciones; }
    public function getCreatedAt(): string { return $this->created_at; }
    public function getCreatedBy(): ?int { return $this->created_by; }
    public function getUpdatedAt(): string { return $this->updated_at; }
    public function getUpdatedBy(): ?int { return $this->updated_by; }
    public function getNombrePaciente(): ?string { return $this->nombre_paciente; }
    public function getNombreUser(): ?string { return $this->nombre_user; }
    public function getEmailUser(): ?string { return $this->email_user; }
    public function getCreatedByName(): ?string { return $this->created_by_name; }
    public function getUpdatedByName(): ?string { return $this->updated_by_name; }

    // ============ Setters ============
    public function setId(?int $id): void { $this->id = $id; }
    public function setPacienteId(int $paciente_id): void { $this->paciente_id = $paciente_id; }
    public function setUserId(int $user_id): void { $this->user_id = $user_id; }
    public function setIndicaciones(string $indicaciones): void { $this->indicaciones = $indicaciones; }
    public function setCreatedAt(string $created_at): void { $this->created_at = $created_at; }
    public function setCreatedBy(?int $created_by): void { $this->created_by = $created_by; }
    public function setUpdatedAt(string $updated_at): void { $this->updated_at = $updated_at; }
    public function setUpdatedBy(?int $updated_by): void { $this->updated_by = $updated_by; }
    public function setNombrePaciente(?string $nombre_paciente): void { $this->nombre_paciente = $nombre_paciente; }
    public function setNombreUser(?string $nombre_user): void { $this->nombre_user = $nombre_user; }
    public function setEmailUser(?string $email_user): void { $this->email_user = $email_user; }
    public function setCreatedByName(?string $created_by_name): void { $this->created_by_name = $created_by_name; }
    public function setUpdatedByName(?string $updated_by_name): void { $this->updated_by_name = $updated_by_name; }

    public function isValid(): bool
    {
        return !empty($this->indicaciones) 
            && $this->paciente_id > 0
            && $this->user_id > 0;
    }

    public function getValidationErrors(): array
    {
        $errors = [];
        if (empty($this->indicaciones)) { $errors[] = 'Las indicaciones son requeridas'; }
        if ($this->paciente_id <= 0) { $errors[] = 'El ID del paciente es inválido'; }
        if ($this->user_id <= 0) { $errors[] = 'El ID del usuario es inválido'; }
        return $errors;
    }
}
