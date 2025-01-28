<?php 
session_start();
require '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula_o_nombre = $_POST['cedula']; // Este campo ahora acepta cedula o nombres
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Consulta para buscar por cedula o nombres
    $sql = "SELECT * FROM usuarios WHERE cedula = ? OR nombres = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $cedula_o_nombre, $cedula_o_nombre); // Usamos el mismo valor para cedula y nombres
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        if (password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombres'];
            $_SESSION['usuario_apellido'] = $usuario['apellidos'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['usuario_foto'] = $usuario['foto_perfil'] ? $usuario['foto_perfil'] : '../../images/user.png';

            // Redirecciona según el rol del usuario
            if ($usuario['rol'] === 'gestor') {
                header("Location: ../gestor/inicio-gestor.php");
            } elseif ($usuario['rol'] === 'administrador') {
                header("Location: ../admin/inicio-administrador.php"); 
            } elseif ($usuario['rol'] === 'docente') {
                header("Location: ../docente/docente-inicio.php"); 
            } else {
                header("Location: ../postulante/inicio-postulante.php"); 
            }
            exit();
        } else {
            $_SESSION['error'] = "Contraseña incorrecta.";
            header("Location: ../../index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No existe una cuenta con esa cédula o usuario.";
        header("Location: ../../index.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
