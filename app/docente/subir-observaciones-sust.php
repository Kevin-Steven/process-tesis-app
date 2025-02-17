<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$cedula_docente  = $_POST['cedula_docente'] ?? null;
$tesis_id        = $_POST['tesis_id'] ?? null;
$accion          = $_POST['accion'] ?? '';
$pareja_id       = $_POST['pareja_id'] ?? 0;  // Para saber si hay un segundo estudiante

// Notas (pueden llegar vacías si no se llenó el campo)
$nota_sustentar     = isset($_POST['nota_sustentar'])   ? floatval($_POST['nota_sustentar'])   : null;
$nota_sustentar_2   = isset($_POST['nota_sustentar_2']) ? floatval($_POST['nota_sustentar_2']) : null;

// Verificación mínima
if (!$cedula_docente || !$tesis_id) {
    die("Error: Datos insuficientes para procesar la solicitud.");
}

// Obtenemos los ids de jurados en la tabla tema
$sql_tema = "SELECT id_jurado_uno, id_jurado_dos, id_jurado_tres FROM tema WHERE id = ?";
$stmt_tema = $conn->prepare($sql_tema);
$stmt_tema->bind_param('i', $tesis_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();

if (!($row_tema = $result_tema->fetch_assoc())) {
    die("Error: Tema no encontrado.");
}

$id_jurado_uno = $row_tema['id_jurado_uno'];
$id_jurado_dos = $row_tema['id_jurado_dos'];
$id_jurado_tres= $row_tema['id_jurado_tres'];

// Obtenemos las cédulas de los tutores/jurados
$sql_tutores = "SELECT id, cedula FROM tutores WHERE id IN (?, ?, ?)";
$stmt_tutores = $conn->prepare($sql_tutores);
$stmt_tutores->bind_param('iii', $id_jurado_uno, $id_jurado_dos, $id_jurado_tres);
$stmt_tutores->execute();
$result_tutores = $stmt_tutores->get_result();

$campo_nota    = null; // Para la nota del primer estudiante
$campo_nota_2  = null; // Para la nota del segundo estudiante (si hay pareja)

while ($row_tutor = $result_tutores->fetch_assoc()) {
    if ($row_tutor['cedula'] == $cedula_docente) {
        // Depende del jurado que coincide con la cédula
        if ($row_tutor['id'] == $id_jurado_uno) {
            $campo_nota   = 'j1_nota_sustentar';
            $campo_nota_2 = 'j1_nota_sustentar_2';
        } elseif ($row_tutor['id'] == $id_jurado_dos) {
            $campo_nota   = 'j2_nota_sustentar';
            $campo_nota_2 = 'j2_nota_sustentar_2';
        } elseif ($row_tutor['id'] == $id_jurado_tres) {
            $campo_nota   = 'j3_nota_sustentar';
            $campo_nota_2 = 'j3_nota_sustentar_2';
        }
        break;
    }
}

if (!$campo_nota) {
    die("Error: Este docente no está asignado como jurado para este tema.");
}

// Validación de rangos (sólo si la nota no es nula)
if ($nota_sustentar !== null && ($nota_sustentar < 0 || $nota_sustentar > 10)) {
    die("Error: La nota del primer estudiante debe estar entre 0.00 y 10.00.");
}

if ($nota_sustentar_2 !== null && ($nota_sustentar_2 < 0 || $nota_sustentar_2 > 10)) {
    die("Error: La nota del segundo estudiante debe estar entre 0.00 y 10.00.");
}

// ------------------------------------------------------------------
// ACCIONES
// ------------------------------------------------------------------
if ($accion === 'subir' || $accion === 'editar') {

    // Si NO hay pareja, no actualizamos la segunda columna
    if ($pareja_id > 0) {
        // Hay pareja
        $sql_update = "UPDATE tema 
                       SET $campo_nota = ?, $campo_nota_2 = ?
                       WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('ddi', $nota_sustentar, $nota_sustentar_2, $tesis_id);
    } else {
        // No hay pareja
        $sql_update = "UPDATE tema 
                       SET $campo_nota = ?
                       WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('di', $nota_sustentar, $tesis_id);
    }

    if ($stmt_update->execute()) {
        header("Location: revisar-sustentacion.php?status=success");
        exit();
    } else {
        header("Location: revisar-sustentacion.php?status=error");
        exit();
    }

} elseif ($accion === 'eliminar') {

    // Al eliminar, ponemos NULL la columna correspondiente.
    // Si existe pareja, ponemos NULL a ambas columnas (para dejar en blanco a ambos).
    if ($pareja_id > 0) {
        $sql_delete = "UPDATE tema SET $campo_nota = NULL, $campo_nota_2 = NULL WHERE id = ?";
    } else {
        $sql_delete = "UPDATE tema SET $campo_nota = NULL WHERE id = ?";
    }
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
