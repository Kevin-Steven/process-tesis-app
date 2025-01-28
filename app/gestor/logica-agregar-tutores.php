<?php
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $cedula = mysqli_real_escape_string($conn, $_POST['cedula']);

    if (empty($nombre) || empty($cedula)) {
        header("Location: agg-del-tutores.php?status=empty-fields");
        exit(); 
    }

    $nombre = ucwords(strtolower($nombre));

    $query_check = "SELECT * FROM tutores WHERE cedula = '$cedula'";
    $result_check = $conn->query($query_check);

    if ($result_check->num_rows > 0) {
        header("Location: agg-del-tutores.php?status=repeat-ci");
        exit();
    } else {
        $sql = "INSERT INTO tutores (nombres, cedula) VALUES ('$nombre', '$cedula')";

        if ($conn->query($sql) === TRUE) {
            header("Location: agg-del-tutores.php?status=success");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>
