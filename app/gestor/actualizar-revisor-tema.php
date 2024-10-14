<?php
session_start();
require '../config/config.php';

// Verificar si el formulario ha sido enviado correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tema_id']) && isset($_POST['revisor_id'])) {
    $tema_id = $_POST['tema_id'];
    $revisor_id = $_POST['revisor_id'];

    // Consultar el tema para obtener el ID del postulante y su pareja (si tiene) solo si estado_registro es 0
    $sql = "SELECT usuario_id, pareja_id FROM tema WHERE id = ? AND estado_registro = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tema_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $usuario_id = $row['usuario_id'];
        $pareja_id = $row['pareja_id'];

        // Iniciar una transacción para asegurar la consistencia de los datos
        $conn->begin_transaction();

        try {
            // Asignar el revisor al postulante
            $sql_update = "UPDATE tema SET revisor_anteproyecto_id = ? WHERE usuario_id = ? AND estado_registro = 0";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ii", $revisor_id, $usuario_id);
            $stmt_update->execute();

            // Si hay una pareja, asignar el revisor también
            if (!is_null($pareja_id) && $pareja_id != 0) {
                $stmt_update->bind_param("ii", $revisor_id, $pareja_id);
                $stmt_update->execute();
            }

            // Confirmar la transacción
            $conn->commit();

            // Redireccionar con un mensaje de éxito
            header("Location: tabla-revisor-anteproyecto.php?status=success");
            exit();
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $conn->rollback();

            // Redireccionar con un mensaje de error
            header("Location: tabla-revisor-anteproyecto.php?status=error");
            exit();
        }
    } else {
        // Si no se encontró el tema o está borrado lógicamente, redireccionar con un mensaje de error
        header("Location: tabla-revisor-anteproyecto.php?status=not_found");
        exit();
    }

    // Cerrar las conexiones
    $stmt->close();
    $conn->close();
} else {
    // Redireccionar si no se envió el formulario correctamente
    header("Location: tabla-revisor-anteproyecto.php?status=invalid_request");
    exit();
}
