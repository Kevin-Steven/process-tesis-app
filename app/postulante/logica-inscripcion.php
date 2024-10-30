<?php
session_start();
require '../config/config.php'; 
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_postulante = $_SESSION['usuario_nombre'];
$apellido_postulante = $_SESSION['usuario_apellido'];

// Directorio donde se guardará el archivo ZIP/RAR
$target_dir = "../uploads/";

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Función para verificar tipo de archivo (ZIP o RAR) y tamaño máximo
function isValidFile($file, $allowed_mime_types, $max_size) {
    if (!is_uploaded_file($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }

    $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_mime_type = mime_content_type($file['tmp_name']);
    $file_size = $file['size'];

    $allowed_extensions = ['zip', 'rar'];

    // Verificar tipo, tamaño y extensión del archivo
    if (in_array($file_type, $allowed_extensions) && 
        in_array($file_mime_type, $allowed_mime_types) && 
        $file_size <= $max_size) {
        return true;
    } else {
        return false;
    }
}

// Función para subir archivo y manejar errores
function uploadFile($file, $target_dir, $input_name, $allowed_mime_types, $max_size, $nuevo_nombre) {
    if (!isset($file[$input_name]) || !isValidFile($file[$input_name], $allowed_mime_types, $max_size)) {
        return null;
    }

    $file_extension = strtolower(pathinfo($file[$input_name]['name'], PATHINFO_EXTENSION));
    $target_file = $target_dir . $nuevo_nombre . '.' . $file_extension;
    
    if (move_uploaded_file($file[$input_name]['tmp_name'], $target_file)) {
        return $target_file;
    } else {
        die("Hubo un error al subir el archivo.");
    }
}

$allowed_mime_types = ['application/zip', 'application/x-rar-compressed', 'application/octet-stream'];
$max_size = 20 * 1024 * 1024; // 20 MB

// Nomenclatura del archivo: Anteproyecto_(1er apellido)_(1er nombre)
$nombre_archivo = 'Documentos_' . explode(' ', $apellido_postulante)[0] . '_' . explode(' ', $nombre_postulante)[0];

// Subir el archivo con la nueva nomenclatura
$documento_carpeta = uploadFile($_FILES, $target_dir, 'documentoCarpeta', $allowed_mime_types, $max_size, $nombre_archivo);

if (!$documento_carpeta) {
    header("Location: inscripcion.php?status=error");
}

// Guardar los datos en la base de datos
$estado_inscripcion = 'En proceso de validación';

// Guardar los datos en la tabla `documentos_postulante`
$sql = "INSERT INTO documentos_postulante (
    usuario_id, documento_carpeta, estado_inscripcion, estado_registro
) VALUES (?, ?, ?, 0)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $usuario_id, $documento_carpeta, $estado_inscripcion);

if ($stmt->execute()) {
    // Enviar correo después de completar la inscripción
    // enviarCorreoInscripcion($nombre_postulante, $apellido_postulante); 

    echo "Inscripción completada con éxito.";
    header("Location: inscripcion.php?status=success");
    exit();
} else {
    header("Location: inscripcion.php?status=invalid_request");
}   

$stmt->close();
$conn->close();

// Función para enviar correo
function enviarCorreoInscripcion($postulante_nombre, $postulante_apellidos, $admin_email = 'tds.titulacion.istjba@gmail.com') {
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
        $mail->addAddress($admin_email); 
        
        $mail->isHTML(true);

        // Asunto y cuerpo del correo
        $mail->Subject = 'Nueva Inscripción Realizada';
        $mail->Body    = '<h1>Notificación de Nueva Inscripción</h1>
                          <p>El postulante <strong>' . $postulante_nombre . ' ' . $postulante_apellidos . '</strong> ha realizado una nueva inscripción al proceso de titulación.</p>
                          <p>Revisa los detalles en el sistema.</p>
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
