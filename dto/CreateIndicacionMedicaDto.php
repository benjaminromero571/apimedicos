<?php

declare(strict_types=1);

/**
 * CreateIndicacionMedicaDto - DTO para crear nuevas indicaciones médicas
 * 
 * Este DTO se utiliza para validar y transferir datos al crear una nueva indicación.
 * El user_id se obtiene automáticamente del JWT del usuario autenticado.
 */
class CreateIndicacionMedicaDto
{
    private int $paciente_id;
    private int $user_id;
    private string $indicaciones;
    private int $created_by;

    /**
     * Constructor que valida y asigna datos
     */
    public function __construct(array $data)
    {
        // Campos requeridos
        $this->paciente_id = $this->validateIntRequired($data, 'paciente_id', 'El ID del paciente es requerido');
        $this->indicaciones = $this->validateRequired($data, 'indicaciones', 'Las indicaciones son requeridas');
        $this->user_id = $this->validateIntRequired($data, 'user_id', 'El ID del usuario es requerido');
        $this->created_by = $this->validateIntRequired($data, 'created_by', 'El ID del creador es requerido');

        $this->validateData();
    }

    /**
     * Valida campo requerido de tipo string
     */
    private function validateRequired(array $data, string $field, string $message): string
    {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            throw new InvalidArgumentException($message);
        }
        return trim($data[$field]);
    }

    /**
     * Valida campo entero requerido
     */
    private function validateIntRequired(array $data, string $field, string $message): int
    {
        if (!isset($data[$field])) {
            throw new InvalidArgumentException($message);
        }
        
        $value = filter_var($data[$field], FILTER_VALIDATE_INT);
        if ($value === false || $value <= 0) {
            throw new InvalidArgumentException($message . ' (debe ser un entero positivo)');
        }
        
        return $value;
    }

    /**
     * Validaciones adicionales de negocio
     */
    private function validateData(): void
    {
        // Validar longitud de las indicaciones
        if (strlen($this->indicaciones) < 10) {
            throw new InvalidArgumentException('Las indicaciones deben tener al menos 10 caracteres');
        }

        if (strlen($this->indicaciones) > 65535) {
            throw new InvalidArgumentException('Las indicaciones no pueden exceder 65535 caracteres');
        }
    }

    /**
     * Convierte el DTO a array para inserción en BD
     */
    public function toArray(): array
    {
        return [
            'paciente_id' => $this->paciente_id,
            'user_id' => $this->user_id,
            'indicaciones' => $this->indicaciones,
            'created_by' => $this->created_by,
            'updated_by' => $this->created_by // Al crear, updated_by es igual a created_by
        ];
    }

    // Getters
    public function getPacienteId(): int { return $this->paciente_id; }
    public function getUserId(): int { return $this->user_id; }
    public function getIndicaciones(): string { return $this->indicaciones; }
    public function getCreatedBy(): int { return $this->created_by; }
}
