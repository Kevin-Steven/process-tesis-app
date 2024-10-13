<?php
session_start();
require '../config/config.php';

// Verificar si se ha enviado el formulario con el ID del tema y el ID del revisor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tema_id']) && isset($_POST['revisor_id'])) {
    $tema_id = $_POST['tema_id'];
    $revisor_id = $_POST['revisor_id'];

    // Consultar el tema para obtener el ID del postulante y su pareja (si tiene)
    $sql = "SELECT usuario_id, pareja_id FROM tema WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tema_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $usuario_id = $row['usuario_id'];
        $pareja_id = $row['pareja_id'];

        // Asignar el revisor al postulante
        $sql_update = "UPDATE tema SET revisor_tesis_id = ? WHERE usuario_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $revisor_id, $usuario_id);
        $stmt_update->execute();

        // Si hay una pareja, asignar el revisor también
        if ($pareja_id) {
            $stmt_update->bind_param("ii", $revisor_id, $pareja_id);
            $stmt_update->execute();
        }

        // Redireccionar con un mensaje de éxito
        header("Location: asignar-revisores.php?status=success");
        exit();
    } else {
        echo "No se encontró el tema especificado.";
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
} else {
    header("Location: asignar-revisores.php?status=error");
    exit();
}
