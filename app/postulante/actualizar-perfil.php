<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Capturar los datos del formulario
  $nombres = mb_strtoupper(mysqli_real_escape_string($conn, $_POST['nombres']), 'UTF-8');  
  $apellidos = mb_strtoupper(mysqli_real_escape_string($conn, $_POST['apellidos']), 'UTF-8');
  $email = mysqli_real_escape_string($conn, $_POST['email']);  
  $cedula = $_POST['cedula'];
  $telefono = $_POST['telefono'];
  $whatsapp = $_POST['whatsapp'];

  // Validación de teléfono y WhatsApp
  if (strlen($telefono) != 10 || strlen($whatsapp) != 10) {
    // Si alguno de los campos no tiene 10 dígitos, redirigir con mensaje de error
    header("Location: perfil.php?status=invalid_phone");
    exit();
  }

  // Obtener los datos actuales del usuario desde la base de datos
  $sql_select = "SELECT nombres, apellidos, email, cedula, telefono, whatsapp, foto_perfil FROM usuarios WHERE id = ?";
  $stmt_select = $conn->prepare($sql_select);
  $stmt_select->bind_param("i", $usuario_id);
  $stmt_select->execute();
  $result = $stmt_select->get_result();
  $usuario_actual = $result->fetch_assoc();
  $stmt_select->close();

  // Verificar si se ha subido una imagen de perfil
  if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
    $target_dir = "../photos/";
    $foto_perfil = $target_dir . basename($_FILES["foto_perfil"]["name"]);
    move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $foto_perfil);

    // Actualizar la variable de sesión con la nueva foto
    $_SESSION['usuario_foto'] = $foto_perfil;
  } else {
    $foto_perfil = $_POST['foto_actual']; // Mantener la foto actual si no se sube una nueva
  }

  // Verificar si ha habido cambios en los datos
  if (
    $nombres === $usuario_actual['nombres'] &&
    $apellidos === $usuario_actual['apellidos'] &&
    $email === $usuario_actual['email'] &&
    $telefono === $usuario_actual['telefono'] &&
    $whatsapp === $usuario_actual['whatsapp'] &&
    $foto_perfil === $usuario_actual['foto_perfil']
  ) {
    // Si no hay cambios, no hacer nada y redirigir con un mensaje de no cambios
    header("Location: perfil.php?status=no_changes");
    exit();
  }

  // Actualizar los datos del usuario en la base de datos
  $sql_update = "UPDATE usuarios SET nombres=?, apellidos=?, email=?, cedula=?, telefono=?, whatsapp=?, foto_perfil=? WHERE id=?";
  $stmt_update = $conn->prepare($sql_update);
  $stmt_update->bind_param("sssssssi", $nombres, $apellidos, $email, $cedula, $telefono, $whatsapp, $foto_perfil, $usuario_id);

  if ($stmt_update->execute()) {
    // Actualizar las variables de sesión si se han modificado los nombres o apellidos
    $_SESSION['usuario_nombre'] = $nombres;
    $_SESSION['usuario_apellido'] = $apellidos;
    
    // Redirigir de nuevo al perfil con un mensaje de éxito
    header("Location: perfil.php?status=success");
    exit();
  } else {
    // En caso de error, redirigir con un mensaje de error
    header("Location: perfil.php?status=error");
    exit();
  }

  $stmt_update->close();
}

$conn->close();
