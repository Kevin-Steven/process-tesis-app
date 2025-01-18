<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: informe.php?status=form_error");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$uploadDir = '../uploads/informes-tutor/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Validación del archivo subido
if (isset($_FILES['archivo_informe']) && $_FILES['archivo_informe']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['archivo_informe']['tmp_name'];
    $fileName = $_FILES['archivo_informe']['name']; // Nombre original del archivo
    $fileSize = $_FILES['archivo_informe']['size'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Extensiones permitidas
    $allowedFileExtensions = ['zip', 'doc', 'docx', 'pdf'];
    $maxFileSize = 10 * 1024 * 1024; // 10MB en bytes

    // Validación del archivo
    $error = '';
    if (!in_array($fileExtension, $allowedFileExtensions)) {
        $error = 'invalid_extension';
    } elseif ($fileSize > $maxFileSize) {
        $error = 'too_large';
    } else {
        $destPath = $uploadDir . $fileName; // Ruta final con el nombre original del archivo

        // Mover el archivo al directorio de destino
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Insertar el registro en la tabla `informes_tutores`
            $sql_insert = "INSERT INTO informes_tutores (tutor_id, archivo) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("is", $usuario_id, $fileName);

            if ($stmt_insert->execute()) {
                header("Location: informe.php?status=success");
                exit();
            } else {
                $error = 'db_error';
            }
        } else {
            $error = 'upload_error';
        }
    }

    // Si ocurre algún error, redirigir con el estado correspondiente
    if ($error) {
        header("Location: informe.php?status=$error");
        exit();
    }
} else {
    header("Location: informe.php?status=no_file");
    exit();
}
?>
