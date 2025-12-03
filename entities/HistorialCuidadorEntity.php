<?php

declare(strict_types=1);

/**
 * Entidad HistorialCuidador - Representación pura de datos de historial de cuidadores
 * 
 * Esta entidad representa los registros diarios que los cuidadores realizan
 * sobre las actividades y observaciones de los pacientes.
 * 
 * Arquitectura: Solo contiene propiedades y métodos básicos de acceso.
 * NO contiene lógica de negocio ni acceso a datos.
 */
class HistorialCuidadorEntity
{
    private ?int $id;
    private string $fecha_historial;
    private ?string $fecha_historial_timestamp;
    private string $detalle;
    private ?array $registro;
    private int $id_paciente;
    private int $id_cuidador;
    
    // Campos de auditoría
    private string $created_at;
    private int $created_by;
    private string $updated_at;
    private int $updated_by;
    
    // Campos relacionados (joins)
    private ?string $nombre_paciente = null;
    private ?string $nombre_cuidador = null;
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
        $this->fecha_historial = $data['fecha_historial'] ?? '';
        $this->fecha_historial_timestamp = $data['fecha_historial_timestamp'] ?? null;
        $this->detalle = $data['detalle'] ?? '';
        $this->registro = isset($data['registro']) ? (is_string($data['registro']) ? json_decode($data['registro'], true) : $data['registro']) : null;
        $this->id_paciente = isset($data['id_paciente']) ? (int)$data['id_paciente'] : 0;
        $this->id_cuidador = isset($data['id_cuidador']) ? (int)$data['id_cuidador'] : 0;
        
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->created_by = isset($data['created_by']) ? (int)$data['created_by'] : 0;
        $this->updated_at = $data['updated_at'] ?? date('Y-m-d H:i:s');
        $this->updated_by = isset($data['updated_by']) ? (int)$data['updated_by'] : 0;
        
        // Campos relacionados opcionales
        $this->nombre_paciente = $data['nombre_paciente'] ?? null;
        $this->nombre_cuidador = $data['nombre_cuidador'] ?? null;
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
            'fecha_historial' => $this->fecha_historial,
            'fecha_historial_timestamp' => $this->fecha_historial_timestamp,
            'detalle' => $this->detalle,
            'registro' => $this->registro,
            'id_paciente' => $this->id_paciente,
            'id_cuidador' => $this->id_cuidador,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            'nombre_paciente' => $this->nombre_paciente,
            'nombre_cuidador' => $this->nombre_cuidador,
            'created_by_name' => $this->created_by_name,
            'updated_by_name' => $this->updated_by_name,
        ];
    }

    // ========== GETTERS ==========

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaHistorial(): string
    {
        return $this->fecha_historial;
    }

    public function getFechaHistorialTimestamp(): ?string
    {
        return $this->fecha_historial_timestamp;
    }

    public function getDetalle(): string
    {
        return $this->detalle;
    }

    public function getRegistro(): ?array
    {
        return $this->registro;
    }

    public function getIdPaciente(): int
    {
        return $this->id_paciente;
    }

    public function getIdCuidador(): int
    {
        return $this->id_cuidador;
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

    public function getNombrePaciente(): ?string
    {
        return $this->nombre_paciente;
    }

    public function getNombreCuidador(): ?string
    {
        return $this->nombre_cuidador;
    }

    public function getCreatedByName(): ?string
    {
        return $this->created_by_name;
    }

    public function getUpdatedByName(): ?string
    {
        return $this->updated_by_name;
    }

    // ========== SETTERS ==========

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setFechaHistorial(string $fecha_historial): void
    {
        $this->fecha_historial = $fecha_historial;
    }

    public function setFechaHistorialTimestamp(?string $fecha_historial_timestamp): void
    {
        $this->fecha_historial_timestamp = $fecha_historial_timestamp;
    }

    public function setDetalle(string $detalle): void
    {
        $this->detalle = $detalle;
    }

    public function setRegistro(?array $registro): void
    {
        $this->registro = $registro;
    }

    public function setIdPaciente(int $id_paciente): void
    {
        $this->id_paciente = $id_paciente;
    }

    public function setIdCuidador(int $id_cuidador): void
    {
        $this->id_cuidador = $id_cuidador;
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

    public function setNombrePaciente(?string $nombre_paciente): void
    {
        $this->nombre_paciente = $nombre_paciente;
    }

    public function setNombreCuidador(?string $nombre_cuidador): void
    {
        $this->nombre_cuidador = $nombre_cuidador;
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
     * Valida si la entidad tiene los datos mínimos requeridos
     */
    public function isValid(): bool
    {
        return !empty($this->detalle) && 
               $this->id_paciente > 0 && 
               $this->id_cuidador > 0 &&
               !empty($this->fecha_historial);
    }
}
