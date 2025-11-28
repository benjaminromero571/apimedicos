<?php

/**
 * Rate Limiting Service
 * Previene ataques de fuerza bruta y DDoS
 */
class RateLimitService
{
    private static $limits = [
        'login' => ['requests' => 5, 'window' => 300], // 5 intentos en 5 minutos
        'api' => ['requests' => 100, 'window' => 60],   // 100 requests por minuto
        'general' => ['requests' => 1000, 'window' => 3600] // 1000 requests por hora
    ];

    private static $storage = [];

    /**
     * Verifica límite con configuración dinámica
     */
    public static function checkLimitWithConfig($ip, $config)
    {
        $key = 'dynamic:' . $ip;
        $now = time();
        $window = $config['time_window'] ?? 3600;
        $maxRequests = $config['max_requests'] ?? 100;

        // Limpiar registros expirados
        self::cleanExpiredRecords($key, $now, $window);

        // Obtener conteo actual
        $count = self::getRequestCount($key, $now, $window);

        $allowed = $count < $maxRequests;
        $remaining = max(0, $maxRequests - $count);
        $resetTime = $now + $window;

        if ($allowed) {
            // Registrar nuevo request
            self::recordRequest($key, $now);
            $remaining--; // Decrementar por el request actual
        }

        return [
            'allowed' => $allowed,
            'requests_made' => $count,
            'remaining' => max(0, $remaining),
            'reset_time' => $resetTime,
            'limit' => $maxRequests
        ];
    }

    /**
     * Verifica si una IP está dentro del límite de rate limiting
     */
    public static function checkLimit($ip, $type = 'general')
    {
        if (!isset(self::$limits[$type])) {
            $type = 'general';
        }

        $limit = self::$limits[$type];
        $key = $type . ':' . $ip;
        $now = time();

        // Limpiar registros expirados
        self::cleanExpiredRecords($key, $now, $limit['window']);

        // Obtener conteo actual
        $count = self::getRequestCount($key, $now, $limit['window']);

        if ($count >= $limit['requests']) {
            return false;
        }

        // Registrar nuevo request
        self::recordRequest($key, $now);

        return true;
    }

    /**
     * Obtiene información sobre el límite actual
     */
    public static function getLimitInfo($ip, $type = 'general')
    {
        if (!isset(self::$limits[$type])) {
            $type = 'general';
        }

        $limit = self::$limits[$type];
        $key = $type . ':' . $ip;
        $now = time();

        self::cleanExpiredRecords($key, $now, $limit['window']);
        $count = self::getRequestCount($key, $now, $limit['window']);

        return [
            'limit' => $limit['requests'],
            'remaining' => max(0, $limit['requests'] - $count),
            'reset_time' => $now + $limit['window'],
            'window' => $limit['window']
        ];
    }

    /**
     * Respuesta de rate limit excedido
     */
    public static function rateLimitExceeded($ip, $type = 'general')
    {
        $info = self::getLimitInfo($ip, $type);
        
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        header('X-RateLimit-Limit: ' . $info['limit']);
        header('X-RateLimit-Remaining: ' . $info['remaining']);
        header('X-RateLimit-Reset: ' . $info['reset_time']);
        header('Retry-After: ' . $info['window']);
        
        echo json_encode([
            'success' => false,
            'error' => 'Rate limit exceeded',
            'message' => 'Demasiadas peticiones. Intente nuevamente en ' . $info['window'] . ' segundos.',
            'retry_after' => $info['window'],
            'code' => 429
        ]);
        exit;
    }

    /**
     * Obtiene la IP del cliente considerando proxies
     */
    public static function getClientIP()
    {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Configura límites personalizados
     */
    public static function setLimit($type, $requests, $window)
    {
        self::$limits[$type] = [
            'requests' => $requests,
            'window' => $window
        ];
    }

    /**
     * Limpia registros expirados
     */
    private static function cleanExpiredRecords($key, $now, $window)
    {
        if (!isset(self::$storage[$key])) {
            return;
        }

        $cutoff = $now - $window;
        self::$storage[$key] = array_filter(self::$storage[$key], function($timestamp) use ($cutoff) {
            return $timestamp > $cutoff;
        });
    }

    /**
     * Obtiene conteo de requests en la ventana de tiempo
     */
    private static function getRequestCount($key, $now, $window)
    {
        if (!isset(self::$storage[$key])) {
            return 0;
        }

        $cutoff = $now - $window;
        return count(array_filter(self::$storage[$key], function($timestamp) use ($cutoff) {
            return $timestamp > $cutoff;
        }));
    }

    /**
     * Registra un nuevo request
     */
    private static function recordRequest($key, $timestamp)
    {
        if (!isset(self::$storage[$key])) {
            self::$storage[$key] = [];
        }
        
        self::$storage[$key][] = $timestamp;
    }

    /**
     * Whitelist de IPs que no tienen rate limiting
     */
    private static $whitelist = ['127.0.0.1', '::1'];

    /**
     * Verifica si una IP está en whitelist
     */
    public static function isWhitelisted($ip)
    {
        return in_array($ip, self::$whitelist);
    }

    /**
     * Agrega IP a whitelist
     */
    public static function addToWhitelist($ip)
    {
        if (!in_array($ip, self::$whitelist)) {
            self::$whitelist[] = $ip;
        }
    }

    /**
     * Middleware principal de rate limiting
     */
    public static function middleware($type = 'api')
    {
        $ip = self::getClientIP();

        // Verificar whitelist
        if (self::isWhitelisted($ip)) {
            return true;
        }

        // Verificar límite
        if (!self::checkLimit($ip, $type)) {
            self::rateLimitExceeded($ip, $type);
            return false;
        }

        // Agregar headers informativos
        $info = self::getLimitInfo($ip, $type);
        header('X-RateLimit-Limit: ' . $info['limit']);
        header('X-RateLimit-Remaining: ' . $info['remaining']);
        header('X-RateLimit-Reset: ' . $info['reset_time']);

        return true;
    }
}

?>