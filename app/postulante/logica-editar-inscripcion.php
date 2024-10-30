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

// Variable para detectar cambios
$mensaje_cambios = "";

// Manejo de archivo
if (!empty($_FILES['documentoCarpeta']['name'])) {
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($_FILES['documentoCarpeta']['name']);
    
    // Verificar tipo de archivo y moverlo al directorio
    if (move_uploaded_file($_FILES['documentoCarpeta']['tmp_name'], $target_file)) {
        $documento_carpeta = basename($_FILES['documentoCarpeta']['name']);
        $mensaje_cambios .= "El postulante ha actualizado el documento.<br>";
    } else {
        die("Error al subir el archivo.");
    }
} else {
    // Mantener el archivo actual si no se sube uno nuevo
    $sql = "SELECT documento_carpeta FROM documentos_postulante WHERE usuario_id = ? ORDER BY fecha_subida DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $documento_carpeta = $result->fetch_assoc()['documento_carpeta'];
    $stmt->close();
}

// Actualizar la inscripción en la base de datos
$sql_update = "UPDATE documentos_postulante SET documento_carpeta = ? WHERE usuario_id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("si", $documento_carpeta, $usuario_id);

if ($stmt_update->execute()) {
    // Enviar el correo si hubo cambios
    if (!empty($mensaje_cambios)) {
       // enviarCorreo($mensaje_cambios, $_SESSION['usuario_nombre'], $_SESSION['usuario_apellido']);
    }

    header("Location: inscripcion.php?status=success");
} else {
    header("Location: inscripcion.php?status=error");
}

$stmt_update->close();
$conn->close();

// Función para enviar el correo
function enviarCorreo($cambios, $nombre_postulante, $apellido_postulante) {
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
        //$mail->addAddress('kbarzola909@gmail.com'); 
        // $mail->addAddress('jmerinog.istjba@gmail.com');
        
        $mail->isHTML(true);

        // Asunto y cuerpo del correo
        $mail->Subject = 'Cambios en la Inscripción de Tesis';
        $mail->Body    = '<h2>Notificación de Cambios</h2>
                         <p>El postulante <strong>' . $nombre_postulante . ' ' . $apellido_postulante . '</strong> ha realizado los siguientes cambios:</p>
                         <p>' . $cambios . '</p>
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
