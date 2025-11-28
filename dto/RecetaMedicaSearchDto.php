<?php

declare(strict_types=1);

/**
 * RecetaMedicaSearchDto - DTO para búsquedas avanzadas de recetas médicas
 * 
 * Permite filtrar recetas por múltiples criterios.
 * Todos los campos son opcionales.
 */
class RecetaMedicaSearchDto
{
    private ?int $id_medico;
    private ?string $fecha_desde;
    private ?string $fecha_hasta;
    private ?string $detalle_busqueda;
    private ?int $limit;
    private ?int $offset;

    /**
     * Constructor que valida y asigna filtros de búsqueda
     */
    public function __construct(array $filters = [])
    {
        $this->id_medico = isset($filters['id_medico']) && $filters['id_medico'] !== '' 
            ? $this->validatePositiveInt($filters['id_medico'], 'id_medico') 
            : null;

        $this->fecha_desde = isset($filters['fecha_desde']) && $filters['fecha_desde'] !== '' 
            ? $this->validateDate($filters['fecha_desde'], 'fecha_desde') 
            : null;

        $this->fecha_hasta = isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== '' 
            ? $this->validateDate($filters['fecha_hasta'], 'fecha_hasta') 
            : null;

        $this->detalle_busqueda = isset($filters['detalle']) && $filters['detalle'] !== '' 
            ? trim($filters['detalle']) 
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
        return $this->id_medico !== null 
            || $this->fecha_desde !== null 
            || $this->fecha_hasta !== null
            || $this->detalle_busqueda !== null;
    }

    /**
     * Construye la cláusula WHERE para SQL
     */
    public function buildWhereClause(): string
    {
        $conditions = [];

        if ($this->id_medico !== null) {
            $conditions[] = "rm.id_medico = {$this->id_medico}";
        }

        if ($this->fecha_desde !== null) {
            $conditions[] = "rm.fecha >= '{$this->fecha_desde}'";
        }

        if ($this->fecha_hasta !== null) {
            $conditions[] = "rm.fecha <= '{$this->fecha_hasta}'";
        }

        if ($this->detalle_busqueda !== null) {
            $detalle = addslashes($this->detalle_busqueda);
            $conditions[] = "rm.detalle LIKE '%{$detalle}%'";
        }

        return empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
    }

    // Getters
    public function getIdMedico(): ?int { return $this->id_medico; }
    public function getFechaDesde(): ?string { return $this->fecha_desde; }
    public function getFechaHasta(): ?string { return $this->fecha_hasta; }
    public function getDetalleBusqueda(): ?string { return $this->detalle_busqueda; }
    public function getLimit(): ?int { return $this->limit; }
    public function getOffset(): int { return $this->offset; }
}
