<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar los datos del formulario
    $tema = $_POST['tema'];
    $objetivo_general = $_POST['objetivo_general'];
    $objetivo_especifico_uno = $_POST['objetivo_especifico_uno'];
    $objetivo_especifico_dos = $_POST['objetivo_especifico_dos'];
    $objetivo_especifico_tres = $_POST['objetivo_especifico_tres'];
    $tutor_id = intval($_POST['tutor_id']);
    $nuevo_pareja_id = intval($_POST['pareja_id']);

    // Obtener el tema_id más reciente para el usuario actual
    $sql_tema_reciente = "SELECT id, pareja_id FROM tema WHERE usuario_id = ? ORDER BY fecha_subida DESC LIMIT 1";
    $stmt_tema_reciente = $conn->prepare($sql_tema_reciente);
    $stmt_tema_reciente->bind_param("i", $usuario_id);
    $stmt_tema_reciente->execute();
    $result_tema_reciente = $stmt_tema_reciente->get_result();

    if ($result_tema_reciente->num_rows > 0) {
        $tema_reciente = $result_tema_reciente->fetch_assoc();
        $tema_id = $tema_reciente['id'];
        $pareja_actual = $tema_reciente['pareja_id'];
    } else {
        die("No se encontró un tema reciente para actualizar.");
    }

    $stmt_tema_reciente->close();

    // Si se ha subido un nuevo archivo de anteproyecto
    $anteproyectoFileName = null;
    if (isset($_FILES['anteproyecto']) && $_FILES['anteproyecto']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_name = basename($_FILES['anteproyecto']['name']); // Usar el nombre original del archivo
        $file_tmp_path = $_FILES['anteproyecto']['tmp_name'];
        $file_name_cmp = explode(".", $file_name);
        $file_extension = strtolower(end($file_name_cmp));

        // Validar tipo de archivo (solo ZIP y RAR permitidos)
        $allowed_extensions = array('zip', 'rar');
        if (in_array($file_extension, $allowed_extensions)) {
            $file_path = $upload_dir . $file_name; // Usar el nombre original en la ruta

            if (move_uploaded_file($file_tmp_path, $file_path)) {
                // Guardar la ruta del archivo para actualizar en la base de datos
                $anteproyectoFileName = $file_name;

                // Actualizar la columna `anteproyecto` con el nombre del archivo original
                $sql_update_anteproyecto = "UPDATE tema SET anteproyecto = ? WHERE id = ?";
                $stmt_update_anteproyecto = $conn->prepare($sql_update_anteproyecto);
                $stmt_update_anteproyecto->bind_param("si", $anteproyectoFileName, $tema_id);
                $stmt_update_anteproyecto->execute();
            } else {
                header("Location: enviar-tema.php?status=error-subir-archivo");
                exit();
            }
        } else {
            header("Location: enviar-tema.php?status=error-solo-zip-rar");
            exit();
        }
    }

    // Verificar si el usuario ha seleccionado una nueva pareja o ha cambiado a "Sin Pareja"
    if ($nuevo_pareja_id !== $pareja_actual) {
        // Si el usuario cambió de pareja
        if ($nuevo_pareja_id == -1) {
            // Si selecciona "Sin Pareja", establecer el campo `pareja_tesis` del usuario a -1
            $sql_update_usuario = "UPDATE usuarios SET pareja_tesis = -1 WHERE id = ?";
            $stmt_update_usuario = $conn->prepare($sql_update_usuario);
            $stmt_update_usuario->bind_param("i", $usuario_id);
            $stmt_update_usuario->execute();

            // Restablecer el campo `pareja_tesis` de la pareja actual a `0`
            if ($pareja_actual && $pareja_actual != -1) {
                $sql_update_pareja_anterior = "UPDATE usuarios SET pareja_tesis = 0 WHERE id = ?";
                $stmt_update_pareja_anterior = $conn->prepare($sql_update_pareja_anterior);
                $stmt_update_pareja_anterior->bind_param("i", $pareja_actual);
                $stmt_update_pareja_anterior->execute();
            }
        } else {
            // Si selecciona un nuevo compañero, actualizar el `pareja_tesis` del usuario y del nuevo compañero
            $sql_update_usuario = "UPDATE usuarios SET pareja_tesis = ? WHERE id = ?";
            $stmt_update_usuario = $conn->prepare($sql_update_usuario);
            $stmt_update_usuario->bind_param("ii", $nuevo_pareja_id, $usuario_id);
            $stmt_update_usuario->execute();

            // Actualizar el campo `pareja_tesis` del nuevo compañero para que sea el ID del usuario actual
            $sql_update_nueva_pareja = "UPDATE usuarios SET pareja_tesis = ? WHERE id = ?";
            $stmt_update_nueva_pareja = $conn->prepare($sql_update_nueva_pareja);
            $stmt_update_nueva_pareja->bind_param("ii", $usuario_id, $nuevo_pareja_id);
            $stmt_update_nueva_pareja->execute();

            // Si el usuario ya tenía una pareja anterior, restablecer el campo `pareja_tesis` de la pareja anterior a `0`
            if ($pareja_actual && $pareja_actual != -1) {
                $sql_update_pareja_anterior = "UPDATE usuarios SET pareja_tesis = 0 WHERE id = ?";
                $stmt_update_pareja_anterior = $conn->prepare($sql_update_pareja_anterior);
                $stmt_update_pareja_anterior->bind_param("i", $pareja_actual);
                $stmt_update_pareja_anterior->execute();
            }
        }
    }

    // Actualizar los datos del tema en la base de datos
    $sql_update = "UPDATE tema SET 
                    tema = ?, 
                    objetivo_general = ?, 
                    objetivo_especifico_uno = ?, 
                    objetivo_especifico_dos = ?, 
                    objetivo_especifico_tres = ?, 
                    tutor_id = ?, 
                    pareja_id = ? 
                   WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssssiii", $tema, $objetivo_general, $objetivo_especifico_uno, $objetivo_especifico_dos, $objetivo_especifico_tres, $tutor_id, $nuevo_pareja_id, $tema_id);

    if ($stmt_update->execute()) {
        header("Location: enviar-tema.php?mensaje=Tema actualizado con éxito");
        exit();
    } else {
        header("Location: enviar-tema.php?mensaje=Error al actualizar el tema");
        //echo "Error al actualizar el tema: " . $stmt_update->error;
    }

    $stmt_update->close();
} else {
    header("Location: enviar-tema.php");
    exit();
}

$conn->close();
