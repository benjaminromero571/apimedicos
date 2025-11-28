<?php

declare(strict_types=1);

/**
 * HistorialCuidadorSearchDto - DTO para búsquedas y filtros de historiales de cuidador
 * 
 * Permite realizar búsquedas avanzadas con múltiples criterios.
 */
class HistorialCuidadorSearchDto
{
    private ?int $id_paciente;
    private ?int $id_cuidador;
    private ?string $fecha_desde;
    private ?string $fecha_hasta;
    private ?string $detalle;
    private ?int $limit;
    private ?int $offset;
    private ?string $order_by;
    private ?string $order_direction;

    public function __construct(array $params = [])
    {
        $this->id_paciente = isset($params['id_paciente']) && is_numeric($params['id_paciente']) 
            ? (int)$params['id_paciente'] 
            : null;
            
        $this->id_cuidador = isset($params['id_cuidador']) && is_numeric($params['id_cuidador']) 
            ? (int)$params['id_cuidador'] 
            : null;
            
        $this->fecha_desde = $params['fecha_desde'] ?? null;
        $this->fecha_hasta = $params['fecha_hasta'] ?? null;
        $this->detalle = isset($params['detalle']) ? trim($params['detalle']) : null;
        
        // Paginación
        $this->limit = isset($params['limit']) && is_numeric($params['limit']) 
            ? (int)$params['limit'] 
            : 50;
            
        $this->offset = isset($params['offset']) && is_numeric($params['offset']) 
            ? (int)$params['offset'] 
            : 0;
        
        // Ordenamiento
        $this->order_by = $params['order_by'] ?? 'fecha_historial';
        $this->order_direction = isset($params['order_direction']) && 
                                 strtoupper($params['order_direction']) === 'ASC' 
            ? 'ASC' 
            : 'DESC';

        $this->validateDates();
    }

    /**
     * Valida el formato de las fechas
     */
    private function validateDates(): void
    {
        if ($this->fecha_desde !== null) {
            $fecha = \DateTime::createFromFormat('Y-m-d', $this->fecha_desde);
            if (!$fecha) {
                throw new InvalidArgumentException('Formato de fecha_desde inválido. Use Y-m-d');
            }
        }

        if ($this->fecha_hasta !== null) {
            $fecha = \DateTime::createFromFormat('Y-m-d', $this->fecha_hasta);
            if (!$fecha) {
                throw new InvalidArgumentException('Formato de fecha_hasta inválido. Use Y-m-d');
            }
        }

        if ($this->fecha_desde && $this->fecha_hasta && $this->fecha_desde > $this->fecha_hasta) {
            throw new InvalidArgumentException('La fecha_desde no puede ser mayor que fecha_hasta');
        }
    }

    /**
     * Construye las condiciones SQL para la búsqueda
     */
    public function buildWhereConditions(): array
    {
        $conditions = [];
        $params = [];
        $types = '';

        if ($this->id_paciente !== null) {
            $conditions[] = 'hc.id_paciente = ?';
            $params[] = $this->id_paciente;
            $types .= 'i';
        }

        if ($this->id_cuidador !== null) {
            $conditions[] = 'hc.id_cuidador = ?';
            $params[] = $this->id_cuidador;
            $types .= 'i';
        }

        if ($this->fecha_desde !== null) {
            $conditions[] = 'hc.fecha_historial >= ?';
            $params[] = $this->fecha_desde;
            $types .= 's';
        }

        if ($this->fecha_hasta !== null) {
            $conditions[] = 'hc.fecha_historial <= ?';
            $params[] = $this->fecha_hasta;
            $types .= 's';
        }

        if ($this->detalle !== null && $this->detalle !== '') {
            $conditions[] = 'hc.detalle LIKE ?';
            $params[] = '%' . $this->detalle . '%';
            $types .= 's';
        }

        return [
            'conditions' => $conditions,
            'params' => $params,
            'types' => $types,
        ];
    }

    // Getters
    public function getIdPaciente(): ?int
    {
        return $this->id_paciente;
    }

    public function getIdCuidador(): ?int
    {
        return $this->id_cuidador;
    }

    public function getFechaDesde(): ?string
    {
        return $this->fecha_desde;
    }

    public function getFechaHasta(): ?string
    {
        return $this->fecha_hasta;
    }

    public function getDetalle(): ?string
    {
        return $this->detalle;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getOrderBy(): ?string
    {
        return $this->order_by;
    }

    public function getOrderDirection(): ?string
    {
        return $this->order_direction;
    }
}
