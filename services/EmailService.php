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

        $resetLink = ($_ENV['FRONTEND_URL'] ?? 'http://localhost:4200') . "/#/reset-password?token=$token";
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

    /**
     * Notifica a los administradores que un cuidador registró una nueva evolución.
     *
     * @param string[] $adminEmails Lista de correos de los administradores destinatarios.
     * @param array{nombre_cuidador:?string,nombre_paciente:?string,detalle:string,fecha_historial:string,id_paciente:?int} $evolucion
     * @return bool true si todos los envíos fueron exitosos.
     */
    public static function sendNewEvolucionToAdmins(array $adminEmails, array $evolucion): bool
    {
        $adminEmails = array_values(array_filter(array_unique($adminEmails), function ($e) {
            return is_string($e) && filter_var($e, FILTER_VALIDATE_EMAIL);
        }));

        if (empty($adminEmails)) {
            return false;
        }

        $cuidador = htmlspecialchars($evolucion['nombre_cuidador'] ?? 'Cuidador', ENT_QUOTES, 'UTF-8');
        $paciente = htmlspecialchars($evolucion['nombre_paciente'] ?? 'Paciente', ENT_QUOTES, 'UTF-8');
        $detalle  = htmlspecialchars($evolucion['detalle'] ?? '', ENT_QUOTES, 'UTF-8');
        $fecha    = htmlspecialchars($evolucion['fecha_historial'] ?? '', ENT_QUOTES, 'UTF-8');

        $idPaciente = isset($evolucion['id_paciente']) ? (int)$evolucion['id_paciente'] : 0;
        $frontendUrl = rtrim($_ENV['FRONTEND_URL'] ?? 'http://localhost:4200', '/');
        $expedienteLink = $idPaciente > 0
            ? "{$frontendUrl}/#/dashboard/expediente/{$idPaciente}"
            : null;

        $linkHtml = $expedienteLink
            ? "<p><a href=\"{$expedienteLink}\" style=\"display:inline-block;padding:10px 18px;background-color:#4285F4;color:#fff;text-decoration:none;border-radius:6px;\">Ver expediente del paciente</a></p>"
            : '';

        $subject = "Nueva evolución registrada por {$cuidador}";
        $message = "
            <p>Se ha registrado una nueva evolución por parte de un cuidador.</p>
            <ul>
              <li><strong>Cuidador:</strong> {$cuidador}</li>
              <li><strong>Paciente:</strong> {$paciente}</li>
              <li><strong>Fecha:</strong> {$fecha}</li>
            </ul>
            <p><strong>Detalle:</strong></p>
            <p>{$detalle}</p>
            {$linkHtml}
        ";

        $config = SecurityConfig::getMailConfig();
        $isDevelopment = SecurityConfig::isDevelopment();

        if ($isDevelopment && empty($config['username'])) {
            $ok = true;
            foreach ($adminEmails as $to) {
                $ok = self::logEmailToFile($to, $subject, $message) && $ok;
            }
            return $ok;
        }

        $allOk = true;
        foreach ($adminEmails as $to) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = $config['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $config['username'];
                $mail->Password   = $config['password'];
                $mail->SMTPSecure = $config['encryption'];
                $mail->Port       = $config['port'];
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($config['from_address'], $config['from_name']);
                $mail->addAddress($to);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $message;
                $mail->AltBody = strip_tags($message);

                $mail->send();
            } catch (Exception $e) {
                error_log("No se pudo notificar a {$to}. Mailer Error: {$mail->ErrorInfo}");
                $allOk = false;
            }
        }
        return $allOk;
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
