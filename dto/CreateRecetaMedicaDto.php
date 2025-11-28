<?php

declare(strict_types=1);

/**
 * CreateRecetaMedicaDto - DTO para crear nuevas recetas médicas
 * 
 * Este DTO se utiliza para validar y transferir datos al crear una nueva receta.
 * Contiene solo los campos necesarios para la creación (sin campos autogenerados).
 */
class CreateRecetaMedicaDto
{
    private string $detalle;
    private ?string $fecha;
    private int $id_medico;
    private int $id_historial;
    private int $created_by;

    /**
     * Constructor que valida y asigna datos
     */
    public function __construct(array $data)
    {
        // Campos requeridos
        $this->detalle = $this->validateRequired($data, 'detalle', 'El detalle de la receta es requerido');
        $this->id_medico = $this->validateIntRequired($data, 'id_medico', 'El ID del médico es requerido');
        $this->created_by = $this->validateIntRequired($data, 'created_by', 'El ID del creador es requerido');
        $this->id_historial = $this->validateIntRequired($data, 'id_historial', 'El ID del historial es requerido');
        
        // fecha es opcional, si no se proporciona se usará CURRENT_TIMESTAMP de MySQL
        $this->fecha = $data['fecha'] ?? null;

        $this->validateData();
    }

    /**
     * Valida campo requerido
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
        // Validar longitud del detalle
        if (strlen($this->detalle) < 10) {
            throw new InvalidArgumentException('El detalle debe tener al menos 10 caracteres');
        }

        if (strlen($this->detalle) > 255) {
            throw new InvalidArgumentException('El detalle no puede exceder 255 caracteres');
        }

        // Validar formato de fecha si se proporciona
        if ($this->fecha !== null) {
            $fecha = DateTime::createFromFormat('Y-m-d', $this->fecha);
            if (!$fecha || $fecha->format('Y-m-d') !== $this->fecha) {
                throw new InvalidArgumentException('El formato de fecha debe ser YYYY-MM-DD');
            }
            
            // Validar que la fecha no sea futura
            $hoy = new DateTime();
            if ($fecha > $hoy) {
                throw new InvalidArgumentException('La fecha de la receta no puede ser futura');
            }
        }
    }

    /**
     * Convierte el DTO a array para inserción en BD
     */
    public function toArray(): array
    {
        $data = [
            'detalle' => $this->detalle,
            'id_medico' => $this->id_medico,
            'id_historial' => $this->id_historial,
            'created_by' => $this->created_by,
            'updated_by' => $this->created_by // Al crear, updated_by es igual a created_by
        ];

        // Solo agregar fecha si se proporcionó
        if ($this->fecha !== null) {
            $data['fecha'] = $this->fecha;
        }

        return $data;
    }

    // Getters
    public function getDetalle(): string { return $this->detalle; }
    public function getFecha(): ?string { return $this->fecha; }
    public function getIdMedico(): int { return $this->id_medico; }
    public function getIdHistorial(): int { return $this->id_historial; }
    public function getCreatedBy(): int { return $this->created_by; }
}
