<?php
// ============ RUTAS DE HISTORIALES ============
$router->get('/historiales', 'HistorialController@obtenerTodos');
$router->get('/historiales/search', 'HistorialController@buscar');
$router->get('/historiales/paciente/{id}', 'HistorialController@obtenerPorPaciente');
$router->get('/historiales/paciente/{id}/ultimo', 'HistorialController@obtenerUltimoPorPaciente');
$router->get('/historiales/paciente/{idpaciente}/estadisticas', 'HistorialController@obtenerEstadisticas');
$router->get('/historiales/paciente/{idpaciente}/resumen', 'HistorialController@resumenMedico');
$router->get('/historiales/{id}', 'HistorialController@obtenerPorId');
$router->post('/historiales', 'HistorialController@crear');
$router->post('/historiales/search', 'HistorialController@buscar');
$router->put('/historiales/{id}', 'HistorialController@actualizar');
$router->delete('/historiales/{id}', 'HistorialController@eliminar');
?>