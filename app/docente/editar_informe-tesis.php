<?php
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: informe-revisor-tesis.php?status=form_error");
    exit();
}

if (!isset($_POST['informe_id'])) {
    header("Location: informe-revisor-tesis.php?status=no_id");
    exit();
}

$informe_id = $_POST['informe_id'];

// Validación del archivo subido
if (isset($_FILES['archivo_informe']) && $_FILES['archivo_informe']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['archivo_informe']['tmp_name'];
    $fileName = $_FILES['archivo_informe']['name'];
    $fileSize = $_FILES['archivo_informe']['size'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $uploadDir = '../uploads/informes-tesis/';

    // Validar extensiones y tamaño
    $allowedFileExtensions = ['zip', 'doc', 'docx', 'pdf'];
    $maxFileSize = 20 * 1024 * 1024; // 20MB

    if (!in_array($fileExtension, $allowedFileExtensions) || $fileSize > $maxFileSize) {
        header("Location: informe-revisor-tesis.php?status=invalid_file");
        exit();
    }

    // Eliminar el archivo existente
    $sql_select = "SELECT informe_tesis FROM informes_tesis WHERE id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $informe_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows > 0) {
        $informe = $result->fetch_assoc();
        unlink($uploadDir . $informe['archivo']);
    }

    // Subir el nuevo archivo
    if (move_uploaded_file($fileTmpPath, $uploadDir . $fileName)) {
        $sql_update = "UPDATE informes_tesis SET informe_tesis = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $fileName, $informe_id);
        if ($stmt_update->execute()) {
            header("Location: informe-revisor-tesis.php?status=updated");
            exit();
        }
    }
}

header("Location: informe-revisor-tesis.php?status=error_updated");
exit();
?>
