<?php

declare(strict_types=1);

require_once __DIR__ . '/../entities/RecetaMedicaEntity.php';

/**
 * RecetaMedicaDto - DTO básico para transferencia de datos de recetas médicas
 * 
 * Se utiliza para respuestas de listados y resúmenes.
 * Contiene solo los campos esenciales para vistas resumidas.
 */
class RecetaMedicaDto
{
    private int $id;
    private string $detalle;
    private string $fecha;
    private int $id_medico;
    private int $id_historial;
    private ?string $nombre_medico;
    private string $created_at;

    public function __construct(
        int $id,
        string $detalle,
        string $fecha,
        int $id_medico,
        int $id_historial,
        ?string $nombre_medico = null,
        string $created_at = ''
    ) {
        $this->id = $id;
        $this->detalle = $detalle;
        $this->fecha = $fecha;
        $this->id_medico = $id_medico;
        $this->id_historial = $id_historial;
        $this->nombre_medico = $nombre_medico;
        $this->created_at = $created_at;
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
            $entity->getIdHistorial(),
            $entity->getNombreMedico(),
            $entity->getCreatedAt()
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
            'id_medico' => $this->id_medico,
            'id_historial' => $this->id_historial,
            'nombre_medico' => $this->nombre_medico,
            'created_at' => $this->created_at
        ];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getDetalle(): string { return $this->detalle; }
    public function getFecha(): string { return $this->fecha; }
    public function getIdMedico(): int { return $this->id_medico; }
    public function getIdHistorial(): int { return $this->id_historial; }
    public function getNombreMedico(): ?string { return $this->nombre_medico; }
    public function getCreatedAt(): string { return $this->created_at; }
}
