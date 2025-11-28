<?php

/**
 * Rutas para Recetas Médicas
 * 
 * Endpoints REST para gestionar recetas médicas creadas por médicos.
 * 
 * Permisos:
 * - GET (Lectura): Todos los usuarios autenticados
 * - POST (Crear): Solo Médicos (solo pueden crear recetas a su nombre)
 * - PUT (Editar): Solo el Médico propietario de la receta y Administradores
 * - DELETE (Eliminar): Solo Administradores
 * 
 * Reglas de negocio importantes:
 * 1. Solo usuarios con rol "Medico" pueden crear recetas y únicamente a su propio nombre
 * 2. Solo el médico que creó la receta puede editarla (los Administradores también pueden editar)
 * 3. Solo los administradores pueden eliminar recetas
 */

// ============ RUTAS PÚBLICAS (requieren autenticación) ============

// Listar todas las recetas médicas con paginación
// GET /recetas-medicas?limit=10&offset=0
$router->get('/recetas-medicas', 'RecetaMedicaController@obtenerTodas');

// Buscar recetas con filtros complejos
// GET /recetas-medicas/buscar?id_medico=1&id_historial=10&fecha_desde=2024-01-01&fecha_hasta=2024-12-31&detalle=paracetamol
$router->get('/recetas-medicas/buscar', 'RecetaMedicaController@buscar');

// Obtener recetas del médico autenticado (solo para médicos)
// GET /recetas-medicas/mis-recetas?limit=20&offset=0
$router->get('/recetas-medicas/mis-recetas', 'RecetaMedicaController@obtenerMisRecetas');

// Obtener una receta específica por ID
// GET /recetas-medicas/123
$router->get('/recetas-medicas/{id}', 'RecetaMedicaController@obtenerPorId');

// Obtener recetas por médico
// GET /recetas-medicas/medico/456?limit=20&offset=0
$router->get('/recetas-medicas/medico/{id}', 'RecetaMedicaController@obtenerPorMedico');

// Obtener estadísticas de recetas por médico
// GET /recetas-medicas/estadisticas/medico/456
$router->get('/recetas-medicas/estadisticas/medico/{id}', 'RecetaMedicaController@estadisticasPorMedico');

// ============ RUTAS PROTEGIDAS (requieren permisos específicos) ============

// Crear nueva receta médica
// POST /recetas-medicas
// Permisos: Solo Médicos (para sí mismos)
// Body: 
// {
//   "detalle": "Paracetamol 500mg cada 8 horas por 5 días",
//   "fecha": "2024-01-15" (opcional, default: hoy),
//   "id_medico": 123,
//   "id_historial": 456
// }
$router->post('/recetas-medicas', 'RecetaMedicaController@crear');

// Actualizar receta existente
// PUT /recetas-medicas/123
// Permisos: Solo el Médico propietario (los Administradores también pueden editar)
// Body:
// {
//   "detalle": "Paracetamol 500mg cada 6 horas por 7 días" (opcional),
//   "fecha": "2024-01-16" (opcional),
//   "id_historial": 456 (opcional)
// }
$router->put('/recetas-medicas/{id}', 'RecetaMedicaController@actualizar');

// Eliminar receta
// DELETE /recetas-medicas/123
// Permisos: Solo Administradores
$router->delete('/recetas-medicas/{id}', 'RecetaMedicaController@eliminar');

?>
