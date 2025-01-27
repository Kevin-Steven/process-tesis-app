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

// Actualizar el campo estado_registro a 1 para marcar el registro como "eliminado" en la tabla documentos_postulante (usuario actual)
$sql_update_inscripcion = "UPDATE documentos_postulante SET estado_registro = 1 WHERE usuario_id = ? AND estado_registro = 0";
$stmt_update_inscripcion = $conn->prepare($sql_update_inscripcion);
$stmt_update_inscripcion->bind_param("i", $usuario_id);

// Ejecutar la actualización para el usuario actual
if ($stmt_update_inscripcion->execute()) {
    // Llamar a la función para enviar el correo
    //enviarCorreoEliminacion($_SESSION['usuario_nombre'], $_SESSION['usuario_apellido']);

    // Redirigir con mensaje de éxito
    header("Location: inscripcion.php?status=deleted");
    exit();
} else {
    header("Location: inscripcion.php?status=invalid_request");
}

$stmt_update_inscripcion->close();
$conn->close();

// Función para enviar el correo
function enviarCorreoEliminacion($nombre_postulante, $apellido_postulante) {
    $mail = new PHPMailer(true);

    try {
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

        $mail->addAddress('tds.titulacion.istjba@gmail.com');
        // $mail->addAddress('jmerinog.istjba@gmail.com');

        $mail->isHTML(true);

        $mail->Subject = 'Inscripción Eliminada - Postulante';
        $mail->Body    = '<h2>Notificación de Eliminación de Inscripción</h2>
                          <p>El postulante <strong>' . $nombre_postulante . ' ' . $apellido_postulante . '</strong> ha eliminado su inscripción del sistema.</p>
                          <p>Por favor, revise los registros para confirmar los cambios.</p>
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
