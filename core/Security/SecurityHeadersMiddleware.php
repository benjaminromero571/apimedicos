<?php

require_once 'core/Security/SecurityConfig.php';

/**
 * Middleware para manejar headers de seguridad y CORS
 */
class SecurityHeadersMiddleware
{
    /**
     * Maneja los headers de seguridad y CORS
     */
    public static function handle()
    {
        self::setCORSHeaders();
        self::setSecurityHeaders();
        
        // Si es una petición OPTIONS (preflight), terminar aquí
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Configura headers CORS
     */
    private static function setCORSHeaders()
    {
        $corsConfig = SecurityConfig::getCORSConfig();
        
        // Obtener el origen de la petición
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Verificar si el origen está permitido
        if (in_array($origin, $corsConfig['origins']) || self::isOriginAllowed($origin, $corsConfig['origins'])) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            // Si no hay origen específico o está en desarrollo, permitir el primero de la lista
            if (empty($origin) || SecurityConfig::isDevelopment()) {
                header("Access-Control-Allow-Origin: " . $corsConfig['origins'][0]);
            }
        }
        
        // Métodos permitidos
        header("Access-Control-Allow-Methods: " . implode(', ', $corsConfig['methods']));
        
        // Headers permitidos
        header("Access-Control-Allow-Headers: " . implode(', ', $corsConfig['headers']));
        
        // Permitir credenciales si está configurado
        if ($corsConfig['credentials']) {
            header("Access-Control-Allow-Credentials: true");
        }
        
        // Tiempo de cache para preflight
        header("Access-Control-Max-Age: " . $corsConfig['max_age']);
        
        // Headers expuestos al cliente
        header("Access-Control-Expose-Headers: Authorization, Content-Length, X-Kuma-Revision");
    }

    /**
     * Verifica si un origen está permitido usando wildcards
     */
    private static function isOriginAllowed($origin, $allowedOrigins)
    {
        foreach ($allowedOrigins as $allowed) {
            // Soporte para wildcards básicos
            if (strpos($allowed, '*') !== false) {
                $pattern = str_replace('*', '.*', $allowed);
                if (preg_match("/^$pattern$/", $origin)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Configura headers de seguridad generales
     */
    private static function setSecurityHeaders()
    {
        $config = SecurityConfig::getSecurityHeadersConfig();
        
        // Headers básicos de seguridad
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        
        // Content Security Policy
        if (!empty($config['csp_rules'])) {
            $csp = [];
            foreach ($config['csp_rules'] as $directive => $value) {
                $csp[] = "$directive $value";
            }
            header("Content-Security-Policy: " . implode('; ', $csp));
        }
        
        // Headers específicos de API
        header("Content-Type: application/json; charset=utf-8");
        header("X-API-Version: " . $config['api_version']);
        
        // HSTS solo en HTTPS
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $hsts = "max-age=" . $config['hsts_max_age'];
            if ($config['hsts_subdomains']) {
                $hsts .= "; includeSubDomains";
            }
            if ($config['hsts_preload']) {
                $hsts .= "; preload";
            }
            header("Strict-Transport-Security: $hsts");
        }
    }

    /**
     * Headers específicos para respuestas JSON
     */
    public static function setJSONHeaders()
    {
        header("Content-Type: application/json; charset=utf-8");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    }

    /**
     * Headers para descargas de archivos
     */
    public static function setDownloadHeaders($filename, $contentType = 'application/octet-stream')
    {
        header("Content-Type: $contentType");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    }
}