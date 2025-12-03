<?php

declare(strict_types=1);

require_once __DIR__ . '/../entities/HistorialCuidadorEntity.php';

/**
 * HistorialCuidadorDto - DTO para transferencia de datos básicos de historial de cuidador
 * 
 * Versión simplificada para listados y respuestas API que no requieren 
 * todos los detalles de la entidad.
 */
class HistorialCuidadorDto
{
    private int $id;
    private string $fecha_historial;
    private ?string $fecha_historial_timestamp;
    private string $detalle;
    private ?array $registro;
    private int $id_paciente;
    private int $id_cuidador;
    private ?string $nombre_paciente;
    private ?string $nombre_cuidador;
    private string $created_at;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->fecha_historial = $data['fecha_historial'];
        $this->fecha_historial_timestamp = $data['fecha_historial_timestamp'] ?? null;
        $this->detalle = $data['detalle'];
        $this->registro = isset($data['registro']) ? (is_array($data['registro']) ? $data['registro'] : []) : null;
        $this->id_paciente = (int)$data['id_paciente'];
        $this->id_cuidador = (int)$data['id_cuidador'];
        $this->nombre_paciente = $data['nombre_paciente'] ?? null;
        $this->nombre_cuidador = $data['nombre_cuidador'] ?? null;
        $this->created_at = $data['created_at'];
    }

    /**
     * Crea un DTO desde una entidad
     */
    public static function fromEntity(HistorialCuidadorEntity $entity): self
    {
        return new self($entity->toArray());
    }

    /**
     * Convierte el DTO a array para respuesta JSON
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
            'nombre_paciente' => $this->nombre_paciente,
            'nombre_cuidador' => $this->nombre_cuidador,
            'created_at' => $this->created_at,
        ];
    }

    // Getters
    public function getId(): int
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

    public function getNombrePaciente(): ?string
    {
        return $this->nombre_paciente;
    }

    public function getNombreCuidador(): ?string
    {
        return $this->nombre_cuidador;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }
}
