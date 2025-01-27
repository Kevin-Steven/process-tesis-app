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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['correcciones']) && $_FILES['correcciones']['error'] === UPLOAD_ERR_OK) {
        // Validar el archivo
        $fileTmpPath = $_FILES['correcciones']['tmp_name'];
        $fileName = $_FILES['correcciones']['name'];
        $fileSize = $_FILES['correcciones']['size'];
        $fileType = $_FILES['correcciones']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Permitir solo archivos ZIP
        $allowedfileExtensions = array('zip');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Verificar el tamaño del archivo (máximo 20 MB)
            if ($fileSize < 20 * 1024 * 1024) {
                // Directorio donde se guardarán las correcciones
                $uploadFileDir = '../uploads/correcciones/';

                // Extraer el primer nombre y primer apellido del usuario
                $primer_nombre = explode(' ', $usuario_nombre)[0];
                $primer_apellido = explode(' ', $usuario_apellido)[0];

                // Formatear el nombre del archivo con la nomenclatura requerida
                $newFileName = "Correcciones_tesis_" . strtoupper($primer_apellido) . "_" . strtoupper($primer_nombre) . '.' . $fileExtension; 

                $dest_path = $uploadFileDir . $newFileName;

                // Mover el archivo al directorio de destino
                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Actualizar la base de datos
                    // Primero, obtener el nombre del archivo actual (si existe) para eliminarlo
                    $sql_select = "SELECT correcciones_tesis FROM tema WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
                    $stmt_select = $conn->prepare($sql_select);
                    $stmt_select->bind_param("i", $usuario_id);
                    $stmt_select->execute();
                    $result_select = $stmt_select->get_result();
                    $tema = $result_select->fetch_assoc();
                    $stmt_select->close();

                    // Si hay un archivo existente, eliminarlo
                    if ($tema && !empty($tema['correcciones_tesis'])) {
                        $existingFile = $uploadFileDir . $tema['correcciones_tesis'];
                        if (file_exists($existingFile)) {
                            unlink($existingFile);
                        }
                    }

                    // Actualizar el campo 'correcciones_tesis' en la base de datos
                    $sql_update = "UPDATE tema SET correcciones_tesis = ? WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("si", $newFileName, $usuario_id);
                    if ($stmt_update->execute()) {
                        $stmt_update->close();
                        header("Location: enviar-documento-tesis.php?status=success");
                        exit();
                    } else {
                        $stmt_update->close();
                        header("Location: enviar-documento-tesis.php?status=db_error");
                        exit();
                    }
                } else {
                    header("Location: enviar-documento-tesis.php?status=upload_error");
                    exit();
                }
            } else {
                header("Location: enviar-documento-tesis.php?status=too_large");
                exit();
            }
        } else {
            header("Location: enviar-documento-tesis.php?status=invalid_extension");
            exit();
        }
    } else {
        header("Location: enviar-documento-tesis.php?status=no_file");
        exit();
    }
} else {
    header("Location: enviar-documento-tesis.php?status=form_error");
    exit();
}
?>
