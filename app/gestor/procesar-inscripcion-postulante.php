<?php
session_start();
require '../config/config.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar si se ha enviado un formulario con ID del postulante y acción válida
if (isset($_POST['id_postulante']) && (isset($_POST['aceptar']) || isset($_POST['denegar']))) {
    $postulante_id = $_POST['id_postulante'];
    $accion = isset($_POST['aceptar']) ? 'aceptar' : 'denegar';

    // Consultar datos del postulante
    $sql = "SELECT u.nombres, u.apellidos, u.email FROM usuarios u WHERE u.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postulante_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $postulante = $result->fetch_assoc();
        $nuevo_estado = $accion === 'aceptar' ? 'Aprobado' : 'Rechazado';

        // Obtener el último registro de inscripción del postulante
        $sql_last_inscripcion = "SELECT id FROM documentos_postulante WHERE usuario_id = ? ORDER BY fecha_subida DESC LIMIT 1";
        $stmt_last_inscripcion = $conn->prepare($sql_last_inscripcion);
        $stmt_last_inscripcion->bind_param("i", $postulante_id);
        $stmt_last_inscripcion->execute();
        $result_last_inscripcion = $stmt_last_inscripcion->get_result();

        if ($result_last_inscripcion->num_rows > 0) {
            $ultima_inscripcion_id = $result_last_inscripcion->fetch_assoc()['id'];

            // Actualizar estado de inscripción según la acción
            $estado_registro = $accion === 'denegar' ? 1 : 0;
            $sql_update = "UPDATE documentos_postulante SET estado_inscripcion = ?, estado_registro = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sii", $nuevo_estado, $estado_registro, $ultima_inscripcion_id);
            $stmt_update->execute();

            // // Llamar a la función para enviar el correo
            // if (enviarCorreoInscripcion($postulante, $accion)) {
            //     header("Location: ver-inscripciones.php?status=success");
            //     exit();
            // } else {
            //     header("Location: ver-inscripciones.php?status=error");
            //     exit();
            // }
            header("Location: ver-inscripciones.php?status=success");
            exit();
        } else {
            echo "No se encontró el registro de inscripción.";
            exit();
        }
    } else {
        echo "Postulante no encontrado.";
        exit();
    }
} else {
    echo "No se ha proporcionado un ID de postulante válido o acción.";
    exit();
}

$conn->close();

/**
 * Función para enviar correos de inscripción (aceptación o rechazo)
 */
function enviarCorreoInscripcion($postulante, $accion)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tds.titulacion.istjba@gmail.com';
        $mail->Password   = 'ecic zfih ifqj utgv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Configurar destinatario
        $mail->setFrom('tds.titulacion.istjba@gmail.com', 'Instituto Superior Tecnológico');
        $mail->addAddress($postulante['email']);
        $mail->isHTML(true);

        // Preparar el correo según la acción
        if ($accion === 'aceptar') {
            $mail->Subject = 'Confirmación de Aceptación';
            $mail->Body = '
            <h2>Estimado ' . $postulante['nombres'] . ' ' . $postulante['apellidos'] . ',</h2>
            <p>Nos complace informarte que tu inscripción ha sido <strong>aprobada</strong>. 
            En breve te contactaremos para los siguientes pasos del proceso de titulación.</p>
            <br>
            <p>Saludos cordiales,<br>Instituto Tecnológico Juan Bautista Aguirre.</p>
            <hr>
            <p><strong>Nota:</strong> Este es un mensaje automatizado. Por favor, no responda a esta cuenta de correo.</p>';
        } else {
            $mail->Subject = 'Notificación de Rechazo';
            $mail->Body = '
            <h2>Estimado ' . $postulante['nombres'] . ' ' . $postulante['apellidos'] . ',</h2>
            <p>Lamentamos informarte que tu inscripción ha sido <strong>rechazada</strong>. 
            Si tienes alguna pregunta, no dudes en contactarnos para más detalles.</p>
            <br>
            <p>Saludos cordiales,<br>Instituto Tecnológico Juan Bautista Aguirre.</p>
            <hr>
            <p><strong>Nota:</strong> Este es un mensaje automatizado. Por favor, no responda a esta cuenta de correo.</p>';
        }

        // Enviar el correo
        return $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar el correo: {$mail->ErrorInfo}");
        return false;
    }
}
