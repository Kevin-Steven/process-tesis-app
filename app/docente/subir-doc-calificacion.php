<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Configuración para la validación de archivos
    $allowedFileExtensions = ['zip', 'doc', 'docx', 'pdf'];
    $maxFileSize = 20 * 1024 * 1024; // 20 MB en bytes
    $ruta_base = '../uploads/calificaciones-tesis/';

    // Obtener datos del formulario
    $tesis_id = $_POST['tesis_id'];
    $accion = $_POST['accion'];
    $nota = isset($_POST['nota-tesis']) ? floatval($_POST['nota-tesis']) : null;

    // Verificar conexión a la base de datos
    if (!$conn) {
        header("Location: rubrica-calificacion.php?estado=error_conexion");
        exit();
    }

    // Validar la nota solo si se ha enviado
    if ($nota !== null && ($nota < 0 || $nota > 10)) {
        header("Location: rubrica-calificacion.php?estado=error_nota");
        exit();
    }

    if ($accion === 'subir' || $accion === 'editar') {
        $archivo_subido = false;
        $ruta_destino = null;

        // Verificar si se ha subido un archivo
        if (isset($_FILES['archivo_tesis']) && $_FILES['archivo_tesis']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['archivo_tesis'];
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);

            // Validar tipo de archivo
            if (!in_array(strtolower($extension), $allowedFileExtensions)) {
                header("Location: rubrica-calificacion.php?estado=error_tipo_archivo");
                exit();
            }

            // Validar tamaño del archivo
            if ($archivo['size'] > $maxFileSize) {
                header("Location: rubrica-calificacion.php?estado=error_tamano_archivo");
                exit();
            }

            // Generar nombre único para el archivo y moverlo a la carpeta
            $nombre_unico = uniqid('doc_calificacion_', true) . '.' . $extension;
            $ruta_destino = $ruta_base . $nombre_unico;

            if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                $archivo_subido = true;
            } else {
                header("Location: rubrica-calificacion.php?estado=error_subida");
                exit();
            }
        }

        // Construir la consulta SQL dinámicamente
        if ($archivo_subido && $nota !== null) {
            // Si se sube archivo y se actualiza nota
            $sql = "UPDATE tema SET rubrica_calificacion = ?, nota_revisor_tesis = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdi", $ruta_destino, $nota, $tesis_id);
        } elseif ($archivo_subido) {
            // Solo se sube archivo
            $sql = "UPDATE tema SET rubrica_calificacion = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $ruta_destino, $tesis_id);
        } elseif ($nota !== null) {
            // Solo se actualiza la nota
            $sql = "UPDATE tema SET nota_revisor_tesis = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("di", $nota, $tesis_id);
        } else {
            // Si no se ha enviado ni archivo ni nota
            header("Location: rubrica-calificacion.php?estado=sin_cambios");
            exit();
        }

        // Ejecutar la consulta
        if ($stmt->execute()) {
            header("Location: rubrica-calificacion.php?estado=exito");
        } else {
            header("Location: rubrica-calificacion.php?estado=error_bd");
        }
        $stmt->close();

    } elseif ($accion === 'eliminar') {
        // Eliminar la referencia al archivo y la nota en la base de datos
        $sql = "UPDATE tema SET rubrica_calificacion = NULL, nota_revisor_tesis = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $tesis_id);

        if ($stmt->execute()) {
            header("Location: rubrica-calificacion.php?estado=exito_eliminar");
        } else {
            header("Location: rubrica-calificacion.php?estado=error_bd");
        }
        $stmt->close();
    }

    // Cerrar conexión
    $conn->close();
}
?>
