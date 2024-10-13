<?php
session_start();
require '../config/config.php'; 
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Verificar si se ha recibido el ID de la pareja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pareja_id'])) {
    $pareja_id = $_POST['pareja_id'];

    // Actualizar la tabla `usuarios` para eliminar la relación de pareja
    // Al usuario actual se le pone `pareja_tesis = 0` y al compañero `pareja_tesis = -1`
    $sql_update_usuarios = "UPDATE usuarios 
                            SET pareja_tesis = CASE 
                                WHEN id = ? THEN 0 
                                WHEN id = ? THEN -1 
                            END
                            WHERE id IN (?, ?)";
    $stmt_update_usuarios = $conn->prepare($sql_update_usuarios);
    $stmt_update_usuarios->bind_param("iiii", $usuario_id, $pareja_id, $usuario_id, $pareja_id);
    $stmt_update_usuarios->execute();

    // Actualizar el campo `pareja_id` de la tabla `tema` para el registro más reciente
    $sql_update_tema = "UPDATE tema 
                        SET pareja_id = -1 
                        WHERE usuario_id = ? 
                        AND pareja_id = ? 
                        AND id = (
                            SELECT MAX(id) FROM tema 
                            WHERE usuario_id = ? AND pareja_id = ?
                        )";
    $stmt_update_tema = $conn->prepare($sql_update_tema);
    $stmt_update_tema->bind_param("iiii", $pareja_id, $usuario_id, $pareja_id, $usuario_id);
    $stmt_update_tema->execute();

    // Obtener información de la pareja eliminada para el correo
    $sql_pareja_info = "SELECT nombres, apellidos, email FROM usuarios WHERE id = ?";
    $stmt_pareja_info = $conn->prepare($sql_pareja_info);
    $stmt_pareja_info->bind_param("i", $pareja_id);
    $stmt_pareja_info->execute();
    $result_pareja_info = $stmt_pareja_info->get_result();
    $pareja_info = $result_pareja_info->fetch_assoc();

    // Enviar correo de notificación a la pareja eliminada
    //enviarCorreoParejaEliminada($pareja_info['nombres'], $pareja_info['apellidos'], $pareja_info['email'], $_SESSION['usuario_nombre'], $_SESSION['usuario_apellido']);

    // Redirigir al usuario con mensaje de éxito
    header("Location: enviar-tema.php?status=pareja_eliminada");
    exit();
} else {
    header("Location: enviar-tema.php");
    exit();
}

// Función para enviar el correo de notificación
function enviarCorreoParejaEliminada($nombre_pareja, $apellido_pareja, $correo_pareja, $nombre_postulante, $apellido_postulante) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP de Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tds.titulacion.istjba@gmail.com';
        $mail->Password   = 'ecic zfih ifqj utgv'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Configuración del charset
        $mail->CharSet    = 'UTF-8';

        // Configuración del remitente y destinatario
        $mail->setFrom('tds.titulacion.istjba@gmail.com', 'Instituto Superior Tecnológico');
        $mail->addAddress($correo_pareja);

        $mail->isHTML(true);

        // Asunto y cuerpo del correo
        $mail->Subject = 'Notificación de Eliminación de Pareja - proceso de titulación';
        $mail->Body    = '<p>Estimado(a) ' . $nombre_pareja . ' ' . $apellido_pareja . ',</p>
                          <p>Le informamos que su pareja del proceso de titulación, ' . $nombre_postulante . ' ' . $apellido_postulante . ', ha decidido eliminar su solicitud en el sistema.</p>
                          <br>
                          <p>Saludos cordiales,<br>Instituto Superior Tecnológico Juan Bautista Aguirre.</p>
                          <hr>
                          <p><strong>Nota:</strong> Este es un mensaje automatizado. Por favor, no responda a esta cuenta de correo.</p>';

        // Enviar correo
        $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar el correo: {$mail->ErrorInfo}");
    }
}
