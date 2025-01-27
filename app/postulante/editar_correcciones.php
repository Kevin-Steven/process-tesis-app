<?php
session_start();
require '../config/config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];
$usuario_apellido = $_SESSION['usuario_apellido'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['correcciones_nuevas'])) {
    // Obtener el archivo anterior desde la base de datos
    $sql_estado = "SELECT estado_tesis, correcciones_tesis FROM tema WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
    $stmt_estado = $conn->prepare($sql_estado);
    $stmt_estado->bind_param("i", $usuario_id);
    $stmt_estado->execute();
    $result_estado = $stmt_estado->get_result();
    $tema = $result_estado->fetch_assoc();
    $stmt_estado->close();

    if ($tema) {
        $estado_tesis = $tema['estado_tesis'];
        $archivo_anterior = $tema['correcciones_tesis'];
    } else {
        header("Location: enviar-correcciones.php?status=no_record");
        exit();
    }

    $archivo_nuevo = $_FILES['correcciones_nuevas'];

    // Validar el tamaño del archivo (máximo 20 MB)
    if ($archivo_nuevo['size'] > 20 * 1024 * 1024) {
        header("Location: enviar-correcciones.php?status=too_large");
        exit();
    }

    // Extraer el primer nombre y primer apellido del usuario
    $primer_nombre = explode(' ', $usuario_nombre)[0];
    $primer_apellido = explode(' ', $usuario_apellido)[0];

    // Formatear el nombre del archivo con la nomenclatura requerida
    $fileExtension = strtolower(pathinfo($archivo_nuevo['name'], PATHINFO_EXTENSION));
    $newFileName = "Correcciones_tesis_upd_" . strtoupper($primer_apellido) . "_" . strtoupper($primer_nombre) . '.' . $fileExtension;

    // Directorio donde se guardarán las correcciones
    $uploadDir = "../uploads/correcciones/";
    $dest_path = $uploadDir . $newFileName;

    if ($estado_tesis === 'Rechazado') {
        // Mover el archivo al directorio de destino
        if (move_uploaded_file($archivo_nuevo['tmp_name'], $dest_path)) {
            // Eliminar el archivo anterior si existe
            if (!empty($archivo_anterior) && file_exists($uploadDir . $archivo_anterior)) {
                unlink($uploadDir . $archivo_anterior);
            }

            // Actualizar la base de datos con el nuevo archivo y cambiar estado_tesis a 'Pendiente'
            $sql = "UPDATE tema SET correcciones_tesis = ?, estado_tesis = 'Pendiente', motivo_rechazo_correcciones = NULL WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $newFileName, $usuario_id);
            $stmt->execute();
            $stmt->close();

            header("Location: enviar-documento-tesis.php?status=update");
            exit();
        } else {
            header("Location: enviar-documento-tesis.php?status=upload_error");
            exit();
        }
    } else {
        // Si el estado no es 'Rechazado' o 'Correcciones Rechazadas', podrías decidir permitir o no la subida
        // Aquí redirigimos con un estado de error
        header("Location: enviar-documento-tesis.php?status=invalid_state");
        exit();
    }
} else {
    header("Location: enviar-documento-tesis.php?status=form_error");
    exit();
}
?>
