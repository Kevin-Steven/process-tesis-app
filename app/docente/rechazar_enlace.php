<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tema_id']) && isset($_POST['motivo_rechazo'])) {
        $tema_id = $_POST['tema_id'];
        $motivo_rechazo = $_POST['motivo_rechazo'];

        // Actualizar el estado y el motivo de rechazo en la base de datos
        $sql = "UPDATE tema SET estado_enlace = 'Rechazado', motivo_rechazo_enlace = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $motivo_rechazo, $tema_id);

        if ($stmt->execute()) {
            // Redirigir con éxito
            header("Location: revisar-plagio.php?status=rejected");
            exit();
        } else {
            // Error al actualizar
            header("Location: revisar-plagio.php?status=error");
            exit();
        }
    } else {
        echo "No se proporcionaron todos los datos.";
    }
} else {
    header("Location:revisar-plagio.php");
}
?>
