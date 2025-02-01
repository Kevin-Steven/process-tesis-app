<?php
session_start();
require '../config/config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener los datos del usuario desde la sesión
$usuario_id = $_SESSION['usuario_id'];
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

// Verificar si el usuario tiene una pareja de tesis
$sql_pareja = "SELECT u.nombres, u.apellidos FROM usuarios u 
               INNER JOIN tema t ON u.id = t.pareja_id 
               WHERE t.usuario_id = ?";
$stmt_pareja = $conn->prepare($sql_pareja);
$stmt_pareja->bind_param("i", $usuario_id);
$stmt_pareja->execute();
$result_pareja = $stmt_pareja->get_result();
$pareja = $result_pareja->fetch_assoc();
$stmt_pareja->close();

$nombre_pareja = '';
if ($pareja) {
    $nombre_pareja = "_" . explode(' ', $pareja['apellidos'])[0] . "_" . explode(' ', $pareja['nombres'])[0];
}

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar que el archivo fue cargado
    if (isset($_FILES['documentoTesis']) && $_FILES['documentoTesis']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['documentoTesis'];
        $fileName = $file['name'];
        $fileTmpPath = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];

        // Validar la extensión del archivo
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($fileExtension !== 'zip') {
            header("Location: enviar-documento-tesis.php?status=invalid_extension");
            exit();
        }

        // Validar el tamaño del archivo (20 MB máximo)
        if ($fileSize > 20 * 1024 * 1024) {
            header("Location: enviar-documento-tesis.php?status=too_large");
            exit();
        }

        $nuevoNombreArchivo = "Documento_tesis_" . $primer_apellido . "_" . $primer_nombre . $nombre_pareja . ".zip";
        $uploadDir = "../uploads/documento-tesis/";
        $uploadPath = $uploadDir . $nuevoNombreArchivo;

        // Mover el archivo cargado a la carpeta de destino
        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
            // Actualizar la base de datos con la ruta del archivo
            $rutaDocumento = $nuevoNombreArchivo;
            $sql_update = "UPDATE tema SET documento_tesis = ?, estado_tesis = 'Pendiente' WHERE usuario_id = ? AND estado_registro = 0";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $rutaDocumento, $usuario_id);
            
            if ($stmt_update->execute()) {
                header("Location: enviar-documento-tesis.php?status=success");
            } else {
                header("Location: enviar-documento-tesis.php?status=db_error");
            }
            $stmt_update->close();
        } else {
            header("Location: enviar-documento-tesis.php?status=upload_error");
        }
    } else {
        header("Location: enviar-documento-tesis.php?status=no_file");
    }
} else {
    header("Location: enviar-documento-tesis.php?status=form_error");
}
?>
