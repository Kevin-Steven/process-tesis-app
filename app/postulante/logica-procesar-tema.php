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

// Obtener datos del postulante
$sql_postulante = "SELECT nombres, apellidos FROM usuarios WHERE id = ?";
$stmt_postulante = $conn->prepare($sql_postulante);
$stmt_postulante->bind_param("i", $usuario_id);
$stmt_postulante->execute();
$result_postulante = $stmt_postulante->get_result();
$postulante = $result_postulante->fetch_assoc();
$postulante_nombre = explode(' ', $postulante['nombres'])[0];
$postulante_apellido = explode(' ', $postulante['apellidos'])[0];
$postulante_nombre_completo = $postulante['nombres'] . ' ' . $postulante['apellidos'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tema = $_POST['tema'];
    $objetivo_general = $_POST['objetivo_general'];
    $objetivo_especifico_uno = $_POST['objetivo_especifico_uno'];
    $objetivo_especifico_dos = $_POST['objetivo_especifico_dos'];
    $objetivo_especifico_tres = $_POST['objetivo_especifico_tres'];
    $tutor_id = intval($_POST['tutor_id']);
    $pareja_id = intval($_POST['pareja_id']);

    if (empty($tema) || empty($objetivo_general) || empty($objetivo_especifico_uno) || empty($objetivo_especifico_dos) || empty($objetivo_especifico_tres) || empty($tutor_id) || empty($pareja_id)) {
        echo "Todos los campos son obligatorios.";
        exit();
    }

    // Configuración para la subida de archivos
    $uploadDir = '../uploads/';
    $anteproyectoFileName = null; // Nombre del archivo a guardar en la BD

    // Verificar si se ha subido un archivo
    if (isset($_FILES['anteproyecto']) && $_FILES['anteproyecto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['anteproyecto']['tmp_name'];
        $fileName = $_FILES['anteproyecto']['name'];
        $fileSize = $_FILES['anteproyecto']['size'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Validar el tamaño del archivo (máximo 20 MB)
        if ($fileSize > 20 * 1024 * 1024) {
            echo "El archivo excede el tamaño máximo permitido de 20 MB.";
            exit();
        }

        // Validar el tipo de archivo (solo permitir ZIP y RAR)
        $allowedfileExtensions = array('zip');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Establecer la nomenclatura "Anteproyecto_(1 apellido)_(1 nombre)"
            $newFileName = "Anteproyecto_" . $postulante_apellido . "_" . $postulante_nombre . "." . $fileExtension;
            $dest_path = $uploadDir . $newFileName;

            // Mover el archivo al directorio de destino
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $anteproyectoFileName = $newFileName; // Guardar el nombre del archivo para la base de datos
            } else {
                header("Location: enviar-tema.php?status=error-subir-archivo");
                exit();
            }
        } else {
            header("Location: enviar-tema.php?status=error-solo-zip");
            exit();
        }
    }

    // Insertar datos en la tabla `tema`
    $sql = "INSERT INTO tema (tema, objetivo_general, objetivo_especifico_uno, objetivo_especifico_dos, objetivo_especifico_tres, tutor_id, usuario_id, pareja_id, anteproyecto, estado_tema, estado_registro, fecha_subida)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', 0, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssiss", $tema, $objetivo_general, $objetivo_especifico_uno, $objetivo_especifico_dos, $objetivo_especifico_tres, $tutor_id, $usuario_id, $pareja_id, $anteproyectoFileName);

    // Ejecutar la consulta e insertar datos
    if ($stmt->execute()) {
        // Actualizar el campo `pareja_tesis` en la tabla `usuarios` para el usuario y su pareja
        $sql_update_pareja = "UPDATE usuarios SET pareja_tesis = CASE
                                  WHEN id = ? THEN ?
                                  WHEN id = ? THEN ?
                              END
                              WHERE id IN (?, ?)";
        $stmt_update = $conn->prepare($sql_update_pareja);
        $stmt_update->bind_param("iiiiii", $usuario_id, $pareja_id, $pareja_id, $usuario_id, $usuario_id, $pareja_id);
        $stmt_update->execute();

        // Enviar correo de notificación al administrador
        // enviarCorreoTema($postulante_nombre_completo);

        // Redireccionar a una página de éxito o mostrar mensaje de éxito
        header("Location: enviar-tema.php?status=success");
        exit();
    } else {
        header("Location: enviar-tema.php?status=error-enviar-tema");
    }

    // Cerrar conexiones
    $stmt->close();
    $conn->close();
} else {
    header("Location: enviar-tema.php");
    exit();
}


// Función para enviar el correo con información del nuevo tema
function enviarCorreoTema($postulante_nombre_completo)
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
        $mail->addAddress('tds.titulacion.istjba@gmail.com');

        $mail->isHTML(true);

        // Asunto y cuerpo del correo
        $mail->Subject = 'Nueva Subida de Tema de Tesis';
        $mail->Body    = '<h2>Nuevo Tema Registrado</h2>
                          <p>El postulante <strong>' . $postulante_nombre_completo . '</strong> ha registrado un nuevo tema de tesis.</p>
                          <p>Revisa el sistema para más detalles.</p>
                          <br>
                          <p>Saludos cordiales,<br>Instituto Superior Tecnológico Juan Bautista Aguirre.</p>
                          <hr>
                          <p><strong>Nota:</strong> Este es un mensaje automatizado. Por favor, no responda a esta cuenta de correo.</p>';

        // Enviar el correo
        $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar el correo: {$mail->ErrorInfo}");
    }
}
