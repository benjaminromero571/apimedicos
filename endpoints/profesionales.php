<?php
// ============ RUTAS DE PROFESIONALES ============
$router->get('/profesionales', 'ProfesionalController@obtenerTodos');
$router->get('/profesionales/especialidades', 'ProfesionalController@obtenerEspecialidades');
$router->get('/profesionales/especialidad/{especialidad}', 'ProfesionalController@obtenerPorEspecialidad');
$router->get('/profesionales/buscar', 'ProfesionalController@buscar');
$router->get('/profesionales/buscar/nombre', 'ProfesionalController@buscarPorNombre');
$router->get('/profesionales/estadisticas', 'ProfesionalController@obtenerEstadisticas');
$router->get('/profesionales/sin-usuario', 'ProfesionalController@obtenerSinUsuario');
$router->get('/profesionales/usuario/{user_id}', 'ProfesionalController@obtenerPorUsuario');
$router->get('/profesionales/documento/{documento}', 'ProfesionalController@obtenerPorDocumento');
$router->get('/profesionales/cedula/{cedula}', 'ProfesionalController@obtenerPorCedula');
$router->get('/profesionales/validar-documento/{documento}', 'ProfesionalController@validarDocumento');
$router->get('/profesionales/validar-cedula/{cedula}', 'ProfesionalController@validarCedula');
$router->get('/profesionales/{id}', 'ProfesionalController@obtenerPorId');
$router->post('/profesionales', 'ProfesionalController@crear');
$router->post('/profesionales/buscar', 'ProfesionalController@buscar');
$router->put('/profesionales/{id}', 'ProfesionalController@actualizar');
$router->put('/profesionales/{id}/asignar-usuario', 'ProfesionalController@asignarUsuario');
$router->delete('/profesionales/{id}', 'ProfesionalController@eliminar');
?>