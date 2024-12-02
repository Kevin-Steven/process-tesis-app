<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['correcciones_tesis'])) {
    $archivo_a_eliminar = $_POST['correcciones_tesis'];
    $uploadDir = '../uploads/correcciones/';

    // Eliminar el archivo si existe
    if (!empty($archivo_a_eliminar) && file_exists($uploadDir . $archivo_a_eliminar)) {
        unlink($uploadDir . $archivo_a_eliminar);
    }

    // Actualizar la base de datos
    $sql = "UPDATE tema SET correcciones_tesis = NULL WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->close();

    header("Location: enviar-documento-tesis.php?status=deleted");
    exit();
} else {
    header("Location: enviar-documento-tesis.php?status=form_error");
    exit();
}
?>
