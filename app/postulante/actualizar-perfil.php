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
  $orcid = mysqli_real_escape_string($conn, $_POST['orcid']);  

  // Validación de teléfono y WhatsApp (deben tener 10 dígitos)
  if (strlen($telefono) != 10 || strlen($whatsapp) != 10 ||strlen($cedula) != 10 ) {
    header("Location: perfil.php?status=invalid_phone");
    exit();
  }

  // Verificar si el correo o la cédula ya existen en otro usuario
  $sql_check = "SELECT id FROM usuarios WHERE (email = ? OR cedula = ?) AND id != ?";
  $stmt_check = $conn->prepare($sql_check);
  $stmt_check->bind_param("ssi", $email, $cedula, $usuario_id);
  $stmt_check->execute();
  $stmt_check->store_result();

  if ($stmt_check->num_rows > 0) {
    // Si existe un usuario con el mismo correo o cédula, redirigir con status=duplicated
    header("Location: perfil.php?status=duplicated");
    exit();
  }
  $stmt_check->close();

  // Obtener los datos actuales del usuario
  $sql_select = "SELECT nombres, apellidos, email, cedula, telefono, whatsapp, orcid, foto_perfil FROM usuarios WHERE id = ?";
  $stmt_select = $conn->prepare($sql_select);
  $stmt_select->bind_param("i", $usuario_id);
  $stmt_select->execute();
  $result = $stmt_select->get_result();
  $usuario_actual = $result->fetch_assoc();
  $stmt_select->close();

  // Verificar si se ha subido una nueva imagen de perfil
  if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
    $target_dir = "../photos/";
    $foto_perfil = $target_dir . basename($_FILES["foto_perfil"]["name"]);
    move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $foto_perfil);
    $_SESSION['usuario_foto'] = $foto_perfil;
  } else {
    $foto_perfil = $_POST['foto_actual'];
  }

  // Verificar si ha habido cambios en los datos
  if (
    $nombres === $usuario_actual['nombres'] &&
    $apellidos === $usuario_actual['apellidos'] &&
    $email === $usuario_actual['email'] &&
    $telefono === $usuario_actual['telefono'] &&
    $whatsapp === $usuario_actual['whatsapp'] &&
    $cedula === $usuario_actual['cedula'] &&
    $foto_perfil === $usuario_actual['foto_perfil'] &&
    $orcid === $usuario_actual['orcid']
  ) {
    header("Location: perfil.php?status=no_changes");
    exit();
  }

  // Actualizar los datos del usuario en la base de datos
  $sql_update = "UPDATE usuarios SET nombres=?, apellidos=?, email=?, cedula=?, telefono=?, whatsapp=?, orcid=?, foto_perfil=? WHERE id=?";
  $stmt_update = $conn->prepare($sql_update);
  $stmt_update->bind_param("ssssssssi", $nombres, $apellidos, $email, $cedula, $telefono, $whatsapp, $orcid, $foto_perfil, $usuario_id);

  if ($stmt_update->execute()) {
    $_SESSION['usuario_nombre'] = $nombres;
    $_SESSION['usuario_apellido'] = $apellidos;
    header("Location: perfil.php?status=success");
    exit();
  } else {
    header("Location: perfil.php?status=error");
    exit();
  }

  $stmt_update->close();
}

$conn->close();
