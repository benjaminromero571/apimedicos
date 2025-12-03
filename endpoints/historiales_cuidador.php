<?php

/**
 * Rutas para Historiales de Cuidador
 * 
 * Endpoints REST para gestionar historiales creados por cuidadores.
 * 
 * Permisos:
 * - GET (Lectura): Todos los usuarios autenticados
 * - POST/PUT (Crear/Editar): Administradores y Cuidadores
 * - DELETE (Eliminar): Solo Administradores
 */

// ============ RUTAS PÚBLICAS (requieren autenticación) ============

// Listar todos los historiales de cuidador con paginación
// GET /historiales-cuidador?limit=10&offset=0
$router->get('/historiales-cuidador', 'HistorialCuidadorController@obtenerTodos');

// Buscar historiales con filtros complejos
// GET /historiales-cuidador/buscar?id_paciente=1&fecha_desde=2024-01-01&fecha_hasta=2024-12-31
$router->get('/historiales-cuidador/buscar', 'HistorialCuidadorController@buscar');

// Obtener un historial específico por ID
// GET /historiales-cuidador/123
$router->get('/historiales-cuidador/{id}', 'HistorialCuidadorController@obtenerPorId');

// Obtener historiales por paciente
// GET /historiales-cuidador/paciente/456?limit=20&offset=0
$router->get('/historiales-cuidador/paciente/{id}', 'HistorialCuidadorController@obtenerPorPaciente');

// Obtener historiales por cuidador
// GET /historiales-cuidador/cuidador/789?limit=20&offset=0
$router->get('/historiales-cuidador/cuidador/{id}', 'HistorialCuidadorController@obtenerPorCuidador');

// Obtener historiales de pacientes asignados a un cuidador
// GET /historiales-cuidador/cuidador-asignado/789?limit=20&offset=0&id_paciente=456
$router->get('/historiales-cuidador/cuidador-asignado/{id}', 'HistorialCuidadorController@historialesPacientesAsignadosPorCuidador');

// Obtener estadísticas de historiales por paciente
// GET /historiales-cuidador/estadisticas/paciente/456
$router->get('/historiales-cuidador/estadisticas/paciente/{id}', 'HistorialCuidadorController@estadisticasPorPaciente');

// ============ RUTAS PROTEGIDAS (requieren permisos específicos) ============

// Crear nuevo historial de cuidador
// POST /historiales-cuidador
// Permisos: Administrador, Cuidador
// Body: { "detalle": "...", "id_paciente": 1, "id_cuidador": 2, "fecha_historial": "2024-01-01 10:30:00" }
$router->post('/historiales-cuidador', 'HistorialCuidadorController@crear');

// Actualizar historial existente
// PUT /historiales-cuidador/123
// Permisos: Administrador, Cuidador
// Body: { "detalle": "...", "fecha_historial": "2024-01-01 11:00:00" }
$router->put('/historiales-cuidador/{id}', 'HistorialCuidadorController@actualizar');

// Eliminar historial
// DELETE /historiales-cuidador/123
// Permisos: Solo Administrador
$router->delete('/historiales-cuidador/{id}', 'HistorialCuidadorController@eliminar');

?>
