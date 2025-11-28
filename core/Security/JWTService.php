<?php

/**
 * JWT Service - Manejo seguro de tokens JWT
 * Implementa firma HMAC SHA-256 para autenticación
 */
class JWTService
{
    private static $secret;
    private static $algorithm = 'HS256';
    private static $expiration = 3600; // 1 hora por defecto

    /**
     * Inicializa la clave secreta
     */
    public static function init($secret = null)
    {
        if ($secret) {
            self::$secret = $secret;
        } else {
            // Usar una clave fija para desarrollo (cambiar en producción)
            self::$secret = 'mi_clave_secreta_jwt_2024_muy_segura_para_desarrollo';
        }
        
    }

    /**
     * Genera un token JWT
     */
    public static function generateToken($payload, $expiration = null)
    {
        if (!self::$secret) {
            self::init();
        }

        $header = [
            'typ' => 'JWT',
            'alg' => self::$algorithm
        ];

        $now = time();
        $exp = $expiration ?: $now + self::$expiration;

        $payload = array_merge($payload, [
            'iat' => $now,    // Issued at
            'exp' => $exp,    // Expiration time
            'nbf' => $now,    // Not before
            'jti' => self::generateJTI() // JWT ID único
        ]);

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = self::generateSignature($headerEncoded . '.' . $payloadEncoded);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    /**
     * Valida y decodifica un token JWT
     */
    public static function validateToken($token)
    {
        if (!self::$secret) {
            self::init();
        }

        // Limpiar token de espacios
        $token = trim($token);
        

        try {
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                throw new Exception('Token format invalid');
            }

            [$headerEncoded, $payloadEncoded, $signature] = $parts;

            // Verificar firma
            $expectedSignature = self::generateSignature($headerEncoded . '.' . $payloadEncoded);

            
            if (!hash_equals($signature, $expectedSignature)) {
                throw new Exception('Invalid signature');
            }

            // Decodificar payload
            $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
            
            if (!$payload) {
                throw new Exception('Invalid payload');
            }

            // Verificar expiración
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                throw new Exception('Token expired');
            }

            // Verificar not before
            if (isset($payload['nbf']) && $payload['nbf'] > time()) {
                throw new Exception('Token not yet valid');
            }

            return $payload;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Refresca un token (genera uno nuevo con datos actualizados)
     */
    public static function refreshToken($token, $newData = [])
    {
        $payload = self::validateToken($token);
        
        if (!$payload) {
            return false;
        }

        // Remover campos de tiempo para regenerar
        unset($payload['iat'], $payload['exp'], $payload['nbf'], $payload['jti']);

        // Agregar nuevos datos
        $payload = array_merge($payload, $newData);

        return self::generateToken($payload);
    }

    /**
     * Extrae el token del header Authorization
     */
    public static function extractTokenFromHeader()
    {
        $headers = self::getAllHeaders();
        
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Genera una firma HMAC segura
     */
    private static function generateSignature($data)
    {
        return self::base64UrlEncode(hash_hmac('sha256', $data, self::$secret, true));
    }

    /**
     * Codificación Base64 URL-safe
     */
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodificación Base64 URL-safe
     */
    private static function base64UrlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    /**
     * Genera un JWT ID único
     */
    private static function generateJTI()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Genera un secreto seguro
     */
    private static function generateSecureSecret()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Obtiene todos los headers HTTP
     */
    private static function getAllHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * Configurar tiempo de expiración por defecto
     */
    public static function setDefaultExpiration($seconds)
    {
        self::$expiration = $seconds;
    }

    /**
     * Obtiene información del token sin validar (para debugging)
     */
    public static function getTokenInfo($token)
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }

        try {
            $header = json_decode(self::base64UrlDecode($parts[0]), true);
            $payload = json_decode(self::base64UrlDecode($parts[1]), true);
            
            return [
                'header' => $header,
                'payload' => $payload,
                'expired' => isset($payload['exp']) ? $payload['exp'] < time() : false
            ];
        } catch (Exception $e) {
            return false;
        }
    }
}

?>