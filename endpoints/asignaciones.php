<?php
// ============ RUTAS DE ASIGNACIONES ============
$router->get('/asignaciones', 'AsignacionController@obtenerTodas');

// Nueva ruta paginada (no afecta la ruta existente)
// GET /asignaciones/paginated?limit=20&offset=0
$router->get('/asignaciones/paginated', 'AsignacionController@obtenerTodasPaginadas');

$router->get('/asignaciones/user/{id}', 'AsignacionController@obtenerPorUser');
$router->get('/asignaciones/paciente/{id}', 'AsignacionController@obtenerPorPaciente');
$router->get('/asignaciones/estadisticas', 'AsignacionController@obtenerEstadisticas');
$router->post('/asignaciones', 'AsignacionController@crear');
$router->delete('/asignaciones/{id}', 'AsignacionController@eliminar');
$router->delete('/asignaciones/unassign', 'AsignacionController@eliminarPorUserYPaciente');
?>