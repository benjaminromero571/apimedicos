<?php

/**
 * Security Headers Service
 * Configura headers de seguridad avanzados
 */
class SecurityHeaders
{
    /**
     * Aplica headers de seguridad básicos
     */
    public static function applyBasicHeaders()
    {
        // Prevenir clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevenir MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Habilitar protección XSS del navegador
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy - no enviar referrer a otros dominios
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Feature Policy - desactivar features no necesarias
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    }

    /**
     * Aplica headers CORS configurables
     */
    public static function applyCORSHeaders($allowedOrigins = ['*'], $allowedMethods = null, $allowedHeaders = null)
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Verificar origen permitido
        if (in_array('*', $allowedOrigins)) {
            // Si se permite cualquier origen
            header('Access-Control-Allow-Origin: *');
        } elseif (!empty($origin) && in_array($origin, $allowedOrigins)) {
            // Si el origen está en la lista permitida
            header('Access-Control-Allow-Origin: ' . $origin);
        } elseif (!empty($allowedOrigins) && !empty($origin)) {
            // Verificar si coincide con algún patrón (para localhost con diferentes puertos)
            foreach ($allowedOrigins as $allowedOrigin) {
                if (self::originMatches($origin, $allowedOrigin)) {
                    header('Access-Control-Allow-Origin: ' . $origin);
                    break;
                }
            }
        }
        
        // Métodos permitidos
        $methods = $allowedMethods ?: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
        header('Access-Control-Allow-Methods: ' . implode(', ', $methods));
        
        // Headers permitidos
        $headers = $allowedHeaders ?: [
            'Origin', 
            'X-Requested-With', 
            'Content-Type', 
            'Accept', 
            'Authorization',
            'X-API-Key'
        ];
        header('Access-Control-Allow-Headers: ' . implode(', ', $headers));
        
        // Permitir credenciales
        header('Access-Control-Allow-Credentials: true');
        
        // Tiempo de cache para preflight
        header('Access-Control-Max-Age: 86400'); // 24 horas
        
        // Headers expuestos al cliente
        header('Access-Control-Expose-Headers: Authorization, Content-Length, X-Kuma-Revision');
    }

    /**
     * Verifica si un origen coincide con un patrón permitido
     */
    private static function originMatches($origin, $allowedOrigin)
    {
        // Coincidencia exacta
        if ($origin === $allowedOrigin) {
            return true;
        }
        
        // Verificar patrones de localhost
        if (strpos($allowedOrigin, 'localhost') !== false || strpos($allowedOrigin, '127.0.0.1') !== false) {
            $allowedParts = parse_url($allowedOrigin);
            $originParts = parse_url($origin);
            
            if (isset($allowedParts['scheme'], $originParts['scheme']) &&
                $allowedParts['scheme'] === $originParts['scheme']) {
                
                $allowedHost = $allowedParts['host'] ?? '';
                $originHost = $originParts['host'] ?? '';
                
                // Permitir localhost y 127.0.0.1 intercambiables
                if (($allowedHost === 'localhost' && $originHost === '127.0.0.1') ||
                    ($allowedHost === '127.0.0.1' && $originHost === 'localhost')) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Aplica Content Security Policy estricto
     */
    public static function applyCSP($customRules = [])
    {
        $defaultRules = [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline'",
            'style-src' => "'self' 'unsafe-inline'",
            'img-src' => "'self' data: https:",
            'connect-src' => "'self'",
            'font-src' => "'self'",
            'object-src' => "'none'",
            'media-src' => "'self'",
            'frame-src' => "'none'"
        ];

        $rules = array_merge($defaultRules, $customRules);
        
        $csp = [];
        foreach ($rules as $directive => $value) {
            $csp[] = $directive . ' ' . $value;
        }

        header('Content-Security-Policy: ' . implode('; ', $csp));
    }

    /**
     * Aplica Strict Transport Security (HSTS)
     */
    public static function applyHSTS($maxAge = 31536000, $includeSubdomains = true, $preload = false)
    {
        $hsts = 'max-age=' . $maxAge;
        
        if ($includeSubdomains) {
            $hsts .= '; includeSubDomains';
        }
        
        if ($preload) {
            $hsts .= '; preload';
        }

        header('Strict-Transport-Security: ' . $hsts);
    }

    /**
     * Headers específicos para API JSON
     */
    public static function applyAPIHeaders()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * Headers de rate limiting informativos
     */
    public static function applyRateLimitHeaders($limit, $remaining, $reset)
    {
        header('X-RateLimit-Limit: ' . $limit);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: ' . $reset);
    }

    /**
     * Header personalizado para identificar la API
     */
    public static function applyAPIIdentification($version = '1.0', $environment = 'production')
    {
        header('X-API-Version: ' . $version);
        header('X-Powered-By: Custom PHP API');
        
        if ($environment === 'development') {
            header('X-Environment: development');
        }
    }

    /**
     * Aplica todos los headers de seguridad recomendados
     */
    public static function applyAllSecurityHeaders($config = [])
    {
        // Headers básicos
        self::applyBasicHeaders();
        
        // CORS
        $corsOrigins = $config['cors_origins'] ?? ['*'];
        $corsMethods = $config['cors_methods'] ?? null;
        $corsHeaders = $config['cors_headers'] ?? null;
        self::applyCORSHeaders($corsOrigins, $corsMethods, $corsHeaders);
        
        // CSP
        $cspRules = $config['csp_rules'] ?? [];
        self::applyCSP($cspRules);
        
        // HSTS (solo si es HTTPS)
        if (self::isHTTPS()) {
            $hstsMaxAge = $config['hsts_max_age'] ?? 31536000;
            $hstsSubdomains = $config['hsts_subdomains'] ?? true;
            $hstsPreload = $config['hsts_preload'] ?? false;
            self::applyHSTS($hstsMaxAge, $hstsSubdomains, $hstsPreload);
        }
        
        // Headers de API
        self::applyAPIHeaders();
        
        // Identificación de API
        $apiVersion = $config['api_version'] ?? '1.0';
        $environment = $config['environment'] ?? 'production';
        self::applyAPIIdentification($apiVersion, $environment);
    }

    /**
     * Verifica si la conexión es HTTPS
     */
    private static function isHTTPS()
    {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            $_SERVER['SERVER_PORT'] == 443 ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
        );
    }

    /**
     * Remueve headers que pueden revelar información del servidor
     */
    public static function removeServerHeaders()
    {
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
            header_remove('Server');
        }
    }

    /**
     * Aplica headers anti-cache para datos sensibles
     */
    public static function applyNoCacheHeaders()
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    }

    /**
     * Headers para forzar descarga de archivos
     */
    public static function applyDownloadHeaders($filename, $contentType = 'application/octet-stream')
    {
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        self::applyNoCacheHeaders();
    }

    /**
     * Middleware principal para aplicar headers de seguridad
     */
    public static function middleware($config = [])
    {
        // Remover headers del servidor
        self::removeServerHeaders();
        
        // Aplicar headers de seguridad
        self::applyAllSecurityHeaders($config);
        
        return true;
    }
}

?>