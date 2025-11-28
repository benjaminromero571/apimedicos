<?php

require_once __DIR__ . '/../repositories/HistorialRepository.php';
require_once __DIR__ . '/../dto/HistorialDto.php';
require_once __DIR__ . '/../dto/CreateHistorialDto.php';
require_once __DIR__ . '/../dto/HistorialSearchDto.php';
require_once __DIR__ . '/../dto/HistorialDetailDto.php';

/**
 * Servicio para gestión de historiales médicos
 * Contiene toda la lógica de negocio relacionada con historiales
 */
class HistorialService
{
    private $historialRepository;

    public function __construct()
    {
        $this->historialRepository = new HistorialRepository();
    }

    /**
     * Obtiene todos los historiales con paginación
     */
    public function getAllHistoriales($limit = null, $offset = 0)
    {
        try {
            $entities = $this->historialRepository->getAll($limit, $offset);
            
            if (!$entities) {
                return [
                    'success' => false,
                    'message' => 'No se pudieron obtener los historiales',
                    'data' => []
                ];
            }

            $historiales = [];
            foreach ($entities as $entity) {
                $historiales[] = HistorialDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Historiales obtenidos correctamente',
                'data' => $historiales,
                'total' => count($historiales)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener historiales: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene un historial por ID con detalles completos
     */
    public function getHistorialById($id)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                return [
                    'success' => false,
                    'message' => 'ID de historial inválido',
                    'data' => null
                ];
            }

            $entity = $this->historialRepository->getById($id);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'Historial no encontrado',
                    'data' => null
                ];
            }

            // Usar DTO detallado para información completa
            $historialDetail = new HistorialDetailDto($entity->toArray());

            return [
                'success' => true,
                'message' => 'Historial obtenido correctamente',
                'data' => $historialDetail->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener el historial: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Obtiene historiales de un paciente específico
     */
    public function getHistorialesByPaciente($idpaciente, $limit = null, $offset = 0)
    {
        try {
            if (empty($idpaciente) || !is_numeric($idpaciente)) {
                return [
                    'success' => false,
                    'message' => 'ID de paciente inválido',
                    'data' => []
                ];
            }

            $entities = $this->historialRepository->getByPacienteId($idpaciente, $limit, $offset);
            
            $historiales = [];
            foreach ($entities as $entity) {
                $historiales[] = HistorialDto::fromEntity($entity);
            }

            // Obtener estadísticas del paciente
            $estadisticas = $this->historialRepository->getEstadisticasPaciente($idpaciente);

            return [
                'success' => true,
                'message' => 'Historiales del paciente obtenidos correctamente',
                'data' => $historiales,
                'estadisticas' => $estadisticas,
                'total' => count($historiales)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener historiales del paciente: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Crea un nuevo historial médico
     */
    public function createHistorial($data)
    {
        try {
            // Crear DTO de creación y validar
            $createDto = new CreateHistorialDto($data);
            $createDto->sanitize();
            
            $validationErrors = $createDto->validate();
            if (!empty($validationErrors)) {
                return [
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validationErrors,
                    'data' => null
                ];
            }

            // Verificar que el paciente existe
            if (!$this->pacienteExists($createDto->idpaciente)) {
                return [
                    'success' => false,
                    'message' => 'El paciente especificado no existe',
                    'data' => null
                ];
            }

            // Crear el historial
            $newId = $this->historialRepository->create($createDto->toArray());
            
            if (!$newId) {
                return [
                    'success' => false,
                    'message' => 'Error al crear el historial médico',
                    'data' => null
                ];
            }

            // Obtener el historial creado con detalles
            $createdEntity = $this->historialRepository->getById($newId);
            $historialDetail = new HistorialDetailDto($createdEntity->toArray());

            return [
                'success' => true,
                'message' => 'Historial médico creado correctamente',
                'data' => $historialDetail->toArray(),
                'id' => $newId
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear historial: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Actualiza un historial existente
     */
    public function updateHistorial($id, $data)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                return [
                    'success' => false,
                    'message' => 'ID de historial inválido',
                    'data' => null
                ];
            }

            // Verificar que existe
            $existing = $this->historialRepository->getById($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Historial no encontrado',
                    'data' => null
                ];
            }

            // Crear DTO y validar
            $updateDto = new CreateHistorialDto($data);
            $updateDto->sanitize();
            
            $validationErrors = $updateDto->validate();
            if (!empty($validationErrors)) {
                return [
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validationErrors,
                    'data' => null
                ];
            }

            // Actualizar
            $success = $this->historialRepository->update($id, $updateDto->toArray());
            
            if (!$success) {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el historial',
                    'data' => null
                ];
            }

            // Obtener el historial actualizado
            $updatedEntity = $this->historialRepository->getById($id);
            $historialDetail = new HistorialDetailDto($updatedEntity->toArray());

            return [
                'success' => true,
                'message' => 'Historial actualizado correctamente',
                'data' => $historialDetail->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar historial: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Elimina un historial
     */
    public function deleteHistorial($id)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                return [
                    'success' => false,
                    'message' => 'ID de historial inválido'
                ];
            }

            // Verificar que existe
            $existing = $this->historialRepository->getById($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'Historial no encontrado'
                ];
            }

            $success = $this->historialRepository->delete($id);
            
            if (!$success) {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el historial'
                ];
            }

            return [
                'success' => true,
                'message' => 'Historial eliminado correctamente'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar historial: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Busca historiales según criterios
     */
    public function searchHistoriales($searchData)
    {
        try {
            $searchDto = new HistorialSearchDto($searchData);
            $criteria = $searchDto->getCriteriaForRepository();
            
            // Realizar búsqueda
            $entities = $this->historialRepository->search(
                $criteria, 
                $searchDto->limit, 
                $searchDto->offset
            );
            
            // Obtener total para paginación
            $total = $this->historialRepository->count($criteria);
            
            $historiales = [];
            foreach ($entities as $entity) {
                $historiales[] = HistorialDto::fromEntity($entity);
            }

            return [
                'success' => true,
                'message' => 'Búsqueda realizada correctamente',
                'data' => $historiales,
                'pagination' => [
                    'total' => $total,
                    'limit' => $searchDto->limit,
                    'offset' => $searchDto->offset,
                    'page' => $searchDto->getPagination()['page'],
                    'total_pages' => ceil($total / $searchDto->limit)
                ],
                'search_criteria' => $searchDto->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene el último historial de un paciente
     */
    public function getLastHistorialByPaciente($idpaciente)
    {
        try {
            if (empty($idpaciente) || !is_numeric($idpaciente)) {
                return [
                    'success' => false,
                    'message' => 'ID de paciente inválido',
                    'data' => null
                ];
            }

            $entity = $this->historialRepository->getLastByPaciente($idpaciente);
            
            if (!$entity) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron historiales para este paciente',
                    'data' => null
                ];
            }

            $historialDto = HistorialDto::fromEntity($entity);

            return [
                'success' => true,
                'message' => 'Último historial obtenido correctamente',
                'data' => $historialDto->toArray()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener último historial: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Obtiene estadísticas médicas de un paciente
     */
    public function getEstadisticasPaciente($idpaciente)
    {
        try {
            if (empty($idpaciente) || !is_numeric($idpaciente)) {
                return [
                    'success' => false,
                    'message' => 'ID de paciente inválido',
                    'data' => null
                ];
            }

            $estadisticas = $this->historialRepository->getEstadisticasPaciente($idpaciente);
            
            if (!$estadisticas || $estadisticas['total_consultas'] == 0) {
                return [
                    'success' => false,
                    'message' => 'No hay datos suficientes para generar estadísticas',
                    'data' => null
                ];
            }

            // Agregar análisis adicional
            $estadisticasExtendidas = $this->procesarEstadisticas($estadisticas);

            return [
                'success' => true,
                'message' => 'Estadísticas obtenidas correctamente',
                'data' => $estadisticasExtendidas
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Verifica si un paciente existe
     */
    private function pacienteExists($idpaciente)
    {
        // Aquí deberíamos usar PacienteRepository
        require_once __DIR__ . '/../repositories/PacienteRepository.php';
        $pacienteRepository = new PacienteRepository();
        return $pacienteRepository->exists($idpaciente);
    }

    /**
     * Procesa estadísticas básicas y agrega análisis
     */
    private function procesarEstadisticas($estadisticas)
    {
        $processed = $estadisticas;
        
        // Calcular IMC promedio si hay datos
        if (!empty($processed['peso_promedio']) && !empty($processed['talla_promedio'])) {
            $peso = (float) $processed['peso_promedio'];
            $talla = (float) $processed['talla_promedio'] / 100;
            
            if ($talla > 0) {
                $processed['imc_promedio'] = round($peso / ($talla * $talla), 2);
                
                // Categoría de IMC
                $imc = $processed['imc_promedio'];
                if ($imc < 18.5) {
                    $processed['categoria_imc'] = 'Bajo peso';
                } elseif ($imc < 25) {
                    $processed['categoria_imc'] = 'Peso normal';
                } elseif ($imc < 30) {
                    $processed['categoria_imc'] = 'Sobrepeso';
                } else {
                    $processed['categoria_imc'] = 'Obesidad';
                }
            }
        }
        
        // Evaluar frecuencia cardíaca promedio
        if (!empty($processed['fc_promedio'])) {
            $fc = (float) $processed['fc_promedio'];
            if ($fc < 60) {
                $processed['estado_fc'] = 'Bradicardia';
            } elseif ($fc > 100) {
                $processed['estado_fc'] = 'Taquicardia';
            } else {
                $processed['estado_fc'] = 'Normal';
            }
        }
        
        // Evaluar frecuencia respiratoria promedio
        if (!empty($processed['fr_promedio'])) {
            $fr = (float) $processed['fr_promedio'];
            if ($fr < 12) {
                $processed['estado_fr'] = 'Bradipnea';
            } elseif ($fr > 20) {
                $processed['estado_fr'] = 'Taquipnea';
            } else {
                $processed['estado_fr'] = 'Normal';
            }
        }
        
        // Calcular duración de seguimiento
        if (!empty($processed['primera_consulta']) && !empty($processed['ultima_consulta'])) {
            $primera = new DateTime($processed['primera_consulta']);
            $ultima = new DateTime($processed['ultima_consulta']);
            $diferencia = $primera->diff($ultima);
            
            $processed['tiempo_seguimiento'] = [
                'años' => $diferencia->y,
                'meses' => $diferencia->m,
                'días' => $diferencia->d,
                'total_dias' => $diferencia->days
            ];
        }
        
        return $processed;
    }
}

?>