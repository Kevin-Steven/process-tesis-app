<?php
session_start();
require '../config/config.php'; 
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tema_id = intval($_POST['tema_id']);
    $motivo_devolucion = htmlspecialchars($_POST['motivo'], ENT_QUOTES, 'UTF-8');
    $enviar_postulante = isset($_POST['enviar_postulante']);
    $enviar_pareja = isset($_POST['enviar_pareja']);

    // Obtener los datos del tema y los usuarios involucrados
    $sql_data = "SELECT t.tema, t.pareja_id, u.email AS postulante_email, u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos,
                p.email AS pareja_email, p.nombres AS pareja_nombres, p.apellidos AS pareja_apellidos
                FROM tema t
                JOIN usuarios u ON t.usuario_id = u.id
                LEFT JOIN usuarios p ON t.pareja_id = p.id
                WHERE t.id = ?";
    $stmt_data = $conn->prepare($sql_data);
    $stmt_data->bind_param("i", $tema_id);
    $stmt_data->execute();
    $tema_data = $stmt_data->get_result()->fetch_assoc();

    // Verificar si se obtuvieron datos correctamente
    if (!$tema_data) {
        error_log("Error: No se encontró el tema con el ID proporcionado.");
        header("Location: ver-temas.php?error=No se encontró el tema");
        exit();
    }

    // Actualizar el estado del tema para el postulante
    $sql_update_tema_postulante = "UPDATE tema SET estado_tema = 'Rechazado', estado_registro = 1 WHERE id = ?";
    $stmt_update_postulante = $conn->prepare($sql_update_tema_postulante);
    $stmt_update_postulante->bind_param("i", $tema_id);

    if (!$stmt_update_postulante->execute()) {
        error_log("Error al actualizar el estado del tema del postulante: " . $conn->error);
    }

    // Si hay una pareja y no está aprobada, actualizar el estado del tema de la pareja solo si el checkbox está seleccionado
    $pareja_id = $tema_data['pareja_id'];
    if ($enviar_pareja && !empty($tema_data['pareja_email']) && $pareja_id > 0) {
        $sql_pareja_tema_aprobado = "SELECT estado_tema FROM tema WHERE usuario_id = ? AND estado_tema = 'Aprobado' LIMIT 1";
        $stmt_pareja_tema_aprobado = $conn->prepare($sql_pareja_tema_aprobado);
        $stmt_pareja_tema_aprobado->bind_param("i", $pareja_id);
        $stmt_pareja_tema_aprobado->execute();
        $result_pareja_tema_aprobado = $stmt_pareja_tema_aprobado->get_result();
        $pareja_tema_aprobado = $result_pareja_tema_aprobado->fetch_assoc();
        $stmt_pareja_tema_aprobado->close();

        // Si el tema de la pareja no está aprobado, proceder con la actualización
        if (!$pareja_tema_aprobado) {
            $sql_update_tema_pareja = "UPDATE tema SET estado_tema = 'Rechazado', estado_registro = 1 WHERE usuario_id = ?";
            $stmt_update_pareja = $conn->prepare($sql_update_tema_pareja);
            $stmt_update_pareja->bind_param("i", $pareja_id);

            if (!$stmt_update_pareja->execute()) {
                error_log("Error al actualizar el estado del tema de la pareja: " . $conn->error);
            }
        }
    }

    // Enviar correo de devolución con motivo si los checkboxes están seleccionados
    if ($enviar_postulante) {
       //  enviarCorreoDevolucion($tema_data['postulante_email'], $tema_data['postulante_nombres'], $tema_data['postulante_apellidos'], $tema_data['tema'], $motivo_devolucion);
    }

    if ($enviar_pareja && !empty($tema_data['pareja_email']) && $pareja_id > 0) {
      //   enviarCorreoDevolucion($tema_data['pareja_email'], $tema_data['pareja_nombres'], $tema_data['pareja_apellidos'], $tema_data['tema'], $motivo_devolucion);
    }

    header("Location: ver-temas.php?mensaje=Tema devuelto con éxito");
    exit();
} else {
    header("Location: ver-temas.php");
    exit();
}

function enviarCorreoDevolucion($email, $nombre, $apellido, $tema, $motivo_devolucion)
{
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP de Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username   = 'tds.titulacion.istjba@gmail.com';
        $mail->Password   = 'ecic zfih ifqj utgv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configuración del charset
        $mail->CharSet = 'UTF-8';

        // Configuración del remitente y destinatario
        $mail->setFrom('tds.titulacion.istjba@gmail.com', 'Instituto Superior Tecnológico');
        $mail->addAddress($email, "$nombre $apellido");

        $mail->isHTML(true);
        $mail->Subject = 'Devolución del Tema de Tesis';
        $mail->Body = "<p>Estimado(a) $nombre $apellido,</p>
                       <p>Le informamos que su tema de tesis \"$tema\" ha sido devuelto.</p>
                       <p><strong>Motivo de la devolución:</strong> $motivo_devolucion</p>
                       <p>Por favor, realice las correcciones necesarias y vuelva a enviarlo.</p>
                       <br>
                       <p>Saludos cordiales,<br>Instituto Superior Tecnológico Juan Bautista Aguirre.</p>
                       <hr>
                       <p><strong>Nota:</strong> Este es un mensaje automatizado. Por favor, no responda a esta cuenta de correo.</p>";

        // Intentar enviar el correo
        if (!$mail->send()) {
            error_log("Error al enviar el correo de devolución: {$mail->ErrorInfo}");
        }
    } catch (Exception $e) {
        error_log("Excepción al enviar el correo de devolución: {$mail->ErrorInfo}");
    }
}
