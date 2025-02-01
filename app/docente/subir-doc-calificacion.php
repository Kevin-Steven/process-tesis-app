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

    // Verificar conexión a la base de datos
    if (!$conn) {
        header("Location: rubrica-calificacion.php?estado=error_conexion");
        exit();
    }

    if ($accion === 'subir' || $accion === 'editar') {
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

            if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                header("Location: rubrica-calificacion.php?estado=error_subida");
                exit();
            }

            // Actualizar la base de datos con la nueva ruta del archivo
            $sql = "UPDATE tema SET rubrica_calificacion = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $ruta_destino, $tesis_id);

            if ($stmt->execute()) {
                header("Location: rubrica-calificacion.php?estado=exito");
            } else {
                header("Location: rubrica-calificacion.php?estado=error_bd");
            }
            $stmt->close();
        } else {
            header("Location: rubrica-calificacion.php?estado=error_archivo");
        }
    } elseif ($accion === 'eliminar') {
        // Actualizar la base de datos para eliminar la referencia al archivo
        $sql = "UPDATE tema SET rubrica_calificacion = NULL WHERE id = ?";
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
