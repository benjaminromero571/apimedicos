<?php

declare(strict_types=1);

require_once __DIR__ . '/../entities/IndicacionMedicaEntity.php';

/**
 * IndicacionMedicaDetailDto - DTO detallado para indicaciones médicas
 * 
 * Incluye todos los campos y datos relacionados (joins).
 * Se utiliza para respuestas de detalle individual.
 */
class IndicacionMedicaDetailDto
{
    private int $id;
    private string $indicaciones;
    private int $paciente_id;
    private ?string $nombre_paciente;
    private int $user_id;
    private ?string $nombre_user;
    private ?string $email_user;
    private string $created_at;
    private ?int $created_by;
    private ?string $created_by_name;
    private string $updated_at;
    private ?int $updated_by;
    private ?string $updated_by_name;

    public function __construct(
        int $id,
        string $indicaciones,
        int $paciente_id,
        ?string $nombre_paciente,
        int $user_id,
        ?string $nombre_user,
        ?string $email_user,
        string $created_at,
        ?int $created_by,
        ?string $created_by_name,
        string $updated_at,
        ?int $updated_by,
        ?string $updated_by_name
    ) {
        $this->id = $id;
        $this->indicaciones = $indicaciones;
        $this->paciente_id = $paciente_id;
        $this->nombre_paciente = $nombre_paciente;
        $this->user_id = $user_id;
        $this->nombre_user = $nombre_user;
        $this->email_user = $email_user;
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
    public static function fromEntity(IndicacionMedicaEntity $entity): self
    {
        return new self(
            $entity->getId() ?? 0,
            $entity->getIndicaciones(),
            $entity->getPacienteId(),
            $entity->getNombrePaciente(),
            $entity->getUserId(),
            $entity->getNombreUser(),
            $entity->getEmailUser(),
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
            'indicaciones' => $this->indicaciones,
            'paciente' => [
                'id' => $this->paciente_id,
                'nombre' => $this->nombre_paciente
            ],
            'usuario' => [
                'id' => $this->user_id,
                'nombre' => $this->nombre_user,
                'email' => $this->email_user
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
    public function getIndicaciones(): string { return $this->indicaciones; }
    public function getPacienteId(): int { return $this->paciente_id; }
    public function getNombrePaciente(): ?string { return $this->nombre_paciente; }
    public function getUserId(): int { return $this->user_id; }
    public function getNombreUser(): ?string { return $this->nombre_user; }
    public function getEmailUser(): ?string { return $this->email_user; }
    public function getCreatedAt(): string { return $this->created_at; }
    public function getCreatedBy(): ?int { return $this->created_by; }
    public function getCreatedByName(): ?string { return $this->created_by_name; }
    public function getUpdatedAt(): string { return $this->updated_at; }
    public function getUpdatedBy(): ?int { return $this->updated_by; }
    public function getUpdatedByName(): ?string { return $this->updated_by_name; }
}
