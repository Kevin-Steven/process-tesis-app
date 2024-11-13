<?php
session_start();
require '../config/config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener los nombres y apellidos del usuario y su pareja (si existe)
$sql_usuario = "SELECT u.nombres, u.apellidos, t.pareja_id 
                FROM usuarios u 
                INNER JOIN tema t ON u.id = t.usuario_id 
                WHERE u.id = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
$usuario = $result_usuario->fetch_assoc();
$stmt_usuario->close();

$primer_nombre = explode(' ', $usuario['nombres'])[0];
$primer_apellido = explode(' ', $usuario['apellidos'])[0];
$nombre_pareja = '';

if (!empty($usuario['pareja_id'])) {
    // Obtener los datos de la pareja si existe
    $sql_pareja = "SELECT nombres, apellidos FROM usuarios WHERE id = ?";
    $stmt_pareja = $conn->prepare($sql_pareja);
    $stmt_pareja->bind_param("i", $usuario['pareja_id']);
    $stmt_pareja->execute();
    $result_pareja = $stmt_pareja->get_result();
    $pareja = $result_pareja->fetch_assoc();
    $stmt_pareja->close();

    if ($pareja) {
        $nombre_pareja = "_" . explode(' ', $pareja['apellidos'])[0] . "_" . explode(' ', $pareja['nombres'])[0];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar que el archivo fue cargado
    if (isset($_FILES['correcciones']) && $_FILES['correcciones']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['correcciones'];
        $fileTmpPath = $file['tmp_name'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validar la extensión del archivo (debe ser ZIP)
        if ($fileExtension !== 'zip') {
            header("Location: enviar-correcciones.php?status=invalid_extension");
            exit();
        }

        // Validar el tamaño del archivo (10 MB máximo)
        if ($fileSize > 10 * 1024 * 1024) {
            header("Location: enviar-correcciones.php?status=too_large");
            exit();
        }

        // Crear la nomenclatura del archivo con el primer apellido y primer nombre (y pareja si existe)
        $nuevoNombreArchivo = "Correcciones_tesis_" . strtoupper($primer_apellido) . "_" . strtoupper($primer_nombre) . $nombre_pareja . ".zip";

        $uploadDir = "../uploads/correcciones/";

        // Crear la carpeta si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadPath = $uploadDir . $nuevoNombreArchivo;

        // Mover el archivo cargado a la carpeta de destino
        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
            // Actualizar la base de datos con la ruta del archivo de correcciones
            $sql_update = "UPDATE tema SET correcciones_tesis = ? WHERE usuario_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $nuevoNombreArchivo, $usuario_id);

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
