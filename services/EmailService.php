<?php

class EmailService
{
    public static function sendPasswordResetEmail(string $email, string $token): bool
    {
        $resetLink = "http://localhost:4200/reset-password?token=$token";
        $subject = "Recuperación de contraseña";
        $message = "Hola, has solicitado recuperar tu contraseña. Haz clic en el siguiente enlace para continuar: $resetLink";
        $headers = "From: no-reply@gico.com" . "
" .
            "Reply-To: no-reply@gico.com" . "
" .
            "X-Mailer: PHP/" . phpversion();

        // For now, we will log the email to a file
        $logMessage = "-----
";
        $logMessage .= "To: $email
";
        $logMessage .= "Subject: $subject
";
        $logMessage .= "Message: $message
";
        $logMessage .= "-----
";

        file_put_contents(__DIR__ . '/../logs/emails.log', $logMessage, FILE_APPEND);

        return true;
    }

    


}
