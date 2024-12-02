<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tema_id'])) {
        $tema_id = $_POST['tema_id'];

        // Actualizar el estado de las correcciones en la base de datos
        $sql = "UPDATE tema SET estado_tesis = 'Aprobado' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $tema_id);

        if ($stmt->execute()) {
            // Redirigir con éxito
            header("Location: revisar-correcciones-tesis.php?status=success");
            exit();
        } else {
            // Error al actualizar
            header("Location: revisar-correcciones-tesis.php?status=error");
            exit();
        }
    } else {
        echo "No se proporcionó el ID del tema.";
    }
} else {
    header("Location: revisar-correcciones-tesis.php");
}
?>
