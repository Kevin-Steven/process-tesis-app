<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

// 1. Obtener la cédula del docente y el ID del tema desde el formulario
$cedula_docente = $_POST['cedula_docente'];
$tesis_id = $_POST['tesis_id'];
$accion = isset($_POST['accion']) ? $_POST['accion'] : ''; // Acción: 'subir', 'editar', 'eliminar'

// 2. Obtener los IDs de los jurados del tema
$sql_tema = "SELECT id_jurado_uno, id_jurado_dos, id_jurado_tres FROM tema WHERE id = ?";
$stmt_tema = $conn->prepare($sql_tema);
$stmt_tema->bind_param('i', $tesis_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();

if ($row_tema = $result_tema->fetch_assoc()) {
  $id_jurado_uno = $row_tema['id_jurado_uno'];
  $id_jurado_dos = $row_tema['id_jurado_dos'];
  $id_jurado_tres = $row_tema['id_jurado_tres'];
} else {
  die("Error: Tema no encontrado.");
}

// 3. Obtener las cédulas de los jurados desde la tabla tutores
$sql_tutores = "SELECT id, cedula FROM tutores WHERE id IN (?, ?, ?)";
$stmt_tutores = $conn->prepare($sql_tutores);
$stmt_tutores->bind_param('iii', $id_jurado_uno, $id_jurado_dos, $id_jurado_tres);
$stmt_tutores->execute();
$result_tutores = $stmt_tutores->get_result();

$campo_obs = null;
while ($row_tutor = $result_tutores->fetch_assoc()) {
  if ($row_tutor['cedula'] == $cedula_docente) {
    if ($row_tutor['id'] == $id_jurado_uno) {
      $campo_obs = 'obs_jurado_uno';
    } elseif ($row_tutor['id'] == $id_jurado_dos) {
      $campo_obs = 'obs_jurado_dos';
    } elseif ($row_tutor['id'] == $id_jurado_tres) {
      $campo_obs = 'obs_jurado_tres';
    }
    break; // Salimos del bucle si encontramos coincidencia
  }
}

// 4. Verificar si se encontró el jurado
if (!$campo_obs) {
  die("Error: Este docente no está asignado como jurado para este tema.");
}

// 5. Definir las restricciones del archivo
$uploadDir = "../uploads/observaciones-sustentacion/";
$allowedFileExtensions = ['zip', 'doc', 'docx', 'pdf'];
$maxFileSize = 20 * 1024 * 1024; 

// 6. Procesar la acción según el valor de $accion (subir, editar, eliminar)
if ($accion == 'subir') {
  // Procesar la subida de un nuevo archivo
  if (isset($_FILES['archivo_tesis']) && $_FILES['archivo_tesis']['error'] == UPLOAD_ERR_OK) {
    $archivo_tmp = $_FILES['archivo_tesis']['tmp_name'];
    $nombre_archivo = basename($_FILES['archivo_tesis']['name']);
    $ruta_destino = $uploadDir . $nombre_archivo;

    // Obtener la extensión del archivo
    $fileExtension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));

    // Validar el tipo de archivo
    if (!in_array($fileExtension, $allowedFileExtensions)) {
      header("Location: revisar-sustentacion.php?status=invalid_extension");
      exit();
    }

    // Validar el tamaño del archivo
    if ($_FILES['archivo_tesis']['size'] > $maxFileSize) {
      header("Location: revisar-sustentacion.php?status=too_large");
      exit();
    }

    // Mover el archivo al directorio de destino
    if (move_uploaded_file($archivo_tmp, $ruta_destino)) {
      // Actualizar la base de datos con la ruta del archivo
      $sql_update = "UPDATE tema SET $campo_obs = ? WHERE id = ?";
      $stmt_update = $conn->prepare($sql_update);
      $stmt_update->bind_param('si', $ruta_destino, $tesis_id);

      if ($stmt_update->execute()) {
        header("Location:  revisar-sustentacion.php?status=success");
        exit();
      } else {
        header("Location: revisar-sustentacion.php?status=error");
        exit();
      }
    } else {
      header("Location: revisar-sustentacion.php?status=upload_error");
      exit();
    }
  } else {
    header("Location: revisar-sustentacion.php?status=no_file");
    exit();
  }
} elseif ($accion == 'editar') {
  // Editar el archivo existente (reemplazarlo)
  if (isset($_FILES['observaciones-tesis-sust']) && $_FILES['observaciones-tesis-sust']['error'] == UPLOAD_ERR_OK) {
    $archivo_tmp = $_FILES['observaciones-tesis-sust']['tmp_name'];
    $nombre_archivo = basename($_FILES['observaciones-tesis-sust']['name']);
    $ruta_destino = $uploadDir . $nombre_archivo;

    // Obtener la extensión del archivo
    $fileExtension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));

    // Validar el tipo de archivo
    if (!in_array($fileExtension, $allowedFileExtensions)) {
      header("Location: revisar-sustentacion.php?status=invalid_extension");
      exit();
    }

    // Validar el tamaño del archivo
    if ($_FILES['observaciones-tesis-sust']['size'] > $maxFileSize) {
      header("Location: revisar-sustentacion.php?status=too_large");
      exit();
    }

    // Eliminar el archivo anterior si existe
    if (file_exists($ruta_destino)) {
      unlink($ruta_destino);
    }

    // Mover el archivo nuevo al directorio de destino
    if (move_uploaded_file($archivo_tmp, $ruta_destino)) {
      // Actualizar la base de datos con la nueva ruta del archivo
      $sql_update = "UPDATE tema SET $campo_obs = ? WHERE id = ?";
      $stmt_update = $conn->prepare($sql_update);
      $stmt_update->bind_param('si', $ruta_destino, $tesis_id);

      if ($stmt_update->execute()) {
        header("Location: revisar-sustentacion.php?status=success");
        exit();
      } else {
        header("Location: revisar-sustentacion.php?status=error");
        exit();
      }
    } else {
      header("Location: revisar-sustentacion.php?status=upload_error");
      exit();
    }
  } else {
    header("Location: revisar-sustentacion.php?status=no_file");
    exit();
  }
} elseif ($accion == 'eliminar') {
  // Eliminar el archivo existente
  // Primero, obtenemos la ruta del archivo desde la base de datos
  $sql_get_file = "SELECT $campo_obs FROM tema WHERE id = ?";
  $stmt_get_file = $conn->prepare($sql_get_file);
  $stmt_get_file->bind_param('i', $tesis_id);
  $stmt_get_file->execute();
  $result_file = $stmt_get_file->get_result();

  if ($result_file->num_rows > 0) {
    $row_file = $result_file->fetch_assoc();
    $ruta_destino = $row_file[$campo_obs];

    // Eliminar el archivo del servidor
    if (file_exists($ruta_destino)) {
      unlink($ruta_destino);
    }

    // Actualizar la base de datos para eliminar la ruta del archivo
    $sql_update = "UPDATE tema SET $campo_obs = NULL WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param('i', $tesis_id);

    if ($stmt_update->execute()) {
      header("Location:  revisar-sustentacion.php?status=deleted");
      exit();
    } else {
      header("Location: revisar-sustentacion.php?status=error");
      exit();
    }
  } else {
    header("Location: revisar-sustentacion.php?status=file_not_found");
    exit();
  }
} else {
  // Acción no válida
  header("Location: revisar-sustentacion.php?status=invalid_action");
  exit();
}
