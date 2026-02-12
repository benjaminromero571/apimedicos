<?php
/**
 * Archivo para cargar todos los endpoints de la API
 * Este archivo centraliza la carga de todas las rutas organizadas por módulos
 */

// Verificar que el router esté disponible
if (!isset($router)) {
    throw new Exception('Router instance is required');
}

// ============ CARGAR ENDPOINTS POR MÓDULOS ============

// Endpoints de autenticación (públicos)
require_once __DIR__ . '/auth.php';

// Endpoints de health check (público)
require_once __DIR__ . '/health.php';

// Endpoints de recursos principales (requieren autenticación)
require_once __DIR__ . '/pacientes.php';
require_once __DIR__ . '/profesionales.php';
require_once __DIR__ . '/historiales.php';
require_once __DIR__ . '/historiales_cuidador.php';
require_once __DIR__ . '/users.php';
require_once __DIR__ . '/asignaciones.php';
require_once __DIR__ . '/recetas_medicas.php';
require_once __DIR__ . '/indicaciones_medicas.php';

// Endpoints administrativos (requieren permisos especiales)
require_once __DIR__ . '/admin.php';

?>