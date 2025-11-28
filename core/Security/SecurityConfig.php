<?php

/**
 * Configuración de Seguridad
 * Centraliza todas las configuraciones de seguridad de la API
 */
class SecurityConfig
{
    /**
     * Configuración de Rate Limiting
     */
    public static function getRateLimitConfig()
    {
        return [
            'max_requests' => $_ENV['RATE_LIMIT_MAX'] ?? 100,
            'time_window' => $_ENV['RATE_LIMIT_WINDOW'] ?? 3600, // 1 hora
            'burst_limit' => $_ENV['RATE_LIMIT_BURST'] ?? 20,   // Máximo en ráfaga
            'enabled' => $_ENV['RATE_LIMIT_ENABLED'] ?? true
        ];
    }

    /**
     * Configuración de CORS
     */
    public static function getCORSConfig()
    {
        $allowedOrigins = $_ENV['CORS_ORIGINS'] ?? 'http://localhost:4200,http://localhost:3000,http://127.0.0.1:4200,http://127.0.0.1:3000';
        
        return [
            'origins' => explode(',', $allowedOrigins),
            'methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'headers' => [
                'Origin', 
                'X-Requested-With', 
                'Content-Type', 
                'Accept', 
                'Authorization',
                'X-API-Key',
                'X-User-Role'
            ],
            'credentials' => true,
            'max_age' => 86400
        ];
    }

    /**
     * Configuración de Headers de Seguridad
     */
    public static function getSecurityHeadersConfig()
    {
        return [
            'api_version' => '1.0',
            'environment' => $_ENV['APP_ENV'] ?? 'development',
            'hsts_max_age' => 31536000,
            'hsts_subdomains' => true,
            'hsts_preload' => false,
            'csp_rules' => [
                'default-src' => "'self'",
                'script-src' => "'self' 'unsafe-inline'",
                'style-src' => "'self' 'unsafe-inline'",
                'img-src' => "'self' data: https:",
                'connect-src' => "'self'",
                'font-src' => "'self'",
                'object-src' => "'none'",
                'media-src' => "'self'",
                'frame-src' => "'none'"
            ]
        ];
    }

    /**
     * Verifica si estamos en modo de desarrollo
     */
    public static function isDevelopment()
    {
        return ($_ENV['APP_ENV'] ?? 'development') === 'development';
    }

    /**
     * Carga variables de entorno desde archivo .env si existe
     */
    public static function loadEnv($envFile)
    {
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, '"\'');
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
}

?>