<?php

declare(strict_types=1);

require_once __DIR__ . '/../entities/HistorialCuidadorEntity.php';

/**
 * HistorialCuidadorDetailDto - DTO para detalles completos de un historial de cuidador
 * 
 * Incluye toda la información disponible, campos de auditoría y relaciones.
 * Se utiliza para vistas detalladas individuales.
 */
class HistorialCuidadorDetailDto
{
    private int $id;
    private string $fecha_historial;
    private string $detalle;
    private int $id_paciente;
    private int $id_cuidador;
    private ?string $nombre_paciente;
    private ?string $nombre_cuidador;
    
    // Campos de auditoría completos
    private string $created_at;
    private int $created_by;
    private ?string $created_by_name;
    private string $updated_at;
    private int $updated_by;
    private ?string $updated_by_name;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->fecha_historial = $data['fecha_historial'];
        $this->detalle = $data['detalle'];
        $this->id_paciente = (int)$data['id_paciente'];
        $this->id_cuidador = (int)$data['id_cuidador'];
        $this->nombre_paciente = $data['nombre_paciente'] ?? null;
        $this->nombre_cuidador = $data['nombre_cuidador'] ?? null;
        
        $this->created_at = $data['created_at'];
        $this->created_by = (int)$data['created_by'];
        $this->created_by_name = $data['created_by_name'] ?? null;
        $this->updated_at = $data['updated_at'];
        $this->updated_by = (int)$data['updated_by'];
        $this->updated_by_name = $data['updated_by_name'] ?? null;
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
            'detalle' => $this->detalle,
            'paciente' => [
                'id' => $this->id_paciente,
                'nombre' => $this->nombre_paciente,
            ],
            'cuidador' => [
                'id' => $this->id_cuidador,
                'nombre' => $this->nombre_cuidador,
            ],
            'auditoria' => [
                'created_at' => $this->created_at,
                'created_by' => [
                    'id' => $this->created_by,
                    'nombre' => $this->created_by_name,
                ],
                'updated_at' => $this->updated_at,
                'updated_by' => [
                    'id' => $this->updated_by,
                    'nombre' => $this->updated_by_name,
                ],
            ],
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

    public function getDetalle(): string
    {
        return $this->detalle;
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

    public function getCreatedBy(): int
    {
        return $this->created_by;
    }

    public function getCreatedByName(): ?string
    {
        return $this->created_by_name;
    }

    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    public function getUpdatedBy(): int
    {
        return $this->updated_by;
    }

    public function getUpdatedByName(): ?string
    {
        return $this->updated_by_name;
    }
}
