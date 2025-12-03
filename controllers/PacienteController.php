<?php
require_once 'services/PacienteService.php';

class PacienteController extends BaseController
{
    private $pacienteService;

    public function __construct()
    {
        parent::__construct();
        $this->pacienteService = new PacienteService();
    }
    /**
     * Obtiene todos los pacientes
     */
    public function obtenerTodos($params = [])
    {
        try {
            $pacientes = $this->pacienteService->getAll();
            
            $response = array_map(function($dto) {
                return $dto->toArray();
            }, $pacientes);
            
            $this->jsonResponse($response);
        } catch (Exception $e) {
            $this->jsonError("Error al obtener pacientes: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene todos los pacientes con paginación
     */
    public function obtenerTodosPaginados($params = [])
    {
        try {
            $limit = isset($params['limit']) ? (int)$params['limit'] : null;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
            
            $result = $this->pacienteService->getAllPaginated($limit, $offset);
            
            if ($result['success']) {
                $response = [
                    'data' => array_map(function($dto) {
                        return $dto->toArray();
                    }, $result['data']),
                    'pagination' => $result['pagination']
                ];
                $this->jsonResponse($response, $result['message']);
            } else {
                $this->jsonError($result['message'], 500);
            }
        } catch (Exception $e) {
            $this->jsonError("Error al obtener pacientes: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene un paciente por ID
     */
    public function obtenerPorId($params)
    {
        try {
            if (!isset($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            $paciente = $this->pacienteService->getByIdWithDetails($params['id']);

            if (!$paciente) {
                $this->jsonError("Paciente no encontrado", 404);
                return;
            }

            $this->jsonResponse($paciente->toArray());
        } catch (Exception $e) {
            $this->jsonError("Error al obtener paciente: " . $e->getMessage(), 500);
        }
    }

    /**
     * Crea un nuevo paciente
     */
    public function crear($params = [])
    {
        try {
            $data = $this->getJsonInput();
            
            if (!$data) {
                $this->jsonError("Datos JSON requeridos", 400);
                return;
            }

            // Crear nuevo paciente usando el service
            $pacienteDto = $this->pacienteService->create($data);

            $this->jsonResponse($pacienteDto->toArray(), "Paciente creado exitosamente", 201);

        } catch (Exception $e) {
            // Si es error de validación, código 400, sino 500
            $statusCode = strpos($e->getMessage(), 'validación') !== false ? 400 : 500;
            $this->jsonError("Error al crear paciente: " . $e->getMessage(), $statusCode);
        }
    }

    /**
     * Actualiza un paciente existente
     */
    public function actualizar($params)
    {
        try {
            if (!isset($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            $data = $this->getJsonInput();
            
            if (!$data) {
                $this->jsonError("Datos JSON requeridos", 400);
                return;
            }

            $success = $this->pacienteService->update($params['id'], $data);
            
            if (!$success) {
                $this->jsonError("Error al actualizar paciente", 500);
                return;
            }

            // Obtener el paciente actualizado
            $paciente = $this->pacienteService->getById($params['id']);
            
            $this->jsonResponse($paciente->toArray(), "Paciente actualizado exitosamente");

        } catch (Exception $e) {
            $statusCode = strpos($e->getMessage(), 'validación') !== false ? 400 : 500;
            $this->jsonError("Error al actualizar paciente: " . $e->getMessage(), $statusCode);
        }
    }

    /**
     * Elimina un paciente
     */
    public function eliminar($params)
    {
        try {
            if (!isset($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            $result = $this->pacienteService->delete($params['id']);

            if (!$result) {
                $this->jsonError("Paciente no encontrado", 404);
                return;
            }

            $this->jsonResponse(null, "Paciente eliminado exitosamente");

        } catch (Exception $e) {
            $this->jsonError("Error al eliminar paciente: " . $e->getMessage(), 500);
        }
    }

    /**
     * Busca pacientes por nombre
     */
    public function buscar($params = [])
    {
        try {
            $query = $_GET['q'] ?? '';
            
            if (empty($query)) {
                $this->jsonError("Parámetro de búsqueda 'q' requerido", 400);
                return;
            }

            $pacientes = $this->pacienteService->searchByName($query);
            
            $response = array_map(function($dto) {
                return $dto->toArray();
            }, $pacientes);
            
            $this->jsonResponse($response);
        } catch (Exception $e) {
            $this->jsonError("Error en la búsqueda: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene un paciente por RUT
     */
    public function obtenerPorRut($params)
    {
        try {
            if (!isset($params['rut'])) {
                $this->jsonError("RUT de paciente requerido", 400);
                return;
            }

            $paciente = $this->pacienteService->getByRut($params['rut']);

            if (!$paciente) {
                $this->jsonError("Paciente no encontrado", 404);
                return;
            }

            $this->jsonResponse($paciente->toArray());
        } catch (Exception $e) {
            $this->jsonError("Error al obtener paciente: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene estadísticas de pacientes
     */
    public function obtenerEstadisticas($params = [])
    {
        try {
            $estadisticas = $this->pacienteService->getEstadisticasExtendidas();
            $this->jsonResponse($estadisticas->toArray());
        } catch (Exception $e) {
            $this->jsonError("Error al obtener estadísticas: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene los cuidadores de un paciente
     */
    public function obtenerCuidadores($params)
    {
        try {
            if (!isset($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            $cuidadores = $this->pacienteService->getCuidadores($params['id']);
            $this->jsonResponse($cuidadores);

        } catch (Exception $e) {
            $this->jsonError("Error al obtener cuidadores: " . $e->getMessage(), 500);
        }
    }

    /**
     * Asigna un cuidador a un paciente
     */
    public function asignarCuidador($params)
    {
        try {
            if (!isset($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            $data = $this->getJsonInput();
            if (!$data || !isset($data['id_cuidador'])) {
                $this->jsonError("ID del cuidador requerido", 400);
                return;
            }

            // Obtener el ID del usuario autenticado si está disponible
            $userId = $_SESSION['user_id'] ?? null;

            $success = $this->pacienteService->assignCuidador($params['id'], $data['id_cuidador'], $userId);
            
            if (!$success) {
                $this->jsonError("Error al asignar cuidador", 500);
                return;
            }

            $this->jsonResponse(null, "Cuidador asignado exitosamente");

        } catch (Exception $e) {
            $this->jsonError("Error al asignar cuidador: " . $e->getMessage(), 500);
        }
    }

    /**
     * Remueve un cuidador de un paciente
     */
    public function removerCuidador($params)
    {
        try {
            if (!isset($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            // Si viene cuidador_id en la URL, remover solo ese cuidador
            if (isset($params['cuidador_id'])) {
                $success = $this->pacienteService->unassignCuidador($params['id'], $params['cuidador_id']);
            } else {
                // Si no viene, esperar el ID en el body
                $data = $this->getJsonInput();
                if (!$data || !isset($data['id_cuidador'])) {
                    $this->jsonError("ID del cuidador requerido", 400);
                    return;
                }
                $success = $this->pacienteService->unassignCuidador($params['id'], $data['id_cuidador']);
            }
            
            if (!$success) {
                $this->jsonError("Error al remover cuidador", 500);
                return;
            }

            $this->jsonResponse(null, "Cuidador removido exitosamente");

        } catch (Exception $e) {
            $this->jsonError("Error al remover cuidador: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene profesionales asignados a un paciente
     */
    public function obtenerProfesionales($params)
    {
        try {
            if (!isset($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            $profesionales = $this->pacienteService->getProfesionales($params['id']);
            $this->jsonResponse($profesionales);

        } catch (Exception $e) {
            $this->jsonError("Error al obtener profesionales: " . $e->getMessage(), 500);
        }
    }

    /**
     * Asigna un profesional a un paciente
     */
    public function asignarProfesional($params)
    {
        try {
            if (!isset($params['id'])) {
                $this->jsonError("ID de paciente requerido", 400);
                return;
            }

            $data = $this->getJsonInput();
            if (!$data || !isset($data['id_profesional'])) {
                $this->jsonError("ID del profesional requerido", 400);
                return;
            }

            $success = $this->pacienteService->assignProfesional($params['id'], $data['id_profesional']);
            
            if (!$success) {
                $this->jsonError("Error al asignar profesional", 500);
                return;
            }

            $this->jsonResponse(null, "Profesional asignado exitosamente");

        } catch (Exception $e) {
            $this->jsonError("Error al asignar profesional: " . $e->getMessage(), 500);
        }
    }

    /**
     * Remueve un profesional de un paciente
     */
    public function removerProfesional($params)
    {
        try {
            if (!isset($params['id']) || !isset($params['profesional_id'])) {
                $this->jsonError("ID de paciente y profesional requeridos", 400);
                return;
            }

            $success = $this->pacienteService->unassignProfesional($params['id'], $params['profesional_id']);
            
            if (!$success) {
                $this->jsonError("Error al remover profesional", 500);
                return;
            }

            $this->jsonResponse(null, "Profesional removido exitosamente");

        } catch (Exception $e) {
            $this->jsonError("Error al remover profesional: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene pacientes por profesional
     */
    public function obtenerPorProfesional($params)
    {
        try {
            if (!isset($params['profesional_id'])) {
                $this->jsonError("ID de profesional requerido", 400);
                return;
            }

            $pacientes = $this->pacienteService->getByProfesional($params['profesional_id']);
            
            $response = array_map(function($dto) {
                return $dto->toArray();
            }, $pacientes);
            
            $this->jsonResponse($response);

        } catch (Exception $e) {
            $this->jsonError("Error al obtener pacientes del profesional: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene pacientes por cuidador
     */
    public function obtenerPorCuidador($params)
    {
        try {
            if (!isset($params['cuidador_id'])) {
                $this->jsonError("ID de cuidador requerido", 400);
                return;
            }

            $pacientes = $this->pacienteService->getByCuidador($params['cuidador_id']);
            
            $response = array_map(function($dto) {
                return $dto->toArray();
            }, $pacientes);
            
            $this->jsonResponse($response);

        } catch (Exception $e) {
            $this->jsonError("Error al obtener pacientes del cuidador: " . $e->getMessage(), 500);
        }
    }
}
