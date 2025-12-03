<?php

declare(strict_types=1);

/**
 * CreateHistorialCuidadorDto - DTO para crear nuevos registros de historial de cuidador
 * 
 * Este DTO se utiliza para validar y transferir datos al crear un nuevo historial.
 * Contiene solo los campos necesarios para la creación (sin campos autogenerados).
 */
class CreateHistorialCuidadorDto
{
    private ?string $fecha_historial;
    private string $detalle;
    private ?array $registro;
    private int $id_paciente;
    private int $id_cuidador;
    private int $created_by;

    /**
     * Constructor que valida y asigna datos
     */
    public function __construct(array $data)
    {
        // fecha_historial es opcional, si no se proporciona se usará CURRENT_TIMESTAMP de MySQL
        $this->fecha_historial = $data['fecha_historial'] ?? null;
        
        // Campos requeridos
        // Inicializar detalle (permitir vacío). Si necesita ser obligatorio, usar validateRequired.
        $this->detalle = isset($data['detalle']) ? trim((string)$data['detalle']) : '';
        $this->registro = isset($data['registro']) ? (is_array($data['registro']) ? $data['registro'] : []) : null;
        $this->id_paciente = $this->validateIntRequired($data, 'id_paciente', 'El ID del paciente es requerido');
        $this->id_cuidador = $this->validateIntRequired($data, 'id_cuidador', 'El ID del cuidador es requerido');
        $this->created_by = $this->validateIntRequired($data, 'created_by', 'El ID del creador es requerido');

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
        if (!isset($data[$field]) || !is_numeric($data[$field]) || (int)$data[$field] <= 0) {
            throw new InvalidArgumentException($message);
        }
        return (int)$data[$field];
    }

    /**
     * Validaciones adicionales de negocio
     */
    private function validateData(): void
    {
        // Validar longitud del detalle
        if (strlen($this->detalle) > 255) {
            throw new InvalidArgumentException('El detalle no puede exceder 255 caracteres');
        }
        // Si se proporciona detalle no vacío, exigir mínimo 5 caracteres
        if ($this->detalle !== '' && strlen($this->detalle) < 5) {
            throw new InvalidArgumentException('El detalle debe tener al menos 5 caracteres');
        }

        // Validar formato de fecha si se proporciona
        if ($this->fecha_historial !== null) {
            $fecha = \DateTime::createFromFormat('Y-m-d H:i:s', $this->fecha_historial);
            if (!$fecha) {
                $fecha = \DateTime::createFromFormat('Y-m-d', $this->fecha_historial);
                if (!$fecha) {
                    throw new InvalidArgumentException('Formato de fecha inválido. Use Y-m-d o Y-m-d H:i:s');
                }
            }
        }

        // Validar que registro sea un array si se proporciona
        if ($this->registro !== null && !is_array($this->registro)) {
            throw new InvalidArgumentException('El registro debe ser un objeto JSON válido');
        }
    }

    /**
     * Convierte el DTO a array para inserción en BD
     */
    public function toArray(): array
    {
        $data = [
            'detalle' => $this->detalle,
            'registro' => $this->registro ? json_encode($this->registro) : '{}',
            'id_paciente' => $this->id_paciente,
            'id_cuidador' => $this->id_cuidador,
            'created_by' => $this->created_by,
            'updated_by' => $this->created_by, // Al crear, updated_by es igual a created_by
        ];

        // Solo incluir fecha_historial si se proporcionó
        if ($this->fecha_historial !== null) {
            $data['fecha_historial'] = $this->fecha_historial;
        }

        return $data;
    }

    // Getters
    public function getFechaHistorial(): ?string
    {
        return $this->fecha_historial;
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

    public function getCreatedBy(): int
    {
        return $this->created_by;
    }
}
