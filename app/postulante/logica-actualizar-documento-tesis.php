<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_POST['id_postulante'];
$accion = $_POST['accion'] ?? '';

// Obtener los nombres y apellidos para la nomenclatura del archivo
$sql_usuario = "SELECT nombres, apellidos FROM usuarios WHERE id = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
$usuario = $result_usuario->fetch_assoc();
$stmt_usuario->close();

$nombre_usuario = $usuario['nombres'];
$apellido_usuario = $usuario['apellidos'];

$sql_pareja = "SELECT nombres, apellidos FROM usuarios u INNER JOIN tema t ON u.id = t.pareja_id WHERE t.usuario_id = ?";
$stmt_pareja = $conn->prepare($sql_pareja);
$stmt_pareja->bind_param("i", $usuario_id);
$stmt_pareja->execute();
$result_pareja = $stmt_pareja->get_result();
$pareja = $result_pareja->fetch_assoc();
$stmt_pareja->close();

$nombre_pareja = $pareja['nombres'] ?? '';
$apellido_pareja = $pareja['apellidos'] ?? '';

// Generar la nomenclatura del archivo
$nuevoNombreArchivo = "Documento_tesis_upd_" . strtoupper(str_replace(' ', '_', $apellido_usuario)) . "_" . strtoupper(str_replace(' ', '_', $nombre_usuario));
if (!empty($nombre_pareja)) {
    $nuevoNombreArchivo .= "_" . strtoupper(str_replace(' ', '_', $apellido_pareja)) . "_" . strtoupper(str_replace(' ', '_', $nombre_pareja));
}
$nuevoNombreArchivo .= ".zip";

$uploadDir = "../uploads/documento-tesis/";
$uploadPath = $uploadDir . $nuevoNombreArchivo;

if ($accion === 'actualizar' && isset($_FILES['documentoTesis'])) {
    $file = $_FILES['documentoTesis'];
    $fileTmpPath = $file['tmp_name'];
    
    if (move_uploaded_file($fileTmpPath, $uploadPath)) {
        $sql_update = "UPDATE tema SET documento_tesis = ?, estado_tesis = 'Pendiente' WHERE usuario_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $nuevoNombreArchivo, $usuario_id);
        $stmt_update->execute();
        $stmt_update->close();
        header("Location: enviar-documento-tesis.php?status=update");
    } else {
        header("Location: enviar-documento-tesis.php?status=upload_error");
    }
} elseif ($accion === 'eliminar') {
    $sql_delete = "UPDATE tema SET documento_tesis = NULL, estado_tesis = 'Eliminado' WHERE usuario_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $usuario_id);
    $stmt_delete->execute();
    $stmt_delete->close();
    header("Location: enviar-documento-tesis.php?status=deleted");
}
?>
