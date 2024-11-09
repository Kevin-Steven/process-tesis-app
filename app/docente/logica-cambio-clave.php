<?php
session_start();
require '../config/config.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Verificar que los campos del formulario estén completos
if (isset($_POST['actualPassword'], $_POST['newPassword'], $_POST['confirmPassword'])) {

    $actualPassword = $_POST['actualPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Verificar si la nueva contraseña coincide con la confirmación
    if ($newPassword !== $confirmPassword) {
        $_SESSION['mensaje'] = "La nueva contraseña y la confirmación no coinciden.";
        $_SESSION['tipo_mensaje'] = 'warning'; // Mensaje de advertencia
        header("Location: cambio-clave.php");
        exit();
    }

    // Obtener la contraseña actual del usuario desde la base de datos
    $sql = "SELECT password FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    // Verificar si la contraseña actual ingresada es correcta
    if (!password_verify($actualPassword, $hashedPassword)) {
        $_SESSION['mensaje'] = "La contraseña actual es incorrecta.";
        $_SESSION['tipo_mensaje'] = 'warning'; // Mensaje de advertencia
        header("Location: cambio-clave.php");
        exit();
    }

    // Encriptar la nueva contraseña
    $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Actualizar la contraseña en la base de datos
    $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newHashedPassword, $usuario_id);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Su contraseña ha sido actualizada con éxito.";
        $_SESSION['tipo_mensaje'] = 'success';
    } else {
        $_SESSION['mensaje'] = "Hubo un error al actualizar su contraseña. Por favor, intente de nuevo.";
        $_SESSION['tipo_mensaje'] = 'warning';
    }

    $stmt->close();
    $conn->close();

    // Redirigir de vuelta al formulario con el mensaje
    header("Location: cambio-clave.php");
    exit();
} else {
    // Si no se completaron los campos, redirigir al formulario
    $_SESSION['mensaje'] = "Por favor, complete todos los campos.";
    $_SESSION['tipo_mensaje'] = 'warning'; 
    header("Location: cambio-clave.php");
    exit();
}
