<?php

declare(strict_types=1);

require_once __DIR__ . '/../entities/RecetaMedicaEntity.php';

/**
 * RecetaMedicaDetailDto - DTO detallado para recetas médicas
 * 
 * Incluye todos los campos y datos relacionados (joins).
 * Se utiliza para respuestas de detalle individual.
 */
class RecetaMedicaDetailDto
{
    private int $id;
    private string $detalle;
    private string $fecha;
    private int $id_medico;
    private ?string $nombre_medico;
    private ?string $email_medico;
    private string $created_at;
    private int $created_by;
    private ?string $created_by_name;
    private string $updated_at;
    private int $updated_by;
    private ?string $updated_by_name;

    public function __construct(
        int $id,
        string $detalle,
        string $fecha,
        int $id_medico,
        ?string $nombre_medico,
        ?string $email_medico,
        string $created_at,
        int $created_by,
        ?string $created_by_name,
        string $updated_at,
        int $updated_by,
        ?string $updated_by_name
    ) {
        $this->id = $id;
        $this->detalle = $detalle;
        $this->fecha = $fecha;
        $this->id_medico = $id_medico;
        $this->nombre_medico = $nombre_medico;
        $this->email_medico = $email_medico;
        $this->created_at = $created_at;
        $this->created_by = $created_by;
        $this->created_by_name = $created_by_name;
        $this->updated_at = $updated_at;
        $this->updated_by = $updated_by;
        $this->updated_by_name = $updated_by_name;
    }

    /**
     * Crea un DTO desde una entidad
     */
    public static function fromEntity(RecetaMedicaEntity $entity): self
    {
        return new self(
            $entity->getId() ?? 0,
            $entity->getDetalle(),
            $entity->getFecha(),
            $entity->getIdMedico(),
            $entity->getNombreMedico(),
            $entity->getEmailMedico(),
            $entity->getCreatedAt(),
            $entity->getCreatedBy(),
            $entity->getCreatedByName(),
            $entity->getUpdatedAt(),
            $entity->getUpdatedBy(),
            $entity->getUpdatedByName()
        );
    }

    /**
     * Convierte el DTO a array para respuestas JSON
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'detalle' => $this->detalle,
            'fecha' => $this->fecha,
            'medico' => [
                'id' => $this->id_medico,
                'nombre' => $this->nombre_medico,
                'email' => $this->email_medico
            ],
            'auditoria' => [
                'created_at' => $this->created_at,
                'created_by' => $this->created_by,
                'created_by_name' => $this->created_by_name,
                'updated_at' => $this->updated_at,
                'updated_by' => $this->updated_by,
                'updated_by_name' => $this->updated_by_name
            ]
        ];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getDetalle(): string { return $this->detalle; }
    public function getFecha(): string { return $this->fecha; }
    public function getIdMedico(): int { return $this->id_medico; }
    public function getNombreMedico(): ?string { return $this->nombre_medico; }
    public function getEmailMedico(): ?string { return $this->email_medico; }
    public function getCreatedAt(): string { return $this->created_at; }
    public function getCreatedBy(): int { return $this->created_by; }
    public function getCreatedByName(): ?string { return $this->created_by_name; }
    public function getUpdatedAt(): string { return $this->updated_at; }
    public function getUpdatedBy(): int { return $this->updated_by; }
    public function getUpdatedByName(): ?string { return $this->updated_by_name; }
}
