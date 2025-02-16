<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$cedula_docente = $_POST['cedula_docente'];
$tesis_id = $_POST['tesis_id'];
$accion = $_POST['accion'] ?? ''; 
$nota_sustentar = isset($_POST['nota_sustentar']) ? floatval($_POST['nota_sustentar']) : null; // Obtener nota

$sql_tema = "SELECT id_jurado_uno, id_jurado_dos, id_jurado_tres FROM tema WHERE id = ?";
$stmt_tema = $conn->prepare($sql_tema);
$stmt_tema->bind_param('i', $tesis_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();

if ($row_tema = $result_tema->fetch_assoc()) {
    $id_jurado_uno = $row_tema['id_jurado_uno'];
    $id_jurado_dos = $row_tema['id_jurado_dos'];
    $id_jurado_tres = $row_tema['id_jurado_tres'];
} else {
    die("Error: Tema no encontrado.");
}

$sql_tutores = "SELECT id, cedula FROM tutores WHERE id IN (?, ?, ?)";
$stmt_tutores = $conn->prepare($sql_tutores);
$stmt_tutores->bind_param('iii', $id_jurado_uno, $id_jurado_dos, $id_jurado_tres);
$stmt_tutores->execute();
$result_tutores = $stmt_tutores->get_result();

$campo_nota = null;
while ($row_tutor = $result_tutores->fetch_assoc()) {
    if ($row_tutor['cedula'] == $cedula_docente) {
        if ($row_tutor['id'] == $id_jurado_uno) {
            $campo_nota = 'j1_nota_sustentar';
        } elseif ($row_tutor['id'] == $id_jurado_dos) {
            $campo_nota = 'j2_nota_sustentar';
        } elseif ($row_tutor['id'] == $id_jurado_tres) {
            $campo_nota = 'j3_nota_sustentar';
        }
        break; 
    }
}

if (!$campo_nota) {
    die("Error: Este docente no está asignado como jurado para este tema.");
}

if ($nota_sustentar !== null && ($nota_sustentar < 0 || $nota_sustentar > 10)) {
    die("Error: La nota debe estar entre 0.00 y 10.00.");
}

if ($accion == 'subir' || $accion == 'editar') {
    $sql_update = "UPDATE tema SET $campo_nota = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param('di', $nota_sustentar, $tesis_id);

    if ($stmt_update->execute()) {
        header("Location: revisar-sustentacion.php?status=success");
        exit();
    } else {
        header("Location: revisar-sustentacion.php?status=error");
        exit();
    }

} elseif ($accion == 'eliminar') {
    $sql_delete = "UPDATE tema SET $campo_nota = NULL WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param('i', $tesis_id);

    if ($stmt_delete->execute()) {
        header("Location: revisar-sustentacion.php?status=deleted");
        exit();
    } else {
        header("Location: revisar-sustentacion.php?status=error");
        exit();
    }
} else {
    header("Location: revisar-sustentacion.php?status=invalid_action");
    exit();
}
