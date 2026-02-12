<?php

declare(strict_types=1);

require_once __DIR__ . '/../entities/IndicacionMedicaEntity.php';

/**
 * IndicacionMedicaDto - DTO básico para transferencia de datos de indicaciones médicas
 * 
 * Se utiliza para respuestas de listados y resúmenes.
 * Contiene solo los campos esenciales para vistas resumidas.
 */
class IndicacionMedicaDto
{
    private int $id;
    private int $paciente_id;
    private int $user_id;
    private string $indicaciones;
    private ?string $nombre_paciente;
    private ?string $nombre_user;
    private string $created_at;

    public function __construct(
        int $id,
        int $paciente_id,
        int $user_id,
        string $indicaciones,
        ?string $nombre_paciente = null,
        ?string $nombre_user = null,
        string $created_at = ''
    ) {
        $this->id = $id;
        $this->paciente_id = $paciente_id;
        $this->user_id = $user_id;
        $this->indicaciones = $indicaciones;
        $this->nombre_paciente = $nombre_paciente;
        $this->nombre_user = $nombre_user;
        $this->created_at = $created_at;
    }

    /**
     * Crea un DTO desde una entidad
     */
    public static function fromEntity(IndicacionMedicaEntity $entity): self
    {
        return new self(
            $entity->getId() ?? 0,
            $entity->getPacienteId(),
            $entity->getUserId(),
            $entity->getIndicaciones(),
            $entity->getNombrePaciente(),
            $entity->getNombreUser(),
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
            'paciente_id' => $this->paciente_id,
            'user_id' => $this->user_id,
            'indicaciones' => $this->indicaciones,
            'nombre_paciente' => $this->nombre_paciente,
            'nombre_user' => $this->nombre_user,
            'created_at' => $this->created_at
        ];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getPacienteId(): int { return $this->paciente_id; }
    public function getUserId(): int { return $this->user_id; }
    public function getIndicaciones(): string { return $this->indicaciones; }
    public function getNombrePaciente(): ?string { return $this->nombre_paciente; }
    public function getNombreUser(): ?string { return $this->nombre_user; }
    public function getCreatedAt(): string { return $this->created_at; }
}
