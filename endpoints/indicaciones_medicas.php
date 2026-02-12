<?php

// ============ INDICACIONES MÉDICAS ============

// Listar todas
$router->get('/indicaciones-medicas', 'IndicacionMedicaController@obtenerTodas');

// Buscar con filtros
$router->get('/indicaciones-medicas/buscar', 'IndicacionMedicaController@buscar');

// Por paciente
$router->get('/indicaciones-medicas/paciente/{id}', 'IndicacionMedicaController@obtenerPorPaciente');

// Por ID
$router->get('/indicaciones-medicas/{id}', 'IndicacionMedicaController@obtenerPorId');

// Crear
$router->post('/indicaciones-medicas', 'IndicacionMedicaController@crear');

// Actualizar
$router->put('/indicaciones-medicas/{id}', 'IndicacionMedicaController@actualizar');

// Eliminar
$router->delete('/indicaciones-medicas/{id}', 'IndicacionMedicaController@eliminar');

?>
