<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Carga manual de PHPMailer
require_once __DIR__ . '/../thirdparty/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../thirdparty/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../thirdparty/phpmailer/src/Exception.php'; 
require_once __DIR__ . '/../core/Security/SecurityConfig.php';

class EmailService
{
    public static function sendPasswordResetEmail(string $email, string $token): bool
    {
        $config = SecurityConfig::getMailConfig();
        $isDevelopment = SecurityConfig::isDevelopment();

        $resetLink = ($_ENV['FRONTEND_URL'] ?? 'http://localhost:4200') . "/reset-password?token=$token";
        $subject = "Recuperación de contraseña";
        $message = "Hola, has solicitado recuperar tu contraseña. Haz clic en el siguiente enlace para continuar: <a href='{$resetLink}'>Recuperar Contraseña</a>";

        // Si estás en desarrollo, puedes optar por solo loguear el email
        if ($isDevelopment && empty($config['username'])) {
            return self::logEmailToFile($email, $subject, $message);
        }

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption']; // PHPMailer::ENCRYPTION_STARTTLS o `ssl`
            $mail->Port       = $config['port'];
            $mail->CharSet    = 'UTF-8';

            // Emisor y receptor
            $mail->setFrom($config['from_address'], $config['from_name']);
            $mail->addAddress($email);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message); // Versión en texto plano

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("No se pudo enviar el correo. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    private static function logEmailToFile(string $to, string $subject, string $message): bool
    {
        $logMessage = "-----\n";
        $logMessage .= "To: $to\n";
        $logMessage .= "Subject: $subject\n";
        $logMessage .= "Message: $message\n";
        $logMessage .= "-----\n";

        $logsDir = __DIR__ . '/../logs';
        $logFile = $logsDir . '/emails.log';

        if (!is_dir($logsDir)) {
            if (!mkdir($logsDir, 0775, true) && !is_dir($logsDir)) {
                error_log('No se pudo crear directorio de logs: ' . $logsDir);
                return false;
            }
        }

        return file_put_contents($logFile, $logMessage, FILE_APPEND) !== false;
    }
}
