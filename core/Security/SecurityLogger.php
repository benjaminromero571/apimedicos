<?php

/**
 * Security Logger Service
 * Registra eventos de seguridad para auditoría
 */
class SecurityLogger
{
    private static $logFile = null;
    private static $enabled = true;

    /**
     * Inicializa el logger
     */
    public static function init($logFile = null)
    {
        if ($logFile) {
            self::$logFile = $logFile;
        } else {
            self::$logFile = __DIR__ . '/../../logs/security.log';
        }

        // Crear directorio si no existe
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Registra un evento de autenticación exitosa
     */
    public static function logSuccessfulAuth($userId, $email, $ip)
    {
        self::log('AUTH_SUCCESS', [
            'user_id' => $userId,
            'email' => $email,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    /**
     * Registra un intento fallido de autenticación
     */
    public static function logFailedAuth($email, $ip, $reason = 'Invalid credentials')
    {
        self::log('AUTH_FAILED', [
            'email' => $email,
            'ip' => $ip,
            'reason' => $reason,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    /**
     * Registra acceso denegado por permisos
     */
    public static function logAccessDenied($userId, $resource, $ip, $reason = 'Insufficient permissions')
    {
        self::log('ACCESS_DENIED', [
            'user_id' => $userId,
            'resource' => $resource,
            'ip' => $ip,
            'reason' => $reason,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    /**
     * Registra rate limiting
     */
    public static function logRateLimit($ip, $endpoint, $limit_type)
    {
        self::log('RATE_LIMIT', [
            'ip' => $ip,
            'endpoint' => $endpoint,
            'limit_type' => $limit_type,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    /**
     * Registra uso de token expirado
     */
    public static function logExpiredToken($ip, $endpoint)
    {
        self::log('EXPIRED_TOKEN', [
            'ip' => $ip,
            'endpoint' => $endpoint,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    /**
     * Registra token inválido o malformado
     */
    public static function logInvalidToken($ip, $endpoint, $token_part = null)
    {
        self::log('INVALID_TOKEN', [
            'ip' => $ip,
            'endpoint' => $endpoint,
            'token_preview' => $token_part ? substr($token_part, 0, 20) . '...' : null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    /**
     * Registra cambio de contraseña
     */
    public static function logPasswordChange($userId, $ip)
    {
        self::log('PASSWORD_CHANGE', [
            'user_id' => $userId,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    /**
     * Registra creación de nuevo usuario
     */
    public static function logUserCreation($newUserId, $creatorId, $ip)
    {
        self::log('USER_CREATION', [
            'new_user_id' => $newUserId,
            'creator_id' => $creatorId,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    /**
     * Registra eliminación de usuario
     */
    public static function logUserDeletion($deletedUserId, $deleterId, $ip)
    {
        self::log('USER_DELETION', [
            'deleted_user_id' => $deletedUserId,
            'deleter_id' => $deleterId,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    /**
     * Registra intentos sospechosos
     */
    public static function logSuspiciousActivity($type, $data, $ip)
    {
        self::log('SUSPICIOUS_ACTIVITY', array_merge([
            'type' => $type,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ], $data));
    }

    /**
     * Registra eventos de administración
     */
    public static function logAdminAction($adminId, $action, $target, $ip)
    {
        self::log('ADMIN_ACTION', [
            'admin_id' => $adminId,
            'action' => $action,
            'target' => $target,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    /**
     * Método principal de logging
     */
    private static function log($event, $data)
    {
        if (!self::$enabled) {
            return;
        }

        if (!self::$logFile) {
            self::init();
        }

        $timestamp = date('Y-m-d H:i:s');
        $requestId = self::getRequestId();
        
        $logEntry = [
            'timestamp' => $timestamp,
            'request_id' => $requestId,
            'event' => $event,
            'data' => $data
        ];

        $logLine = json_encode($logEntry) . PHP_EOL;

        // Escribir al archivo de log de forma segura
        if (file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX) === false) {
            error_log("Failed to write to security log: " . self::$logFile);
        }
    }

    /**
     * Genera un ID único para la request
     */
    private static function getRequestId()
    {
        static $requestId = null;
        
        if ($requestId === null) {
            $requestId = uniqid('req_', true);
        }
        
        return $requestId;
    }

    /**
     * Obtiene los últimos eventos de seguridad
     */
    public static function getRecentEvents($limit = 100, $eventType = null)
    {
        if (!self::$logFile || !file_exists(self::$logFile)) {
            return [];
        }

        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if (!$lines) {
            return [];
        }

        $events = [];
        $count = 0;

        // Leer desde el final del archivo
        for ($i = count($lines) - 1; $i >= 0 && $count < $limit; $i--) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $event = json_decode($line, true);
            if (!$event) continue;

            // Filtrar por tipo de evento si se especifica
            if ($eventType && $event['event'] !== $eventType) {
                continue;
            }

            $events[] = $event;
            $count++;
        }

        return $events;
    }

    /**
     * Analiza intentos de login fallidos para detectar ataques
     */
    public static function analyzeFailedLogins($timeWindow = 3600)
    {
        $events = self::getRecentEvents(1000, 'AUTH_FAILED');
        $cutoff = time() - $timeWindow;
        
        $ipCounts = [];
        $emailCounts = [];

        foreach ($events as $event) {
            $timestamp = strtotime($event['timestamp']);
            if ($timestamp < $cutoff) continue;

            $ip = $event['data']['ip'] ?? 'unknown';
            $email = $event['data']['email'] ?? 'unknown';

            $ipCounts[$ip] = ($ipCounts[$ip] ?? 0) + 1;
            $emailCounts[$email] = ($emailCounts[$email] ?? 0) + 1;
        }

        return [
            'suspicious_ips' => array_filter($ipCounts, function($count) { return $count >= 10; }),
            'targeted_emails' => array_filter($emailCounts, function($count) { return $count >= 5; }),
            'time_window' => $timeWindow
        ];
    }

    /**
     * Habilita o deshabilita el logging
     */
    public static function setEnabled($enabled)
    {
        self::$enabled = (bool) $enabled;
    }

    /**
     * Rota los logs (elimina logs antiguos)
     */
    public static function rotateLogs($daysToKeep = 30)
    {
        if (!self::$logFile || !file_exists(self::$logFile)) {
            return;
        }

        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) return;

        $cutoff = time() - ($daysToKeep * 24 * 3600);
        $newLines = [];

        foreach ($lines as $line) {
            $event = json_decode($line, true);
            if ($event && isset($event['timestamp'])) {
                $timestamp = strtotime($event['timestamp']);
                if ($timestamp >= $cutoff) {
                    $newLines[] = $line;
                }
            }
        }

        file_put_contents(self::$logFile, implode(PHP_EOL, $newLines) . PHP_EOL);
    }
}

?>