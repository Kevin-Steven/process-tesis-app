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

    // Actualizar el estado del tema a 'Aprobado'
    $sql_update = "UPDATE tema SET estado_tema = 'Aprobado' WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("i", $tema_id);

    if ($stmt_update->execute()) {
        // Obtener datos del postulante para enviar el correo
        $sql_data = "SELECT t.tema, u.email AS postulante_email, u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos
                     FROM tema t
                     JOIN usuarios u ON t.usuario_id = u.id
                     WHERE t.id = ?";
        $stmt_data = $conn->prepare($sql_data);
        $stmt_data->bind_param("i", $tema_id);
        $stmt_data->execute();
        $tema_data = $stmt_data->get_result()->fetch_assoc();

        // Enviar correo al postulante informando la aprobación del tema
       // enviarCorreoAprobacion($tema_data['postulante_email'], $tema_data['postulante_nombres'], $tema_data['postulante_apellidos'], $tema_data['tema']);

        header("Location: ver-temas.php?mensaje=Tema aprobado con éxito");
        exit();
    } else {
        echo "Error al aprobar el tema.";
    }
} else {
    header("Location: ver-temas.php");
    exit();
}

function enviarCorreoAprobacion($email, $nombre, $apellido, $tema) {
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
        $mail->setFrom('kbarzolav.istjba@gmail.com', 'Instituto Superior Tecnológico');
        $mail->addAddress($email, "$nombre $apellido");

        $mail->isHTML(true);
        $mail->Subject = 'Aprobación del Tema de Tesis';
        $mail->Body = "<p>Estimado(a) $nombre $apellido,</p>
                       <p>Le informamos que su tema de tesis \"$tema\" ha sido <strong>aprobado</strong>.</p>
                       <br>
                       <p>Saludos cordiales,<br>Instituto Superior Tecnológico Juan Bautista Aguirre.</p>
                       <hr>
                       <p><strong>Nota:</strong> Este es un mensaje automatizado. Por favor, no responda a esta cuenta de correo.</p>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar el correo de aprobación: {$mail->ErrorInfo}");
    }
}
?>
