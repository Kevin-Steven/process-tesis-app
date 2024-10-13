<?php
session_start();
require '../config/config.php';

// Verificar si el usuario ha iniciado sesión y tiene permisos de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'administrador') {
    header("Location: ../../index.php");
    exit();
}

if (isset($_POST['usuario_id']) && isset($_POST['nuevo_rol'])) {
    $usuario_id = $_POST['usuario_id'];
    $nuevo_rol = $_POST['nuevo_rol'];

    // Verificar que el nuevo rol es válido
    $roles_validos = ['postulante', 'administrador', 'gestor', 'docente'];
    if (!in_array($nuevo_rol, $roles_validos)) {
        echo "Rol no válido.";
        exit();
    }

    // Actualizar el rol del usuario en la base de datos
    $sql = "UPDATE usuarios SET rol = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_rol, $usuario_id);

    if ($stmt->execute()) {
        // Redirigir de nuevo a la página de modificar rol con un mensaje de éxito
        header("Location: modificar-rol.php?status=success");
    } else {
        // Redirigir con un mensaje de error si la actualización falló
        header("Location: modificar-rol.php?status=error");
    }

    $stmt->close();
} else {
    // Redirigir con un mensaje de error si los datos no se han enviado correctamente
    header("Location: modificar-rol.php?status=invalid_request");
}

$conn->close();
