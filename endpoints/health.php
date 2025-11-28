<?php
// ============ RUTA DE HEALTH CHECK (PÚBLICA) ============
$router->get('/health', function() {
    $health = [
        'status' => 'OK',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => SecurityConfig::getSecurityHeadersConfig()['api_version'],
        'environment' => SecurityConfig::getSecurityHeadersConfig()['environment']
    ];
    
    // Verificar conexión a base de datos
    try {
        $db = new PDO("mysql:host=localhost;dbname=gico", "root", "");
        $health['database'] = 'connected';
    } catch (Exception $e) {
        $health['database'] = 'disconnected';
        $health['status'] = 'WARNING';
    }
    
    echo json_encode($health);
});
?>