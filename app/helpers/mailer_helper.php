<?php
// app/helpers/mailer_helper.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

function sendVerificationEmail($recipientEmail, $subject, $code) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del Servidor SMTP de Hostinger
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        
        // --- LA CORRECCIÓN ESTÁ AQUÍ ---
        // Ambas líneas deben usar el mismo correo de Hostinger
        $mail->Username   = 'soporte@enyooi.com'; // <<== TU EMAIL DE HOSTINGER
        $mail->Password   = 'Soporte2003@'; // <<== LA CONTRASEÑA DE ESE EMAIL

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // El remitente DEBE COINCIDIR con el Username
        $mail->setFrom('soporte@enyooi.com', 'Enyooi'); // <<== USA EL MISMO EMAIL DE ARRIBA
        
        // Destinatario
        $mail->addAddress($recipientEmail);

        // Contenido del Correo
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = "
        <html>
        <body>
            <p>Hola,</p>
            <p>Tu código de verificación para Enyooi es:</p>
            <h2 style='text-align:center; color: #7c5cff;'>$code</h2>
            <p>Este código es válido por 15 minutos.</p>
            <p><em>El equipo de Enyooi</em></p>
        </body>
        </html>
        ";
        $mail->AltBody = "Tu código de verificación es: $code";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer (Hostinger) no pudo enviar el correo. Error: {$mail->ErrorInfo}");
        return false;
    }
}