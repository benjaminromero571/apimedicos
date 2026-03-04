<?php
require_once __DIR__ . '/../libs/phpmailer/Exception.php';
require_once __DIR__ . '/../libs/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../libs/phpmailer/SMTP.php';
require_once __DIR__ . '/../core/Security/JWTService.php';
require_once __DIR__ . '/../services/UserService.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;   

class AuthController extends BaseController
{
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
    }

    /**
     * Maneja el login de usuarios
     */
    public function login($params = [])
    {
        try {
            $data = $this->getJsonInput();
            
            if (!$data) {
                $this->jsonError("Datos JSON requeridos", 400);
                return;
            }

            // Validar campos requeridos
            $required = ['email', 'password'];
            $missing = $this->validateRequired($data, $required);

            if (!empty($missing)) {
                $this->jsonError("Campos requeridos faltantes", 400, $missing);
                return;
            }

            // Sanitizar email
            $email = $this->sanitizeString($data['email']);

            // Autenticar usuario usando el service
            $result = $this->userService->authenticateUser($email, $data['password']);

            if (!$result['success']) {
                $this->jsonError($result['message'], 401);
                return;
            }

            $userDto = $result['data'];

            // Crear payload para JWT
            $payload = [
                'user_id' => $userDto->id,
                'email' => $userDto->email,
                'role' => $userDto->rol ?? 'Cuidador',
                'name' => $userDto->name,
                'iat' => time(),
                'exp' => time() + 86400 // 24 horas
            ];

            // Generar JWT token
            $token = JWTService::generateToken($payload);

            $this->jsonResponse([
                'user' => $userDto->toArray(),
                'token' => $token,
                'expires_at' => date('Y-m-d H:i:s', $payload['exp'])
            ], "Login exitoso");

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->jsonError("Error en el login: " . $e->getMessage(), 500);
        }
    }

    public function forgotPassword($params = []) {
    try {
        $data = $this->getJsonInput(); // Obtener datos de Angular
        $email = $this->sanitizeString($data['email']);

        // 1. Verificar si el usuario existe (usando tu UserService)
        $result = $this->userService->getUserByEmail($email);
        
        if ($result['success']) {
            $user = $result['data'];
            
            // 2. Generar token único
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            // 3. Guardar en la base de datos
            // Ajusta esta query según cómo manejes tu conexión a DB actual
            $this->db->query("INSERT INTO password_resets (email, token, expires_at) 
                             VALUES (?, ?, ?)", [$email, $token, $expires]);
            
            // 4. Enviar el correo
            $link = "http://localhost:4200/#/reset-password?token=" . $token;
            $this->sendEmail($email, "Recuperar Contraseña", 
                "Hola, para restablecer tu clave haz clic aquí: <a href='$link'>$link</a>");
        }

        // Siempre respondemos éxito por seguridad
        $this->jsonResponse(null, "Si el correo existe, recibirás instrucciones.");

    } catch (Exception $e) {
        $this->jsonError("Error: " . $e->getMessage(), 500);
    }
}

    public function resetPassword($request) {
    $token = $request->token;
    $newPassword = $request->password;
    
    // 1. Validar el token y que no haya expirado
    $reset = $this->db->query("SELECT email FROM password_resets 
                              WHERE token = ? AND expires_at > NOW()", [$token])->first();
    
    if (!$reset) {
        return ["status" => "error", "message" => "El enlace ha expirado o es inválido."];
    }
    
    // 2. Encriptar la nueva contraseña con BCRYPT
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    // 3. Actualizar la tabla 'users'
    $this->db->query("UPDATE users SET password = ? WHERE email = ?", [$hashedPassword, $reset->email]);
    
    // 4. Borrar el token para que no se use de nuevo
    $this->db->query("DELETE FROM password_resets WHERE email = ?", [$reset->email]);
    
    return ["status" => "success", "message" => "Contraseña actualizada correctamente."];
}
    /**
     * Verifica un JWT token
     */
    public function verify($params = [])
    {
        try {
            $token = $this->getAuthToken();

            if (!$token) {
                $this->jsonError("Token requerido", 401);
                return;
            }

            // Verificar JWT token
            $payload = JWTService::validateToken($token);

            if (!$payload) {
                $this->jsonError("Token inválido", 401);
                return;
            }

            // Verificar usuario aún existe usando el service
            $result = $this->userService->getUserById($payload['user_id']);
            
            if (!$result['success']) {
                $this->jsonError("Usuario no encontrado", 401);
                return;
            }

            $userDto = $result['data'];

            $this->jsonResponse([
                'valid' => true,
                'user' => $userDto->toArraySummary(),
                'token_payload' => $payload,
                'expires_at' => date('Y-m-d H:i:s', $payload['exp'])
            ]);

        } catch (Exception $e) {
            error_log("Token verification error: " . $e->getMessage());
            $this->jsonError("Error al verificar token: " . $e->getMessage(), 500);
        }
    }

    /**
     * Refresh token endpoint
     */
    public function refresh($params = [])
    {
        try {
            $token = $this->getAuthToken();

            if (!$token) {
                $this->jsonError("Token requerido", 401);
                return;
            }

            // Verificar token actual
            $payload = JWTService::validateToken($token);

            if (!$payload) {
                $this->jsonError("Token inválido", 401);
                return;
            }

            // Verificar usuario usando el service
            $result = $this->userService->getUserById($payload['user_id']);
            
            if (!$result['success']) {
                $this->jsonError("Usuario no válido para refresh", 401);
                return;
            }

            $userDto = $result['data'];

            // Generar nuevo token
            $newPayload = [
                'user_id' => $userDto->id,
                'email' => $userDto->email,
                'role' => $userDto->rol ?? 'Cuidador',
                'name' => $userDto->name,
                'iat' => time(),
                'exp' => time() + 86400 // 24 horas
            ];

            $newToken = JWTService::generateToken($newPayload);

            $this->jsonResponse([
                'token' => $newToken,
                'user' => $userDto->toArraySummary(),
                'expires_at' => date('Y-m-d H:i:s', $newPayload['exp'])
            ], "Token renovado exitosamente");

        } catch (Exception $e) {
            error_log("Token refresh error: " . $e->getMessage());
            $this->jsonError("Error al renovar token: " . $e->getMessage(), 500);
        }
    }

    /**
     * Registro de nuevos usuarios
     */
    public function register($params = [])
    {
        try {
            $data = $this->getJsonInput();
            
            if (!$data) {
                $this->jsonError("Datos JSON requeridos", 400);
                return;
            }

            // Validar campos requeridos
            $required = ['name', 'email', 'password', 'rol'];
            $missing = $this->validateRequired($data, $required);

            if (!empty($missing)) {
                $this->jsonError("Campos requeridos faltantes", 400, $missing);
                return;
            }

            // Crear usuario usando el service
            $result = $this->userService->createUser($data);

            if (!$result['success']) {
                $statusCode = isset($result['errors']) ? 400 : 500;
                $errorMessage = $result['message'];
                
                if (isset($result['errors'])) {
                    $errorMessage .= ': ' . implode(', ', array_values($result['errors']));
                }
                
                $this->jsonError($errorMessage, $statusCode);
                return;
            }

            $userDto = $result['data'];

            // Crear payload para JWT
            $payload = [
                'user_id' => $userDto->id,
                'email' => $userDto->email,
                'role' => $userDto->rol,
                'name' => $userDto->name,
                'iat' => time(),
                'exp' => time() + 86400 // 24 horas
            ];

            // Generar JWT token
            $token = JWTService::generateToken($payload);

            $this->jsonResponse([
                'user' => $userDto->toArray(),
                'token' => $token,
                'expires_at' => date('Y-m-d H:i:s', $payload['exp'])
            ], "Usuario registrado exitosamente", 201);

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->jsonError("Error en el registro: " . $e->getMessage(), 500);
        }
    }

    /**
     * Logout endpoint
     */
    public function logout($params = [])
    {
        try {
            // En una implementación real, aquí se podría invalidar el token
            // agregándolo a una lista negra
            $this->jsonResponse(['message' => 'Logout exitoso']);

        } catch (Exception $e) {
            $this->jsonError("Error en logout: " . $e->getMessage(), 500);
        }
    }

    /**
     * Verifica si un email está disponible
     */
    public function checkEmail($params = [])
    {
        try {
            $email = $params['email'] ?? $_GET['email'] ?? '';
            
            if (empty($email)) {
                $this->jsonError("Email requerido", 400);
                return;
            }

            $result = $this->userService->getUserByEmail($email);
            
            if ($result['success']) {
                // Email ya existe
                $this->jsonResponse([
                    'available' => false,
                    'exists' => true
                ], "Email ya registrado");
            } else {
                // Email disponible
                $this->jsonResponse([
                    'available' => true,
                    'exists' => false
                ], "Email disponible");
            }

        } catch (Exception $e) {
            $this->jsonError("Error al verificar email: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene el token de autorización de los headers
     */
    private function getAuthToken()
    {
        $headers = getallheaders();
        $authHeader = '';
        
        // Buscar header de autorización (case-insensitive)
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $authHeader = $value;
                break;
            }
        }

        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            return substr($authHeader, 7); // Remover "Bearer "
        }

        return null;
    }

    private function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del Servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Servidor de Gmail
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tu-correo@gmail.com'; // Cambia esto
        $mail->Password   = 'tu-clave-de-aplicacion'; // Contraseña de aplicación de 16 dígitos
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Destinatarios
        $mail->setFrom('no-reply@tuapp.com', 'Sistema Medico GICO');
        $mail->addAddress($to);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->CharSet = 'UTF-8';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

    /**
     * Obtiene la IP del cliente
     */
    private function getClientIP()
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

?>