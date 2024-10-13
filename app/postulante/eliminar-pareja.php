<?php
session_start();
require '../config/config.php'; // Conexión a la base de datos
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
$pareja_id = $_POST['pareja_id'] ?? null;

// Verificar si se recibió un ID de pareja válido
if ($pareja_id) {
    // Iniciar una transacción
    $conn->begin_transaction();

    try {
        // Obtener datos del compañero
        $sql_get_pareja = "SELECT nombres, apellidos, email FROM usuarios WHERE id = ?";
        $stmt_get_pareja = $conn->prepare($sql_get_pareja);
        $stmt_get_pareja->bind_param("i", $pareja_id);
        $stmt_get_pareja->execute();
        $result_get_pareja = $stmt_get_pareja->get_result();
        $pareja_data = $result_get_pareja->fetch_assoc();
        $stmt_get_pareja->close();

        // Actualizar el campo `pareja_tesis` del usuario actual a 0
        $sql_update_usuario = "UPDATE usuarios SET pareja_tesis = 0 WHERE id = ?";
        $stmt_update_usuario = $conn->prepare($sql_update_usuario);
        $stmt_update_usuario->bind_param("i", $usuario_id);
        $stmt_update_usuario->execute();
        $stmt_update_usuario->close();

        // Actualizar el campo `pareja_tesis` de la pareja a -1 (solo el registro más reciente)
        $sql_update_pareja = "
            UPDATE usuarios 
            SET pareja_tesis = -1 
            WHERE id = ? 
            ORDER BY fecha_subida DESC 
            LIMIT 1";
        $stmt_update_pareja = $conn->prepare($sql_update_pareja);
        $stmt_update_pareja->bind_param("i", $pareja_id);
        $stmt_update_pareja->execute();
        $stmt_update_pareja->close();

        // Actualizar el campo `pareja_id` en la tabla `documentos_postulante` a -1 para el registro más reciente
        $sql_update_documentos = "
            UPDATE documentos_postulante 
            SET pareja_id = -1 
            WHERE usuario_id = ? 
            ORDER BY fecha_subida DESC 
            LIMIT 1";
        $stmt_update_documentos = $conn->prepare($sql_update_documentos);
        $stmt_update_documentos->bind_param("i", $pareja_id);
        $stmt_update_documentos->execute();
        $stmt_update_documentos->close();

        // Confirmar la transacción
        $conn->commit();

        // Enviar correo al compañero
        // enviarCorreoEliminacionPareja($pareja_data['nombres'], $pareja_data['apellidos'], $pareja_data['email'], $_SESSION['usuario_nombre'], $_SESSION['usuario_apellido']);

        // Redirigir con un mensaje de éxito
        header("Location: inscripcion.php?status=success&message=Pareja eliminada exitosamente.");
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();

        // Redirigir con un mensaje de error
        header("Location: inscripcion.php?status=error&message=Error al eliminar pareja.");
    }
} else {
    // Si no se recibe un ID de pareja válido, redirigir con un mensaje de error
    header("Location: inscripcion.php?status=error&message=No se ha encontrado una pareja para eliminar.");
}

// Función para enviar el correo al compañero
function enviarCorreoEliminacionPareja($nombre_pareja, $apellido_pareja, $correo_pareja, $nombre_usuario, $apellido_usuario) {
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
        $mail->addAddress($correo_pareja, $nombre_pareja . ' ' . $apellido_pareja);

        $mail->isHTML(true);

        // Asunto y cuerpo del correo
        $mail->Subject = 'Notificación de Eliminación de Pareja - proceso de titulación';
        $mail->Body    = '<h2>Estimado(a) ' . $nombre_pareja . ' ' . $apellido_pareja . ',</h2>
                          <p>Le informamos que su pareja del proceso de titulación, <strong>' . $nombre_usuario . ' ' . $apellido_usuario . '</strong>, ha decidido eliminar su solicitud en el sistema.</p>
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
?>
