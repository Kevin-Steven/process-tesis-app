<?php
session_start();
require '../config/config.php';

// Verificar si se ha enviado el formulario
if (isset($_POST['enviar_enlace'])) {
    $postulante = $_POST['postulante']; 
    $enlacePlagio = $_POST['link-plagio'];
    
    $sql = "UPDATE tema SET enlace_plagio = ?, estado_enlace = 'Pendiente' WHERE usuario_id = ?"; 

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $enlacePlagio, $postulante); 

        if ($stmt->execute()) {
            //echo "Enlace actualizado correctamente.";
            header("Location: estado-plagio.php?status=success");
        } else {
            //echo "Error al actualizar el enlace: " . $stmt->error;
            header("Location: estado-plagio.php?status=db_error");

        }
    } else {
        header("Location: estado-plagio.php?status=db_error");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: estado-plagio.php?status=form_error");
}
?>
