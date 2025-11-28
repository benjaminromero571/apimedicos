<?php
// ============ RUTAS DE ASIGNACIONES ============
$router->get('/asignaciones', 'AsignacionController@obtenerTodas');
$router->get('/asignaciones/user/{id}', 'AsignacionController@obtenerPorUser');
$router->get('/asignaciones/paciente/{id}', 'AsignacionController@obtenerPorPaciente');
$router->get('/asignaciones/estadisticas', 'AsignacionController@obtenerEstadisticas');
$router->post('/asignaciones', 'AsignacionController@crear');
$router->delete('/asignaciones/{id}', 'AsignacionController@eliminar');
$router->delete('/asignaciones/unassign', 'AsignacionController@eliminarPorUserYPaciente');
?>