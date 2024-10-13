<?php
session_start();
require '../config/config.php';

// Verificar si el formulario fue enviado correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tema_id']) && isset($_POST['tutor_id'])) {
    $tema_id = intval($_POST['tema_id']);
    $nuevo_tutor_id = intval($_POST['tutor_id']);

    // Verificar que el tutor seleccionado sea válido
    if ($nuevo_tutor_id > 0) {
        // Consulta para obtener la información del tema y verificar si tiene una pareja
        $sql = "SELECT usuario_id, pareja_id FROM tema WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $tema_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $tema = $result->fetch_assoc();
            $usuario_id = $tema['usuario_id'];
            $pareja_id = $tema['pareja_id'];

            // Actualizar el tutor para el tema del postulante
            $sql_update = "UPDATE tema SET tutor_id = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ii", $nuevo_tutor_id, $tema_id);
            $stmt_update->execute();

            // Si el postulante tiene una pareja, también actualizar el tutor para el tema de la pareja
            if (!empty($pareja_id)) {
                $sql_update_pareja = "UPDATE tema SET tutor_id = ? WHERE usuario_id = ?";
                $stmt_update_pareja = $conn->prepare($sql_update_pareja);
                $stmt_update_pareja->bind_param("ii", $nuevo_tutor_id, $pareja_id);
                $stmt_update_pareja->execute();
            }

            header("Location: ver-temas-aprobados.php?status=success");
            exit();
        } else {
            header("Location: ver-temas-aprobados.php?status=not_found");
            exit();
        }
    } else {
        header("Location: editar-tutor-ap.php?id=$tema_id&status=invalid_tutor");
        exit();
    }
} else {
    header("Location: ver-temas-aprobados.php?status=form_error");
    exit();
}
