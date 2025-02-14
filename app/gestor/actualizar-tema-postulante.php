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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tema_id = intval($_POST['tema_id']);
    $nuevo_postulante_id = intval($_POST['postulante_id']);
    $nuevo_pareja_id = intval($_POST['pareja_id']);

    $tema = trim($_POST['tema']);
    $objetivo_general = trim($_POST['objetivo_general']);
    $objetivo_especifico_uno = trim($_POST['objetivo_especifico_uno']);
    $objetivo_especifico_dos = trim($_POST['objetivo_especifico_dos']);
    $objetivo_especifico_tres = trim($_POST['objetivo_especifico_tres']);

    // ✅ 1️⃣ Obtener los valores actuales del tema antes de modificar
    $sql_tema_actual = "SELECT usuario_id, pareja_id FROM tema WHERE id = ?";
    $stmt_tema_actual = $conn->prepare($sql_tema_actual);
    $stmt_tema_actual->bind_param("i", $tema_id);
    $stmt_tema_actual->execute();
    $stmt_tema_actual->bind_result($postulante_actual, $pareja_actual);
    $stmt_tema_actual->fetch();
    $stmt_tema_actual->close();

    if ($nuevo_postulante_id !== $postulante_actual) {
        $sql_update_tema_postulante = "UPDATE tema SET usuario_id = ? WHERE id = ?";
        $stmt_update_tema_postulante = $conn->prepare($sql_update_tema_postulante);
        $stmt_update_tema_postulante->bind_param("ii", $nuevo_postulante_id, $tema_id);
        $stmt_update_tema_postulante->execute();
    
        $sql_update_anterior_postulante = "UPDATE usuarios SET pareja_tesis = 0 WHERE id = ?";
        $stmt_update_anterior_postulante = $conn->prepare($sql_update_anterior_postulante);
        $stmt_update_anterior_postulante->bind_param("i", $postulante_actual);
        $stmt_update_anterior_postulante->execute();
    
        // ✅ Si el nuevo postulante era la pareja anterior, eliminar la relación de pareja
        if ($nuevo_postulante_id === $pareja_actual) {
            $nuevo_pareja_id = -1;
        }
    }
    

    // ✅ 3️⃣ Verificar si el postulante ha cambiado su pareja
    if ($nuevo_pareja_id !== $pareja_actual) {
        if ($nuevo_pareja_id == -1) { // Si selecciona "Sin Pareja"
            $sql_update_postulante = "UPDATE usuarios SET pareja_tesis = -1 WHERE id = ?";
            $stmt_update_postulante = $conn->prepare($sql_update_postulante);
            $stmt_update_postulante->bind_param("i", $nuevo_postulante_id);
            $stmt_update_postulante->execute();

            if ($pareja_actual && $pareja_actual != -1) {
                $sql_update_pareja_anterior = "UPDATE usuarios SET pareja_tesis = 0 WHERE id = ?";
                $stmt_update_pareja_anterior = $conn->prepare($sql_update_pareja_anterior);
                $stmt_update_pareja_anterior->bind_param("i", $pareja_actual);
                $stmt_update_pareja_anterior->execute();
            }

            $sql_update_tema = "UPDATE tema SET pareja_id = -1 WHERE id = ?";
            $stmt_update_tema = $conn->prepare($sql_update_tema);
            $stmt_update_tema->bind_param("i", $tema_id);
            $stmt_update_tema->execute();
        } else { // Si el postulante selecciona una nueva pareja

            // ✅ 1️⃣ Obtener la pareja actual de `nuevo_pareja_id`
            $sql_pareja_anterior = "SELECT pareja_tesis FROM usuarios WHERE id = ?";
            $stmt_pareja_anterior = $conn->prepare($sql_pareja_anterior);
            $stmt_pareja_anterior->bind_param("i", $nuevo_pareja_id);
            $stmt_pareja_anterior->execute();
            $stmt_pareja_anterior->bind_result($pareja_anterior_nuevo_pareja);
            $stmt_pareja_anterior->fetch();
            $stmt_pareja_anterior->close();

            // ✅ 2️⃣ Si `nuevo_pareja_id` tenía pareja antes, actualizar su pareja a `pareja_tesis = 0`
            if ($pareja_anterior_nuevo_pareja && $pareja_anterior_nuevo_pareja != -1) {
                $sql_update_antigua_pareja = "UPDATE usuarios SET pareja_tesis = 0 WHERE id = ?";
                $stmt_update_antigua_pareja = $conn->prepare($sql_update_antigua_pareja);
                $stmt_update_antigua_pareja->bind_param("i", $pareja_anterior_nuevo_pareja);
                $stmt_update_antigua_pareja->execute();
            }

            // ✅ 3️⃣ Actualizar `pareja_tesis` del postulante con la nueva pareja
            $sql_update_postulante = "UPDATE usuarios SET pareja_tesis = ? WHERE id = ?";
            $stmt_update_postulante = $conn->prepare($sql_update_postulante);
            $stmt_update_postulante->bind_param("ii", $nuevo_pareja_id, $nuevo_postulante_id);
            $stmt_update_postulante->execute();

            // ✅ 4️⃣ Actualizar `pareja_tesis` del nuevo compañero con el postulante
            $sql_update_nueva_pareja = "UPDATE usuarios SET pareja_tesis = ? WHERE id = ?";
            $stmt_update_nueva_pareja = $conn->prepare($sql_update_nueva_pareja);
            $stmt_update_nueva_pareja->bind_param("ii", $nuevo_postulante_id, $nuevo_pareja_id);
            $stmt_update_nueva_pareja->execute();

            // ✅ 5️⃣ Si el postulante tenía una pareja anterior, ahora la pareja anterior tiene `pareja_tesis = 0`
            if ($pareja_actual && $pareja_actual != -1) {
                $sql_update_pareja_anterior = "UPDATE usuarios SET pareja_tesis = 0 WHERE id = ?";
                $stmt_update_pareja_anterior = $conn->prepare($sql_update_pareja_anterior);
                $stmt_update_pareja_anterior->bind_param("i", $pareja_actual);
                $stmt_update_pareja_anterior->execute();
            }

            // ✅ 6️⃣ Actualizar `tema.pareja_id` con el nuevo compañero
            $sql_update_tema = "UPDATE tema SET pareja_id = ? WHERE id = ?";
            $stmt_update_tema = $conn->prepare($sql_update_tema);
            $stmt_update_tema->bind_param("ii", $nuevo_pareja_id, $tema_id);
            $stmt_update_tema->execute();
        }
    }

    // ✅ 4️⃣ Actualizar los datos del tema en la tabla `tema`
    $sql_update_tema_info = "UPDATE tema SET 
                                tema = ?, 
                                objetivo_general = ?, 
                                objetivo_especifico_uno = ?, 
                                objetivo_especifico_dos = ?, 
                                objetivo_especifico_tres = ? 
                            WHERE id = ?";
    $stmt_update_tema_info = $conn->prepare($sql_update_tema_info);
    $stmt_update_tema_info->bind_param(
        "sssssi",
        $tema,
        $objetivo_general,
        $objetivo_especifico_uno,
        $objetivo_especifico_dos,
        $objetivo_especifico_tres,
        $tema_id
    );

    if ($stmt_update_tema_info->execute()) {
        header("Location: editar-tema.php?id=$tema_id&status=success");
        exit();
    } else {
        header("Location: editar-tema.php?id=$tema_id&status=form_error");
        exit();
    }
} else {
    header("Location: editar-tema.php");
    exit();
}

$conn->close();
