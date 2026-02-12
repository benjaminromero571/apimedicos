<?php
// Cargar configuraciones de entorno
require_once 'core/Security/SecurityConfig.php';
// Ruta a la raíz del proyecto
$envFile = __DIR__ . '/.env';
SecurityConfig::loadEnv($envFile);

// Security Services
require_once 'core/Security/JWTService.php';
require_once 'core/Security/AuthMiddleware.php';
require_once 'core/Security/AuthorizationMiddleware.php';
require_once 'core/Security/RateLimitService.php';
require_once 'core/Security/SecurityLogger.php';
require_once 'core/Security/SecurityHeaders.php';

// Obtener configuraciones
$securityConfig = SecurityConfig::getSecurityHeadersConfig();
$corsConfig = SecurityConfig::getCORSConfig();
$rateLimitConfig = SecurityConfig::getRateLimitConfig();

// Aplicar headers de seguridad
SecurityHeaders::middleware([
    'cors_origins' => $corsConfig['origins'],
    'cors_methods' => $corsConfig['methods'],
    'cors_headers' => $corsConfig['headers'],
    'api_version' => $securityConfig['api_version'],
    'environment' => $securityConfig['environment'],
    'hsts_max_age' => $securityConfig['hsts_max_age'],
    'csp_rules' => $securityConfig['csp_rules']
]);

// Aplicar rate limiting (excepto para OPTIONS)
if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS' && $rateLimitConfig['enabled']) {
    $clientIP = $_SERVER['REMOTE_ADDR'];
    $rateLimitResult = RateLimitService::checkLimitWithConfig($clientIP, $rateLimitConfig);

    if (!$rateLimitResult['allowed']) {
        SecurityLogger::logSecurityEvent('rate_limit_exceeded', $clientIP, [
            'requests_made' => $rateLimitResult['requests_made'],
            'limit' => $rateLimitConfig['max_requests']
        ]);

        http_response_code(429);
        echo json_encode([
            'error' => 'Rate limit exceeded. Try again later.',
            'retry_after' => $rateLimitResult['reset_time'] - time()
        ]);
        exit();
    }

    // Aplicar headers de rate limit
    SecurityHeaders::applyRateLimitHeaders(
        $rateLimitConfig['max_requests'],
        $rateLimitResult['remaining'],
        $rateLimitResult['reset_time']
    );
}

// Configuración de headers CORS (reemplazado por SecurityHeaders)
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
// header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
// header('Content-Type: application/json; charset=utf-8');

// Manejar preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir el core
require_once 'core/Router.php';
require_once 'core/BaseController.php';

// Incluir servicios
require_once 'services/ProfesionalService.php';
require_once 'services/UserService.php';

// Incluir controladores
require_once 'controllers/RecetaMedicaController.php';
require_once 'controllers/PacienteController.php';
require_once 'controllers/ProfesionalController.php';
require_once 'controllers/HistorialController.php';
require_once 'controllers/HistorialCuidadorController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/AsignacionController.php';
require_once 'controllers/IndicacionMedicaController.php';
require_once 'controllers/AuthController.php';

// Crear instancia del router
$router = new Router();

// ============ CARGAR ENDPOINTS ============
// Cargar todos los endpoints desde el directorio endpoints
require_once 'endpoints/index.php';

// ============ MIDDLEWARE DE AUTENTICACIÓN ============
/**
 * Función para aplicar middleware de autenticación
 */
function applyAuthMiddleware($excludePaths = [])
{
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = '/api';

    if (strpos($currentPath, $basePath) === 0) {
        $currentPath = substr($currentPath, strlen($basePath));
    }

    // Verificar si la ruta está excluida
    foreach ($excludePaths as $excludePath) {
        if (strpos($currentPath, $excludePath) === 0) {
            return true; // No aplicar middleware
        }
    }

    // Aplicar middleware de autenticación
    $authResult = AuthMiddleware::verify();

    if (!$authResult['success']) {
        http_response_code($authResult['code']);
        echo json_encode(['error' => $authResult['message']]);
        exit();
    }

    // Almacenar información del usuario autenticado globalmente
    $GLOBALS['current_user'] = $authResult['user'];
    $GLOBALS['current_user_payload'] = $authResult['payload'];

    return true;
}

// Rutas públicas (no requieren autenticación)
$publicPaths = [
    '/auth/login',
    '/auth/register',
    '/auth/verify',
    '/health'
];

// Aplicar middleware de autenticación (excepto para rutas públicas y OPTIONS)
if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    applyAuthMiddleware($publicPaths);

    // Aplicar middleware de autorización para rutas protegidas
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = '/api';

    if (strpos($currentPath, $basePath) === 0) {
        $currentPath = substr($currentPath, strlen($basePath));
    }

    // Solo aplicar autorización si ya pasó la autenticación
    $isPublicPath = false;
    foreach ($publicPaths as $publicPath) {
        if (strpos($currentPath, $publicPath) === 0) {
            $isPublicPath = true;
            break;
        }
    }

    if (!$isPublicPath) {
        $authzResult = AuthorizationMiddleware::authorize($_SERVER['REQUEST_METHOD'], $currentPath);

        if (!$authzResult['success']) {
            http_response_code($authzResult['code']);
            echo json_encode(['error' => $authzResult['message']]);
            exit();
        }
    }
}

// Obtener la URI actual
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remover el prefijo de la ruta si existe (ajustar según tu configuración)
$basePath = '/api';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Ejecutar el router
try {
    $router->dispatch($method, $uri);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
?>