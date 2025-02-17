<?php
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion == 'agregar') {
        // Obtener los valores del formulario
        $nombre = mb_strtoupper(mysqli_real_escape_string($conn, $_POST['nombre']), 'UTF-8');
        $cedula = mysqli_real_escape_string($conn, $_POST['cedula']);

        // Verificar si la cédula ya existe
        $query_check = "SELECT * FROM tutores WHERE cedula = '$cedula'";
        $result_check = $conn->query($query_check);

        if ($result_check->num_rows > 0) {
            header("Location: agg-del-tutores.php?status=repeat-ci");
        } else {
            $sql = "INSERT INTO tutores (nombres, cedula, estado) VALUES ('$nombre', '$cedula', '0')";
            $conn->query($sql);
            header("Location: agg-del-tutores.php?status=success");
        }
    } elseif ($accion == 'editar') {
        // Obtener los valores del formulario
        $id = $_POST['id'];
        $nombre = mb_strtoupper(mysqli_real_escape_string($conn, $_POST['nombre']), 'UTF-8');
        $cedula = mysqli_real_escape_string($conn, $_POST['cedula']);

        // Verificar si la cédula ya existe para otro tutor (evitar duplicados)
        $query_check = "SELECT * FROM tutores WHERE cedula = '$cedula' AND id != '$id'";
        $result_check = $conn->query($query_check);

        if ($result_check->num_rows > 0) {
            header("Location: agg-del-tutores.php?status=repeat-ci");
        } else {
            // Actualizar los datos del tutor
            $sql = "UPDATE tutores SET nombres = '$nombre', cedula = '$cedula' WHERE id = '$id'";
            if ($conn->query($sql)) {
                header("Location: agg-del-tutores.php?status=updated");
            } else {
                header("Location: agg-del-tutores.php?status=error");
            }
        }
    } elseif ($accion == 'eliminar') {
        // Borrado lógico: Cambiar estado a 1 en lugar de eliminar el registro
        $id = $_POST['id'];
        $sql = "UPDATE tutores SET estado = '1' WHERE id = '$id'";
        if ($conn->query($sql)) {
            header("Location: agg-del-tutores.php?status=deleted");
        } else {
            header("Location: agg-del-tutores.php?status=error");
        }
    }
}
