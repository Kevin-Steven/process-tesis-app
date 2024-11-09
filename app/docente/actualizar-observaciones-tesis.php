<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['enviar_observaciones'])) {
    header("Location: revisar-tesis.php?status=form_error");
    exit();
}

$tesis_id = $_POST['id_tesis'];
$postulante_id = $_POST['id_postulante'];
$pareja_id = isset($_POST['id_pareja']) ? $_POST['id_pareja'] : null;

// Consultar los detalles del postulante y su pareja (si tiene)
$sql = "SELECT u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos,
               pareja.nombres AS pareja_nombres, pareja.apellidos AS pareja_apellidos
        FROM usuarios u
        LEFT JOIN usuarios pareja ON u.pareja_tesis = pareja.id
        WHERE u.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $postulante_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $datos = $result->fetch_assoc();

    $postulante_nombre = explode(' ', $datos['postulante_nombres'])[0];
    $postulante_apellido = explode(' ', $datos['postulante_apellidos'])[0];
    $pareja_nombre = isset($datos['pareja_nombres']) ? explode(' ', $datos['pareja_nombres'])[0] : null;
    $pareja_apellido = isset($datos['pareja_apellidos']) ? explode(' ', $datos['pareja_apellidos'])[0] : null;
} else {
    header("Location: revisar-tesis.php?status=not_found");
    exit();
}

$uploadDir = '../uploads/observaciones-tesis/';

// Crear el directorio si no existe
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Validación del archivo subido
if (isset($_FILES['archivo_observaciones']) && $_FILES['archivo_observaciones']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['archivo_observaciones']['tmp_name'];
    $fileName = $_FILES['archivo_observaciones']['name'];
    $fileSize = $_FILES['archivo_observaciones']['size'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Extensiones permitidas
    $allowedfileExtensions = ['zip', 'doc', 'docx'];
    $maxFileSize = 20 * 1024 * 1024; // 20MB en bytes

    // Validación del archivo
    $error = '';
    if (!in_array($fileExtension, $allowedfileExtensions)) {
        $error = 'invalid_extension';
    } elseif ($fileSize > $maxFileSize) {
        $error = 'too_large';
    } else {
        // Generar el nombre del archivo
        $newFileName = 'Observaciones_upd_' . $postulante_apellido . '_' . $postulante_nombre;
        if ($pareja_nombre && $pareja_apellido) {
            $newFileName .= '_' . $pareja_apellido . '_' . $pareja_nombre;
        }
        $newFileName .= '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        // Mover el archivo al directorio de destino
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Actualizar la tabla `tema` con el nombre del archivo para el campo `observaciones_tesis`
            $sql_update = "UPDATE tema SET observaciones_tesis = ? WHERE usuario_id = ? AND estado_registro = 0"; // Evitar registros eliminados
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $newFileName, $postulante_id);
            $stmt_update->execute();

            // Si existe pareja, actualizar también para la pareja
            if ($pareja_id) {
                $stmt_update->bind_param("si", $newFileName, $pareja_id);
                $stmt_update->execute();
            }

            header("Location: obs-realizadas-tesis.php?status=success");
            exit();
        } else {
            $error = 'upload_error';
        }
    }

    // Si ocurre algún error, redirigir con el estado correspondiente
    if ($error) {
        header("Location: obs-realizadas-tesis.php?status=$error");
        exit();
    }
} else {
    header("Location: obs-realizadas-tesis.php?status=no_file");
    exit();
}
?>
