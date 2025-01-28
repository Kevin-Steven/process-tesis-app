<?php
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tutor_id = $_POST['tutor_id'];

    if (!empty($tutor_id) && is_numeric($tutor_id)) {
        $tutor_id = mysqli_real_escape_string($conn, $tutor_id);

        $sql = "UPDATE tutores SET estado = 1 WHERE id = $tutor_id";

        if ($conn->query($sql) === TRUE) {
            header("Location: agg-del-tutores.php?status=tutor_deleted");
            exit(); 
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        header("Location: agg-del-tutores.php?status=empty-tutor");
        exit();
    }
}
?>
