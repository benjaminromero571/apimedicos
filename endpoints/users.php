<?php
// ============ RUTAS DE USUARIOS ============
$router->get('/users', 'UserController@obtenerTodos');
$router->get('/users/roles', 'UserController@obtenerRoles');
$router->get('/users/estadisticas', 'UserController@obtenerEstadisticas');
$router->get('/users/buscar', 'UserController@buscar');
$router->get('/users/email', 'UserController@obtenerPorEmail');
$router->get('/users/rol/{rol}', 'UserController@obtenerPorRol');
$router->get('/users/{id}', 'UserController@obtenerPorId');
$router->get('/users/{id}/perfil', 'UserController@obtenerPerfil');
$router->post('/users', 'UserController@crear');
$router->post('/users/buscar', 'UserController@buscar');
$router->post('/users/autenticar', 'UserController@autenticar');
$router->put('/users/{id}', 'UserController@actualizar');
$router->put('/users/{id}/password', 'UserController@cambiarPassword');
$router->put('/users/{id}/admin-password', 'UserController@cambiarPasswordAdmin');
$router->delete('/users/{id}', 'UserController@eliminar');
