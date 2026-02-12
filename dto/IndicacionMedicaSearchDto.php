<?php

declare(strict_types=1);

/**
 * IndicacionMedicaSearchDto - DTO para búsquedas avanzadas de indicaciones médicas
 * 
 * Permite filtrar indicaciones por múltiples criterios.
 * Todos los campos son opcionales.
 */
class IndicacionMedicaSearchDto
{
    private ?int $paciente_id;
    private ?int $user_id;
    private ?string $fecha_desde;
    private ?string $fecha_hasta;
    private ?string $indicaciones_busqueda;
    private ?int $limit;
    private ?int $offset;

    /**
     * Constructor que valida y asigna filtros de búsqueda
     */
    public function __construct(array $filters = [])
    {
        $this->paciente_id = isset($filters['paciente_id']) && $filters['paciente_id'] !== '' 
            ? $this->validatePositiveInt($filters['paciente_id'], 'paciente_id') 
            : null;

        $this->user_id = isset($filters['user_id']) && $filters['user_id'] !== ''
            ? $this->validatePositiveInt($filters['user_id'], 'user_id')
            : null;

        $this->fecha_desde = isset($filters['fecha_desde']) && $filters['fecha_desde'] !== '' 
            ? $this->validateDate($filters['fecha_desde'], 'fecha_desde') 
            : null;

        $this->fecha_hasta = isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== '' 
            ? $this->validateDate($filters['fecha_hasta'], 'fecha_hasta') 
            : null;

        $this->indicaciones_busqueda = isset($filters['indicaciones']) && $filters['indicaciones'] !== '' 
            ? trim($filters['indicaciones']) 
            : null;

        $this->limit = isset($filters['limit']) && $filters['limit'] !== '' 
            ? $this->validatePositiveInt($filters['limit'], 'limit') 
            : null;

        $this->offset = isset($filters['offset']) && $filters['offset'] !== '' 
            ? $this->validateNonNegativeInt($filters['offset'], 'offset') 
            : 0;

        $this->validateDateRange();
    }

    /**
     * Valida entero positivo
     */
    private function validatePositiveInt($value, string $field): int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        if ($int === false || $int <= 0) {
            throw new InvalidArgumentException("El campo {$field} debe ser un entero positivo");
        }
        return $int;
    }

    /**
     * Valida entero no negativo (puede ser 0)
     */
    private function validateNonNegativeInt($value, string $field): int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        if ($int === false || $int < 0) {
            throw new InvalidArgumentException("El campo {$field} debe ser un entero no negativo");
        }
        return $int;
    }

    /**
     * Valida formato de fecha
     */
    private function validateDate(string $date, string $field): string
    {
        $fecha = DateTime::createFromFormat('Y-m-d', $date);
        if (!$fecha || $fecha->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException("El campo {$field} debe tener formato YYYY-MM-DD");
        }
        return $date;
    }

    /**
     * Valida que fecha_desde sea menor o igual a fecha_hasta
     */
    private function validateDateRange(): void
    {
        if ($this->fecha_desde && $this->fecha_hasta) {
            $desde = new DateTime($this->fecha_desde);
            $hasta = new DateTime($this->fecha_hasta);
            
            if ($desde > $hasta) {
                throw new InvalidArgumentException('La fecha_desde debe ser menor o igual a fecha_hasta');
            }
        }
    }

    /**
     * Verifica si hay algún filtro activo
     */
    public function hasFilters(): bool
    {
        return $this->paciente_id !== null 
            || $this->user_id !== null
            || $this->fecha_desde !== null 
            || $this->fecha_hasta !== null
            || $this->indicaciones_busqueda !== null;
    }

    // Getters
    public function getPacienteId(): ?int { return $this->paciente_id; }
    public function getUserId(): ?int { return $this->user_id; }
    public function getFechaDesde(): ?string { return $this->fecha_desde; }
    public function getFechaHasta(): ?string { return $this->fecha_hasta; }
    public function getIndicacionesBusqueda(): ?string { return $this->indicaciones_busqueda; }
    public function getLimit(): ?int { return $this->limit; }
    public function getOffset(): int { return $this->offset; }
}
