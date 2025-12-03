<?php
// ============ RUTAS DE PACIENTES ============

// Rutas básicas de CRUD
$router->get('/pacientes', 'PacienteController@obtenerTodos');

// Nueva ruta paginada (no afecta la ruta existente)
// GET /pacientes/paginated?limit=20&offset=0
$router->get('/pacientes/paginated', 'PacienteController@obtenerTodosPaginados');

$router->get('/pacientes/buscar', 'PacienteController@buscar');
$router->get('/pacientes/rut/{rut}', 'PacienteController@obtenerPorRut');
$router->get('/pacientes/{id}', 'PacienteController@obtenerPorId');
$router->post('/pacientes', 'PacienteController@crear');
$router->put('/pacientes/{id}', 'PacienteController@actualizar');
$router->delete('/pacientes/{id}', 'PacienteController@eliminar');

// Rutas de estadísticas
$router->get('/pacientes/stats/general', 'PacienteController@obtenerEstadisticas');

// Rutas de cuidadores
$router->get('/pacientes/cuidador/{cuidador_id}', 'PacienteController@obtenerPorCuidador');
$router->get('/pacientes/{id}/cuidadores', 'PacienteController@obtenerCuidadores');
$router->post('/pacientes/{id}/cuidador', 'PacienteController@asignarCuidador');
$router->delete('/pacientes/{id}/cuidador/{cuidador_id}', 'PacienteController@removerCuidador');

// Rutas de profesionales
$router->get('/pacientes/profesional/{profesional_id}', 'PacienteController@obtenerPorProfesional');
$router->get('/pacientes/{id}/profesionales', 'PacienteController@obtenerProfesionales');
$router->post('/pacientes/{id}/profesionales', 'PacienteController@asignarProfesional');
$router->delete('/pacientes/{id}/profesionales/{profesional_id}', 'PacienteController@removerProfesional');
?>