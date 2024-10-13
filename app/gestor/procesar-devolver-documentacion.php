<?php
session_start();
require '../config/config.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_postulante'])) {
    $postulante_id = $_POST['id_postulante'];
    $mensaje = mysqli_real_escape_string($conn, $_POST['mensaje']);
    $enviar_postulante = isset($_POST['enviar_postulante']) ? true : false;

    // Obtener datos del postulante y su inscripción
    $sql = "SELECT u.nombres, u.apellidos, u.email, d.id as documento_id 
            FROM usuarios u 
            LEFT JOIN documentos_postulante d ON u.id = d.usuario_id 
            WHERE u.id = ? ORDER BY d.fecha_subida DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postulante_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $postulante = $result->fetch_assoc();

    // Enviar correo si se ha marcado la opción
    if ($enviar_postulante) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username   = 'tds.titulacion.istjba@gmail.com';
            $mail->Password   = 'ecic zfih ifqj utgv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('tds.titulacion.istjba@gmail.com', 'Instituto Superior Tecnológico');
            $mail->addAddress($postulante['email']);
            $mail->isHTML(true);
            $mail->Subject = 'Devolución de Documentación';
            $mail->Body = "<h2>Estimado {$postulante['nombres']} {$postulante['apellidos']},</h2>
                           <p>Le informamos que su documentación ha sido devuelta.</p>
                           <p><strong>Motivo de la devolución:</strong> {$mensaje}</p>
                           <p>Por favor, realice las correcciones necesarias y vuelva a enviarla.</p>
                           <br>
                           <p>Saludos cordiales,<br>Instituto Tecnológico Juan Bautista Aguirre.</p>
                           <hr>
                           <p><strong>Nota:</strong> Este es un mensaje automatizado. Por favor, no responda a esta cuenta de correo.</p>";
            $mail->send();

            // Actualizar el estado del postulante
            $sql_update_postulante = "UPDATE documentos_postulante 
                                      SET estado_inscripcion = 'Rechazado', estado_registro = 1 
                                      WHERE id = ?";
            $stmt_update_postulante = $conn->prepare($sql_update_postulante);
            $stmt_update_postulante->bind_param("i", $postulante['documento_id']);
            $stmt_update_postulante->execute();

            // Redirigir después de éxito
            header("Location: ver-inscripciones.php?status=success");
        } catch (Exception $e) {
            // Redirigir con estado de error
            $error = urlencode("Error al enviar el correo: {$mail->ErrorInfo}");
            header("Location: ver-inscripciones.php?status=error&mensaje=$error");
        }
    }
}

$conn->close();
