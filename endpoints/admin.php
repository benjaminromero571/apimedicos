<?php
require_once __DIR__ . '/../entities/UserEntity.php';
require_once __DIR__ . '/../entities/ProfesionalEntity.php';
require_once __DIR__ . '/../entities/PacienteEntity.php';
require_once __DIR__ . '/../entities/HistorialEntity.php';

// ============ RUTAS ADMINISTRATIVAS (REQUIEREN ADMIN+) ============
$router->get('/admin/logs/security', function() {
    // Verificar permisos de admin
    if (!isset($GLOBALS['current_user_payload']) || 
        !in_array($GLOBALS['current_user_payload']['role'], ['admin', 'Administrador'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Permisos de administrador requeridos']);
        return;
    }
    
    try {
        $logs = SecurityLogger::getRecentEvents(50);
        echo json_encode(['data' => $logs, 'message' => 'Logs de seguridad obtenidos']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener logs: ' . $e->getMessage()]);
    }
});


