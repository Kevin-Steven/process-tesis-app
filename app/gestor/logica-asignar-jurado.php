<?php
require '../config/config.php';

// Verificar si se enviaron los datos del formulario
if (isset($_POST['tema_id'], $_POST['jurado_1'], $_POST['jurado_2'], $_POST['jurado_3'])) {
    $tema_id = intval($_POST['tema_id']);
    $jurado_1 = intval($_POST['jurado_1']);
    $jurado_2 = intval($_POST['jurado_2']);
    $jurado_3 = intval($_POST['jurado_3']);

    // Verificar que los jurados no sean duplicados
    if ($jurado_1 === $jurado_2 || $jurado_1 === $jurado_3 || $jurado_2 === $jurado_3) {
        header("Location: asignar-jurado.php?id=$tema_id&status=error_duplicado");
        exit();
    }

    // Preparar el UPDATE para asignar los jurados
    $sql_update = "UPDATE tema 
                   SET id_jurado_uno = ?, 
                       id_jurado_dos = ?, 
                       id_jurado_tres = ? 
                   WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("iiii", $jurado_1, $jurado_2, $jurado_3, $tema_id);

    if ($stmt->execute()) {
        // Redirigir con estado de éxito
        header("Location: asignar-jurado.php?status=success");
    } else {
        // Redirigir con estado de error
        header("Location: asignar-jurado.php?id=$tema_id&status=error");
    }
    $stmt->close();
} else {
    // Redirigir si no se enviaron los datos necesarios
    header("Location: asignar-jurado.php?status=form_error");
}
?>
