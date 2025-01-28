<?php
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: informe-revisor-tesis.php?status=form_error");
    exit();
}

if (!isset($_POST['informe_id'])) {
    header("Location: informe-revisor-tesis.php?status=no_id");
    exit();
}

$informe_id = $_POST['informe_id'];
$sql_delete = "UPDATE informes_tesis SET estado = 1 WHERE id = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $informe_id);

if ($stmt_delete->execute()) {
    header("Location: informe-revisor-tesis.php?status=deleted");
    exit();
} else {
    header("Location: informe-revisor-tesis.php?status=dlt_error");
    exit();
}
?>
