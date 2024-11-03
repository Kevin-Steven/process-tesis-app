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

// Verificar que la conexión a la base de datos ($conn) esté disponible
if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Verificar si se ha enviado el formulario correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar el ID del tema a eliminar
    $tema_id = intval($_POST['tema_id']);

    // Obtener el ID del usuario, el nombre del tema y su posible pareja a partir del tema
    $sql_tema = "SELECT usuario_id, pareja_id, tema FROM tema WHERE id = ?";
    $stmt_tema = $conn->prepare($sql_tema);
    $stmt_tema->bind_param("i", $tema_id);
    $stmt_tema->execute();
    $result_tema = $stmt_tema->get_result();
    $tema_data = $result_tema->fetch_assoc();
    $stmt_tema->close();

    // Verificar que se haya encontrado el tema
    if (!$tema_data) {
        die("Tema no encontrado.");
    }

    $usuario_id = $tema_data['usuario_id'];
    $pareja_id = $tema_data['pareja_id'];
    $nombre_tema = $tema_data['tema']; // Obtener el nombre del tema

    // Actualizar el campo `estado_registro` del tema a 1 para realizar el borrado lógico
    $sql_update_tema = "UPDATE tema SET estado_registro = 1 WHERE id = ?";
    $stmt_update_tema = $conn->prepare($sql_update_tema);
    $stmt_update_tema->bind_param("i", $tema_id);

    // Ejecutar la actualización del tema
    if ($stmt_update_tema->execute()) {
        // Actualizar el campo `pareja_id` en la tabla `tema` para ambos usuarios
        $sql_update_tema_pareja = "UPDATE tema SET pareja_id = CASE
                WHEN usuario_id = ? THEN 0
                WHEN usuario_id = ? THEN -1
            END
            WHERE usuario_id IN (?, ?)";
        $stmt_update_tema_pareja = $conn->prepare($sql_update_tema_pareja);
        $stmt_update_tema_pareja->bind_param("iiii", $usuario_id, $pareja_id, $usuario_id, $pareja_id);
        $stmt_update_tema_pareja->execute();
        $stmt_update_tema_pareja->close();

        // Actualizar el campo `pareja_tesis` para el usuario que elimina el tema y su pareja
        $sql_update_usuario = "UPDATE usuarios SET pareja_tesis = 0 WHERE id = ?";
        $stmt_update_usuario = $conn->prepare($sql_update_usuario);
        $stmt_update_usuario->bind_param("i", $usuario_id);
        $stmt_update_usuario->execute();

        // Si hay una pareja, actualizar el campo `pareja_tesis` de la pareja a 0
        if ($pareja_id) {
            $sql_update_pareja = "UPDATE usuarios SET pareja_tesis = 0 WHERE id = ?";
            $stmt_update_pareja = $conn->prepare($sql_update_pareja);
            $stmt_update_pareja->bind_param("i", $pareja_id);
            $stmt_update_pareja->execute();
        }

        // Obtener información del usuario (postulante) que elimina el tema
        $sql_usuario = "SELECT nombres, apellidos, email FROM usuarios WHERE id = ?";
        $stmt_usuario = $conn->prepare($sql_usuario);
        $stmt_usuario->bind_param("i", $usuario_id);
        $stmt_usuario->execute();
        $result_usuario = $stmt_usuario->get_result();
        $usuario_info = $result_usuario->fetch_assoc();

        // Enviar correo al gestor
        // enviarCorreoGestor($usuario_info['nombres'], $usuario_info['apellidos'], $nombre_tema);

        // Si hay un compañero, enviarle la notificación también
        if ($pareja_id) {
            $sql_pareja_info = "SELECT nombres, apellidos, email FROM usuarios WHERE id = ?";
            $stmt_pareja_info = $conn->prepare($sql_pareja_info);
            $stmt_pareja_info->bind_param("i", $pareja_id);
            $stmt_pareja_info->execute();
            $result_pareja_info = $stmt_pareja_info->get_result();
            $pareja_info = $result_pareja_info->fetch_assoc();

            // enviarCorreoParejaEliminada($pareja_info['nombres'], $pareja_info['apellidos'], $pareja_info['email'], $usuario_info['nombres'], $usuario_info['apellidos'], $nombre_tema);
        }

        // Redireccionar a la página de "enviar-tema.php" con mensaje de éxito
        header("Location: enviar-tema.php?mensaje=Tema eliminado correctamente");
        exit();
    } else {
        // Mostrar mensaje de error en caso de fallo
        echo "Error al eliminar el tema: " . $stmt_update_tema->error;
    }

    $stmt_update_tema->close();
} else {
    // Redirigir si se accede a este archivo sin enviar el formulario
    header("Location: enviar-tema.php");
    exit();
}

$conn->close();

// Función para enviar correo al gestor
function enviarCorreoGestor($nombre_postulante, $apellido_postulante, $nombre_tema)
{
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

        $mail->isHTML(true);

        // Asunto y cuerpo del correo
        $mail->Subject = 'Notificación de Eliminación de Tema';
        $mail->Body    = "<h2>Eliminación de tema</h2>
                          <p>Le informamos que el estudiante <strong>$nombre_postulante $apellido_postulante</strong> ha eliminado su tema titulado <strong>$nombre_tema</strong> del proceso de titulación.</p>
                          <br>
                          <p>Saludos cordiales,<br>Instituto Superior Tecnológico Juan Bautista Aguirre.</p>
                          <hr>
                          <p><strong>Nota:</strong> Este es un mensaje automatizado. Por favor, no responda a esta cuenta de correo.</p>";

        // Enviar correo
        $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar el correo al gestor: {$mail->ErrorInfo}");
    }
}

// Función para enviar correo a la pareja eliminada
function enviarCorreoParejaEliminada($nombre_pareja, $apellido_pareja, $correo_pareja, $nombre_postulante, $apellido_postulante, $nombre_tema)
{
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP de Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kbarzolav.istjba@gmail.com';
        $mail->Password   = 'hdha jtez rkhr omik';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Configuración del charset
        $mail->CharSet    = 'UTF-8';

        // Configuración del remitente y destinatario
        $mail->setFrom('kbarzolav.istjba@gmail.com', 'Instituto Superior Tecnológico');
        $mail->addAddress($correo_pareja);

        $mail->isHTML(true);

        // Asunto y cuerpo del correo
        $mail->Subject = 'Notificación de Eliminación de Tema y Pareja';
        $mail->Body    = "<p>Estimado(a) <strong>$nombre_pareja $apellido_pareja</strong>,</p>
                          <p>Le informamos que su compañero de titulación <strong>$nombre_postulante $apellido_postulante</strong> ha eliminado su tema titulado <strong>$nombre_tema</strong> del proceso de titulación, por lo que la relación de pareja ha sido eliminada.</p>
                          <br>
                          <p>Saludos cordiales,<br>Instituto Superior Tecnológico Juan Bautista Aguirre.</p>
                          <hr>
                          <p><strong>Nota:</strong> Este es un mensaje automatizado. Por favor, no responda a esta cuenta de correo.</p>";

        // Enviar correo
        $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar el correo a la pareja: {$mail->ErrorInfo}");
    }
}
