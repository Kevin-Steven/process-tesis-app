<?php
require '../config/config.php';

if (isset($_POST['tema_id'])) {
    
    $tema_id = intval($_POST['tema_id']);
    
    // Si los jurados no están seleccionados, se asigna NULL
    $jurado_1 = !empty($_POST['jurado_1']) ? intval($_POST['jurado_1']) : NULL;
    $jurado_2 = !empty($_POST['jurado_2']) ? intval($_POST['jurado_2']) : NULL;
    $jurado_3 = !empty($_POST['jurado_3']) ? intval($_POST['jurado_3']) : NULL;
    
    // Si sede o aula no están seleccionadas, se asigna NULL
    $sede = !empty($_POST['sede']) ? trim($_POST['sede']) : NULL;
    $aula = !empty($_POST['aula']) ? trim($_POST['aula']) : NULL;
    
    // Si la fecha o la hora no están seleccionadas, se asigna NULL
    $fecha_sustentar = !empty($_POST['fecha']) ? $_POST['fecha'] : NULL;
    $hora_sustentar = !empty($_POST['hora']) ? $_POST['hora'] : NULL;

    // Validar que los jurados sean distintos si están asignados
    if (($jurado_1 && $jurado_2 && $jurado_1 === $jurado_2) || 
        ($jurado_1 && $jurado_3 && $jurado_1 === $jurado_3) || 
        ($jurado_2 && $jurado_3 && $jurado_2 === $jurado_3)) {
        header("Location: asignar-jurado.php?id=$tema_id&status=error_duplicado");
        exit();
    }

    // Preparar la consulta UPDATE permitiendo valores NULL
    $sql_update = "UPDATE tema 
                   SET id_jurado_uno = ?, 
                       id_jurado_dos = ?, 
                       id_jurado_tres = ?, 
                       sede = ?, 
                       aula = ?, 
                       fecha_sustentar = ?, 
                       hora_sustentar = ? 
                   WHERE id = ?";

    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("iiissssi", $jurado_1, $jurado_2, $jurado_3, $sede, $aula, $fecha_sustentar, $hora_sustentar, $tema_id);

    if ($stmt->execute()) {
        header("Location: asignar-jurado.php?id=$tema_id&status=success");
    } else {
        header("Location: asignar-jurado.php?id=$tema_id&status=error");
    }

    $stmt->close();
} else {
    header("Location: asignar-jurado.php?status=form_error");
}
?>
