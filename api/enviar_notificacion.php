<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ⭐ Cargar PHPMailer manualmente
require_once '../phpmailer/src/Exception.php';
require_once '../phpmailer/src/PHPMailer.php';
require_once '../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../classes/Database.php';
require_once '../classes/Acompanante.php';
require_once '../classes/Usuario.php';

$data = json_decode(file_get_contents('php://input'), true);

$usuario_id = intval($data['usuario_id'] ?? 0);
$dominio = $data['dominio'] ?? '';

if ($usuario_id > 0 && $dominio) {
    $db = new Database();
    $acompananteObj = new Acompanante($db);
    $usuarioObj = new Usuario($db);
    
    // Cargar datos del usuario
    $usuarioObj->cargarPorId($usuario_id);
    $nombreUsuario = $usuarioObj->getNombre();
    $emailUsuario = $usuarioObj->getEmail();
    
    // Obtener acompañantes con notificaciones activas
    $acompanantes = $acompananteObj->obtenerPorUsuario($usuario_id);
    $notificados = 0;
    $errores = [];
    
    foreach ($acompanantes as $acomp) {
        // Solo enviar a los que tienen notificar = 1
        if ($acomp['notificar'] == 1) {
            $resultado = enviarCorreoSMTP(
                $acomp['email'],
                $acomp['nombre'],
                $nombreUsuario,
                $emailUsuario,
                $dominio
            );
            
            if ($resultado['success']) {
                $notificados++;
            } else {
                $errores[] = $resultado['error'];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'notificados' => $notificados,
        'errores' => $errores,
        'message' => "Notificación enviada a $notificados acompañante(s)"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos'
    ]);
}

function enviarCorreoSMTP($emailDestino, $nombreDestino, $nombreUsuario, $emailUsuario, $dominio) {
    $mail = new PHPMailer(true);
    
    try {
        // ⚠️ CONFIGURACIÓN - CAMBIAR ESTOS DATOS
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'cloudafe@gmail.com'; // ⚠️ CAMBIAR
        $mail->Password = 'fsep fzpm ojob artr'; // ⚠️ CONTRASEÑA DE APLICACIÓN
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Configuración del correo
        $mail->setFrom('cloudafe@gmail.com', 'BlockBet'); // ⚠️ CAMBIAR
        $mail->addAddress($emailDestino, $nombreDestino);
        $mail->addReplyTo($emailUsuario, $nombreUsuario);
        
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = " BlockBet - Intento de acceso detectado";
        
        $fecha = date('d/m/Y H:i:s');
        
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #ffffff; }
                .header { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
                .alert-box { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .info-box { background: #e0e7ff; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .footer { background: #374151; color: white; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; font-size: 0.9rem; }
                .btn { display: inline-block; background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🛡️ BlockBet</h1>
                    <p>Alerta de Intento de Acceso</p>
                </div>
                
                <div class='content'>
                    <p>Hola <strong>$nombreDestino</strong>,</p>
                    
                    <p>Te enviamos esta notificación porque eres parte de la red de apoyo de <strong>$nombreUsuario</strong> en BlockBet.</p>
                    
                    <div class='alert-box'>
                        <strong> Intento de Acceso Detectado</strong><br>
                        <strong>Usuario:</strong> $nombreUsuario<br>
                        <strong>Sitio bloqueado:</strong> $dominio<br>
                        <strong>Fecha y hora:</strong> $fecha
                    </div>
                    
                    <div class='info-box'>
                        <strong> ¿Qué puedes hacer?</strong><br>
                        • Envía un mensaje de apoyo a $nombreUsuario<br>
                        • Recuérdale sus objetivos y motivaciones<br>
                        • Mantén una conversación sin juzgar<br>
                        • Hazle saber que no está solo/a en este proceso
                    </div>
                    
                    <p>Este intento fue <strong>bloqueado exitosamente</strong> por el sistema. Sin embargo, tu apoyo es fundamental para su proceso de recuperación.</p>
                    
                    <p style='text-align: center;'>
                        <a href='mailto:$emailUsuario' class='btn'>📧 Contactar a $nombreUsuario</a>
                    </p>
                </div>
                
                <div class='footer'>
                    <p>Este mensaje fue enviado automáticamente por BlockBet</p>
                    <p>Sistema de prevención de ludopatía digital</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}
?>